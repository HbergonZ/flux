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
        log_message('debug', '==== INÍCIO avaliar() ====');
        log_message('debug', '[Solicitacoes] Iniciando avaliação da solicitação ID: ' . $id);

        if (!$this->request->isAJAX()) {
            log_message('debug', '[Solicitacoes] Acesso não é AJAX - redirecionando');
            return redirect()->back();
        }

        $solicitacao = $this->solicitacoesModel->find($id);
        if (!$solicitacao) {
            log_message('error', '[Solicitacoes] Solicitação não encontrada - ID: ' . $id);
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitação não encontrada']);
        }

        log_message('debug', '[Solicitacoes] Dados brutos da solicitação: ' . print_r($solicitacao, true));

        $dadosAtuais = $this->parseSolicitacaoData($solicitacao['dados_atuais']);
        $dadosAlterados = $this->parseSolicitacaoData($solicitacao['dados_alterados']);

        log_message('debug', '[Solicitacoes] Dados atuais decodificados:' . print_r($dadosAtuais, true));
        log_message('debug', '[Solicitacoes] Dados alterados decodificados:' . print_r($dadosAlterados, true));

        // Se for uma ação, buscar equipe atual
        if ($solicitacao['nivel'] === 'acao' && !empty($solicitacao['id_acao'])) {
            $equipeAtual = $this->acoesModel->getEquipeAcao($solicitacao['id_acao']);
            log_message('debug', '[Solicitacoes] Equipe atual:' . print_r($equipeAtual, true));

            if (!empty($equipeAtual)) {
                $dadosAtuais['equipe_real'] = array_map(function ($membro) {
                    return $membro['username'] ?? 'Membro sem nome';
                }, $equipeAtual);
            } else {
                $dadosAtuais['equipe_real'] = [];
            }
        }

        // Processar evidências para projeto
        if ($solicitacao['nivel'] === 'projeto' && isset($dadosAlterados['evidencias'])) {
            log_message('debug', '[Solicitacoes] Processando evidências para projeto');
            $dadosAlterados['evidencias'] = $this->processarEvidenciasParaVisualizacao(
                $dadosAlterados['evidencias'],
                $solicitacao['id_projeto']
            );
            log_message('debug', '[Solicitacoes] Evidências processadas:' . print_r($dadosAlterados['evidencias'], true));
        }

        // Obter nome do solicitante
        $solicitante = $this->getSolicitanteName($solicitacao['id_solicitante']);

        log_message('debug', '[Solicitacoes] Preparando resposta JSON');
        $response = [
            'success' => true,
            'data' => [
                'solicitante' => $solicitante,
                'data_solicitacao' => $solicitacao['data_solicitacao'],
                'justificativa_solicitante' => $solicitacao['justificativa_solicitante']
            ],
            'dados_atuais' => $dadosAtuais,
            'dados_alterados' => $dadosAlterados,
            'tipo' => $solicitacao['tipo'],
            'nivel' => $solicitacao['nivel']
        ];

        log_message('debug', '[Solicitacoes] Resposta final:' . print_r($response, true));
        log_message('debug', '==== FIM avaliar() ====');

        return $this->response->setJSON($response);
    }

    protected function processarAlteracoesEquipeNomes($equipeData)
    {
        if (!is_array($equipeData)) return $equipeData;

        $result = [];
        foreach ($equipeData as $action => $userIds) {
            if (!is_array($userIds)) continue;

            $users = $this->userModel->whereIn('id', $userIds)->findAll();
            $result[$action] = array_column($users, 'username');
        }

        return $result;
    }

    protected function replaceUserIdsWithNames($equipeData)
    {
        if (!is_array($equipeData)) return $equipeData;

        $result = [];
        foreach ($equipeData as $action => $userIds) {
            if (!is_array($userIds)) continue;

            $users = $this->userModel->whereIn('id', $userIds)->findAll();
            $result[$action] = array_column($users, 'username');
        }

        return !empty($result) ? $result : $equipeData;
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

        if (!$model->insert($dados)) {
            throw new \Exception('Falha na inserção: ' . implode(', ', $model->errors()));
        }

        $insertId = $model->getInsertID();
        $registro = $model->find($insertId);
        if (!$registro) {
            throw new \Exception('Falha ao recuperar registro criado');
        }

        $this->solicitacoesModel->update($solicitacao['id'], [
            $this->getIdField($solicitacao['nivel']) => $insertId
        ]);

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

        $this->solicitacoesModel->transStart();

        // Processar alterações na equipe (se houver)
        if ($solicitacao['nivel'] === 'acao' && isset($dadosAlterados['equipe'])) {
            if (!$this->processarAlteracoesEquipe($idRegistro, $dadosAlterados['equipe'])) {
                throw new \Exception('Falha ao processar alterações na equipe');
            }
            unset($dadosAtualizar['equipe']);
        }

        // Processar alterações nas evidências (se houver)
        if ($solicitacao['nivel'] === 'acao' && isset($dadosAlterados['evidencias'])) {
            $evidenciasModel = new \App\Models\EvidenciasModel();

            // Adicionar novas evidências
            if (!empty($dadosAlterados['evidencias']['adicionar'])) {
                foreach ($dadosAlterados['evidencias']['adicionar'] as $evidencia) {
                    $data = [
                        'tipo' => $evidencia['tipo'],
                        'evidencia' => $evidencia['conteudo'],
                        'descricao' => $evidencia['descricao'] ?? null,
                        'nivel' => 'acao',
                        'id_nivel' => $idRegistro,
                        'created_by' => auth()->id(),
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    if (!$evidenciasModel->insert($data)) {
                        throw new \Exception('Falha ao adicionar evidência: ' . implode(', ', $evidenciasModel->errors()));
                    }
                }
            }

            // Remover evidências solicitadas
            if (!empty($dadosAlterados['evidencias']['remover'])) {
                foreach ($dadosAlterados['evidencias']['remover'] as $evidencia) {
                    if (!$evidenciasModel->delete($evidencia['id'])) {
                        throw new \Exception('Falha ao remover evidência ID: ' . $evidencia['id']);
                    }
                }
            }
        }

        if (!empty($dadosAtualizar) && !$model->update($idRegistro, $dadosAtualizar)) {
            throw new \Exception('Falha na atualização: ' . implode(', ', $model->errors()));
        }

        $dadosNovos = $model->find($idRegistro);

        $this->logController->registrarEdicao(
            $solicitacao['nivel'],
            $dadosAntigos,
            $dadosNovos,
            $this->getJustificativa($solicitacao)
        );

        $this->solicitacoesModel->transComplete();
        return true;
    }

    protected function processarAlteracoesEquipe($acaoId, $alteracoesEquipe)
    {
        return $this->acoesModel->processarAlteracoesEquipe($acaoId, $alteracoesEquipe);
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
            if ($campo === 'equipe') continue;

            if (is_array($valor)) {
                $resultado[$campo] = $valor['para'] ?? null;
            } else {
                $resultado[$campo] = $valor;
            }
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

    protected function processarEvidenciasParaVisualizacao($evidenciasSolicitadas, $idRegistro)
    {
        $resultado = ['adicionar' => [], 'remover' => []];

        if (empty($evidenciasSolicitadas)) {
            return $resultado;
        }

        // Tratar evidências para adicionar
        if (isset($evidenciasSolicitadas['adicionar'])) {
            // Caso direto: {'adicionar': [...]}
            $resultado['adicionar'] = $evidenciasSolicitadas['adicionar'];
        } elseif (isset($evidenciasSolicitadas['evidencias']['adicionar'])) {
            // Caso aninhado: {'evidencias': {'adicionar': [...]}}
            $resultado['adicionar'] = $evidenciasSolicitadas['evidencias']['adicionar'];
        }

        // Tratar evidências para remover
        if (isset($evidenciasSolicitadas['remover'])) {
            // Caso direto: {'remover': [...]}
            $resultado['remover'] = $evidenciasSolicitadas['remover'];
        } elseif (isset($evidenciasSolicitadas['evidencias']['remover'])) {
            // Caso aninhado: {'evidencias': {'remover': [...]}}
            $resultado['remover'] = $evidenciasSolicitadas['evidencias']['remover'];
        }

        return $resultado;
    }
}
