<?php

namespace App\Controllers;

use App\Models\SolicitacoesModel;
use App\Models\EtapasModel;
use App\Models\AcoesModel;
use App\Models\ProjetosModel;
use App\Models\PlanosModel;
use CodeIgniter\Shield\Models\UserModel;
use App\Controllers\LogController;

class Solicitacoes extends BaseController
{
    protected $solicitacoesModel;
    protected $etapasModel;
    protected $acoesModel;
    protected $projetosModel;
    protected $planosModel;
    protected $userModel;
    protected $logController;

    public function __construct()
    {
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->etapasModel = new EtapasModel();
        $this->acoesModel = new AcoesModel();
        $this->projetosModel = new ProjetosModel();
        $this->planosModel = new PlanosModel();
        $this->userModel = new UserModel();
        $this->logController = new LogController();
    }

    public function index()
    {
        $solicitacoes = $this->solicitacoesModel->where('status', 'pendente')->findAll();

        foreach ($solicitacoes as &$solicitacao) {
            $dados = json_decode($solicitacao['dados_atuais'] ?? '{}', true);
            $solicitacao['nome'] = $this->getNomeSolicitacao($solicitacao, $dados);
            $solicitacao['solicitante'] = $this->getSolicitanteName($solicitacao['id_solicitante']);
        }

        $data = [
            'title' => 'Solicitações Pendentes',
            'solicitacoes' => $solicitacoes
        ];

        return view('layout', ['content' => view('sys/solicitacoes', $data)]);
    }

    protected function getNomeSolicitacao($solicitacao, $dados)
    {
        switch ($solicitacao['nivel']) {
            case 'plano':
                return $dados['nome'] ?? 'Novo Plano';
            case 'projeto':
                return $dados['nome'] ?? $dados['identificador'] ?? 'Novo Projeto';
            case 'etapa':
                return $dados['nome'] ?? 'Nova Etapa';
            case 'acao':
                return $dados['nome'] ?? 'Nova Ação';
            default:
                return 'Nova Solicitação';
        }
    }

    protected function getSolicitanteName($id)
    {
        if (empty($id)) return 'Sistema';
        try {
            $user = $this->userModel->findById($id);
            return $user ? $user->username : 'Usuário #' . $id;
        } catch (\Exception $e) {
            return 'Usuário #' . $id;
        }
    }

    public function avaliar($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $solicitacao = $this->solicitacoesModel->find($id);
        if (!$solicitacao) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitação não encontrada']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $solicitacao,
            'dados_atuais' => $this->parseSolicitacaoData($solicitacao['dados_atuais']),
            'dados_alterados' => $this->parseSolicitacaoData($solicitacao['dados_alterados']),
            'tipo' => $solicitacao['tipo'],
            'nivel' => $solicitacao['nivel']
        ]);
    }

    protected function parseSolicitacaoData($data)
    {
        return !empty($data) ? json_decode($data, true) : [];
    }

    public function processar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $post = $this->request->getPost();
        if (empty($post['id']) || empty($post['acao'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Parâmetros inválidos']);
        }

        $solicitacao = $this->solicitacoesModel->find($post['id']);
        if (!$solicitacao) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitação não encontrada']);
        }

        $status = ($post['acao'] === 'aceitar') ? 'aprovada' : 'rejeitada';
        $updateData = [
            'status' => $status,
            'data_avaliacao' => date('Y-m-d H:i:s'),
            'id_avaliador' => auth()->id(),
            'justificativa_avaliador' => $post['justificativa'] ?? null
        ];

        if (!$this->solicitacoesModel->update($post['id'], $updateData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar solicitação',
                'errors' => $this->solicitacoesModel->errors()
            ]);
        }

        if ($post['acao'] === 'aceitar' && !$this->processarSolicitacao($solicitacao)) {
            $modelMap = [
                'plano' => $this->planosModel,
                'projeto' => $this->projetosModel,
                'etapa' => $this->etapasModel,
                'acao' => $this->acoesModel
            ];

            $model = $modelMap[$solicitacao['nivel']] ?? null;
            $modelErrors = $model ? $model->errors() : [];
            log_message('error', 'Falha ao processar solicitação: ' . $post['id'] . ' - Erros: ' . print_r($modelErrors, true));

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao processar alterações',
                'errors' => $modelErrors
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Solicitação ' . $status . ' com sucesso'
        ]);
    }

    protected function processarSolicitacao($solicitacao)
    {
        $modelMap = [
            'plano' => $this->planosModel,
            'projeto' => $this->projetosModel,
            'etapa' => $this->etapasModel,
            'acao' => $this->acoesModel
        ];

        $idFieldMap = [
            'plano' => 'id_plano',
            'projeto' => 'id_projeto',
            'etapa' => 'id_etapa',
            'acao' => 'id_acao'
        ];

        if (!isset($modelMap[$solicitacao['nivel']])) {
            log_message('error', 'Nível inválido: ' . $solicitacao['nivel']);
            return false;
        }

        return $this->processarRegistro(
            $solicitacao,
            $modelMap[$solicitacao['nivel']],
            $idFieldMap[$solicitacao['nivel']]
        );
    }

    protected function processarRegistro($solicitacao, $model, $idField)
    {
        try {
            $this->solicitacoesModel->transStart();

            switch (strtolower($solicitacao['tipo'])) {
                case 'inclusão':
                    $result = $this->processarInclusao($solicitacao, $model);
                    break;

                case 'edição':
                    $result = $this->processarEdicao($solicitacao, $model, $idField);
                    break;

                case 'exclusão':
                    $result = $this->processarExclusao($solicitacao, $model, $idField);
                    break;

                default:
                    throw new \Exception('Tipo de solicitação inválido');
            }

            $this->solicitacoesModel->transCommit();
            return $result;
        } catch (\Exception $e) {
            $this->solicitacoesModel->transRollback();
            log_message('error', 'Erro ao processar registro: ' . $e->getMessage());
            return false;
        }
    }

    protected function processarInclusao($solicitacao, $model)
    {
        $dados = json_decode($solicitacao['dados_alterados'], true) ?? [];
        $dados = $this->prepararDadosInclusao($dados, $solicitacao);

        // Insere o registro
        if (!$model->insert($dados)) {
            throw new \Exception('Falha na inserção: ' . implode(', ', $model->errors()));
        }

        // Obtém o ID do novo registro
        $insertId = $model->getInsertID();

        // Obtém os dados completos do registro criado
        $registro = $model->find($insertId);
        if (!$registro) {
            throw new \Exception('Falha ao recuperar registro criado');
        }

        // Atualiza a solicitação com o ID do novo registro
        $this->solicitacoesModel->update($solicitacao['id'], [
            $this->getIdField($solicitacao['nivel']) => $insertId
        ]);

        // Registra no log administrativo com o ID gerado
        $this->logController->registrarCriacao(
            $solicitacao['nivel'],
            $registro,
            $this->getJustificativa($solicitacao)
        );

        return true;
    }

    protected function processarEdicao($solicitacao, $model, $idField)
    {
        $idRegistro = $solicitacao[$idField] ?? null;
        if (empty($idRegistro)) {
            throw new \Exception('ID do registro não informado');
        }

        $dadosAntigos = $model->find($idRegistro);
        if (!$dadosAntigos) {
            throw new \Exception('Registro não encontrado');
        }

        $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?? [];
        $dadosAtualizar = $this->prepararDadosEdicao($dadosAlterados);

        if (!$model->update($idRegistro, $dadosAtualizar)) {
            throw new \Exception('Falha na atualização: ' . implode(', ', $model->errors()));
        }

        $dadosNovos = $model->find($idRegistro);

        $this->logController->registrarEdicao(
            $solicitacao['nivel'],
            $dadosAntigos,
            $dadosNovos,
            $this->getJustificativa($solicitacao)
        );

        return true;
    }

    protected function processarExclusao($solicitacao, $model, $idField)
    {
        $idRegistro = $solicitacao[$idField] ?? null;
        if (empty($idRegistro)) {
            throw new \Exception('ID do registro não informado');
        }

        $dadosAntigos = $model->find($idRegistro);
        if (!$dadosAntigos) {
            throw new \Exception('Registro não encontrado');
        }

        $this->logController->registrarExclusao(
            $solicitacao['nivel'],
            $dadosAntigos,
            $this->getJustificativa($solicitacao)
        );

        if (!$model->delete($idRegistro)) {
            throw new \Exception('Falha na exclusão: ' . implode(', ', $model->errors()));
        }

        return true;
    }

    protected function prepararDadosInclusao($dados, $solicitacao)
    {
        $base = [
            'id_projeto' => $solicitacao['id_projeto'] ?? null,
            'id_etapa' => $solicitacao['id_etapa'] ?? null
        ];

        if ($solicitacao['nivel'] === 'acao') {
            $base = array_merge($base, [
                'nome' => $dados['nome'] ?? 'Nova Ação',
                'responsavel' => $dados['responsavel'] ?? null,
                'equipe' => $dados['equipe'] ?? null,
                'tempo_estimado_dias' => !empty($dados['tempo_estimado_dias']) ? (int)$dados['tempo_estimado_dias'] : null,
                'entrega_estimada' => $this->formatarData($dados['entrega_estimada'] ?? null),
                'data_inicio' => $this->formatarData($dados['data_inicio'] ?? null),
                'data_fim' => $this->formatarData($dados['data_fim'] ?? null),
                'status' => $dados['status'] ?? 'Não iniciado',
                'ordem' => (int)($dados['ordem'] ?? $this->acoesModel->getProximaOrdem($solicitacao['id_etapa'] ?? null))
            ]);
        }

        return $base;
    }

    protected function prepararDadosEdicao($dadosAlterados)
    {
        $resultado = [];
        foreach ($dadosAlterados as $campo => $valor) {
            $resultado[$campo] = is_array($valor) ? ($valor['para'] ?? null) : $valor;
        }
        return $resultado;
    }

    protected function formatarData($data)
    {
        return !empty($data) ? date('Y-m-d', strtotime($data)) : null;
    }

    protected function getJustificativa($solicitacao)
    {
        return "Solicitação #{$solicitacao['id']} - Usuário #{$solicitacao['id_solicitante']}: " .
            ($solicitacao['justificativa_solicitante'] ?? 'Sem justificativa');
    }

    protected function getIdField($nivel)
    {
        return 'id_' . $nivel;
    }
}
