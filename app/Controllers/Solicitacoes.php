<?php

namespace App\Controllers;

use App\Models\SolicitacoesModel;
use App\Models\EtapasModel;
use App\Models\AcoesModel;
use App\Models\ProjetosModel;
use App\Models\PlanosModel;
use App\Models\EixosModel;
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
    protected $eixosModel;

    public function __construct()
    {
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->etapasModel = new EtapasModel();
        $this->acoesModel = new AcoesModel();
        $this->projetosModel = new ProjetosModel();
        $this->planosModel = new PlanosModel();
        $this->userModel = new UserModel();
        $this->logController = new LogController();
        $this->eixosModel = new EixosModel();
    }

    public function index()
    {
        $solicitacoes = $this->solicitacoesModel->where('status', 'pendente')->findAll();
        $eixos = [];
        foreach ($this->eixosModel->select('id, nome')->findAll() as $eixo) {
            $eixos[$eixo['id']] = $eixo['nome'];
        }
        foreach ($solicitacoes as &$solicitacao) {
            $dados = json_decode($solicitacao['dados_atuais'] ?? '{}', true);
            $dados_alterados = json_decode($solicitacao['dados_alterados'] ?? '{}', true);
            // Totais de evidências e indicadores nos dados atuais
            $solicitacao['total_evidencias'] = (!empty($dados['evidencias']) && is_array($dados['evidencias'])) ? count($dados['evidencias']) : 0;
            $solicitacao['total_indicadores'] = (!empty($dados['indicadores']) && is_array($dados['indicadores'])) ? count($dados['indicadores']) : 0;
            // Totais nos dados alterados
            $solicitacao['total_evidencias_alteradas'] = (!empty($dados_alterados['evidencias']) && is_array($dados_alterados['evidencias'])) ? count($dados_alterados['evidencias']) : 0;
            $solicitacao['total_indicadores_alteradas'] = (!empty($dados_alterados['indicadores']) && is_array($dados_alterados['indicadores'])) ? count($dados_alterados['indicadores']) : 0;
            $solicitacao['nome'] = $this->getNomeSolicitacao($solicitacao, $dados);
            $solicitacao['solicitante'] = $this->getSolicitanteName($solicitacao['id_solicitante']);
        }
        unset($solicitacao);

        $data = [
            'title' => 'Solicitações Pendentes',
            'solicitacoes' => $solicitacoes,
            'eixos' => $eixos
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
            return $user && !empty($user->name) ? $user->name : 'Usuário #' . $id;
        } catch (\Exception $e) {
            return 'Usuário #' . $id;
        }
    }

    public function avaliar($id)
    {
        $solicitacao = $this->solicitacoesModel->find($id);
        if (!$solicitacao) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitação não encontrada'
            ]);
        }
        $dadosAtuais = json_decode($solicitacao['dados_atuais'], true) ?? [];
        $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?? [];
        // Contagem de evidências e indicadores nos atuais e alterados
        $dadosAtuais['total_evidencias'] = (!empty($dadosAtuais['evidencias']) && is_array($dadosAtuais['evidencias'])) ? count($dadosAtuais['evidencias']) : 0;
        $dadosAtuais['total_indicadores'] = (!empty($dadosAtuais['indicadores']) && is_array($dadosAtuais['indicadores'])) ? count($dadosAtuais['indicadores']) : 0;
        $dadosAlterados['total_evidencias'] = (!empty($dadosAlterados['evidencias']) && is_array($dadosAlterados['evidencias'])) ? count($dadosAlterados['evidencias']) : 0;
        $dadosAlterados['total_indicadores'] = (!empty($dadosAlterados['indicadores']) && is_array($dadosAlterados['indicadores'])) ? count($dadosAlterados['indicadores']) : 0;
        // Carregar nomes dos responsáveis em dados_atuais
        if (!empty($dadosAtuais['responsaveis'])) {
            $responsaveisIds = $dadosAtuais['responsaveis'];
            if (!is_array($responsaveisIds)) $responsaveisIds = [$responsaveisIds];
            $dadosAtuais['responsaveis_nomes'] = array_column($this->getUserNamesByIds($responsaveisIds), 'name');
        } else if (!empty($dadosAtuais['responsavel'])) {
            $dadosAtuais['responsaveis_nomes'] = [$dadosAtuais['responsavel']];
        } else {
            $dadosAtuais['responsaveis_nomes'] = [];
        }
        // Carregar nomes dos responsáveis nas alterações
        if (!empty($dadosAlterados['responsaveis'])) {
            $addIds = isset($dadosAlterados['responsaveis']['adicionar']) ? $dadosAlterados['responsaveis']['adicionar'] : [];
            $remIds = isset($dadosAlterados['responsaveis']['remover']) ? $dadosAlterados['responsaveis']['remover'] : [];
            $addIds = is_array($addIds) ? $addIds : [];
            $remIds = is_array($remIds) ? $remIds : [];
            $dadosAlterados['responsaveis']['adicionar_nomes'] = array_column($this->getUserNamesByIds($addIds), 'name');
            $dadosAlterados['responsaveis']['remover_nomes'] = array_column($this->getUserNamesByIds($remIds), 'name');
        }
        // Padroniza evidências para trazer objeto adicionar/remover
        if (!empty($dadosAlterados['evidencias'])) {
            $dadosAlterados['evidencias'] = $this->processarEvidenciasParaVisualizacao($dadosAlterados['evidencias'], $solicitacao['id_acao'] ?? null);
        }
        // Indicadores mesma lógica
        if (!empty($dadosAlterados['indicadores'])) {
            $dadosAlterados['indicadores'] = $this->processarEvidenciasParaVisualizacao($dadosAlterados['indicadores'], $solicitacao['id_acao'] ?? null);
        }
        $usuario = null;
        if (!empty($solicitacao['id_solicitante'])) {
            $usuario = $this->userModel->find($solicitacao['id_solicitante']);
        }
        $data = [
            'id' => $solicitacao['id'],
            'nivel' => $solicitacao['nivel'],
            'tipo' => $solicitacao['tipo'],
            'status' => $solicitacao['status'],
            'solicitante' => $usuario && !empty($usuario->name) ? $usuario->name : 'Não informado',
            'data_solicitacao' => $solicitacao['data_solicitacao'],
            'justificativa_solicitante' => $solicitacao['justificativa_solicitante'] ?? '',
            'dados_atuais' => $dadosAtuais,
            'dados_alterados' => $dadosAlterados,
        ];
        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    protected function getUserNamesByIds($ids)
    {
        if (empty($ids) || !is_array($ids)) return [];
        $ids = array_unique(array_filter(array_map('intval', $ids)));
        if (empty($ids)) return [];
        $users = $this->userModel->whereIn('id', $ids)->findAll();
        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => is_object($user) ? $user->id : $user['id'],
                'name' => is_object($user) ? $user->name : $user['name'],
            ];
        }
        return $result;
    }

    protected function processarAlteracoesEquipeNomes($equipeData)
    {
        if (!is_array($equipeData)) return $equipeData;
        $result = [];
        foreach ($equipeData as $action => $userIds) {
            if (!is_array($userIds)) continue;
            $users = $this->userModel->whereIn('id', $userIds)->findAll();
            $result[$action] = array_column($users, 'name');
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
            $result[$action] = array_column($users, 'name');
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
        if (empty($evidenciasSolicitadas) || !is_array($evidenciasSolicitadas)) {
            return $resultado;
        }
        if (isset($evidenciasSolicitadas['adicionar'])) {
            $resultado['adicionar'] = $evidenciasSolicitadas['adicionar'];
        } elseif (isset($evidenciasSolicitadas['evidencias']['adicionar'])) {
            $resultado['adicionar'] = $evidenciasSolicitadas['evidencias']['adicionar'];
        }
        if (isset($evidenciasSolicitadas['remover'])) {
            $resultado['remover'] = $evidenciasSolicitadas['remover'];
        } elseif (isset($evidenciasSolicitadas['evidencias']['remover'])) {
            $resultado['remover'] = $evidenciasSolicitadas['evidencias']['remover'];
        }
        return $resultado;
    }
}
