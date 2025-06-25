<?php

namespace App\Controllers;

use App\Models\AcoesModel;
use App\Models\EtapasModel;
use App\Models\ProjetosModel;
use App\Models\PlanosModel;
use App\Models\SolicitacoesModel;
use App\Controllers\LogController;
use App\Models\EvidenciasModel;

class Acoes extends BaseController
{
    protected $logController;
    protected $acoesModel;
    protected $etapasModel;
    protected $projetosModel;
    protected $planosModel;
    protected $solicitacoesModel;
    protected $evidenciasModel;

    public function __construct()
    {
        $this->acoesModel = new AcoesModel();
        $this->etapasModel = new EtapasModel();
        $this->projetosModel = new ProjetosModel();
        $this->planosModel = new PlanosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->logController = new LogController();
        $this->evidenciasModel = new EvidenciasModel();
    }

    private function getProximaOrdem($idOrigem, $tipoOrigem)
    {
        $builder = $this->acoesModel;

        if ($tipoOrigem === 'etapa') {
            $builder->where('id_etapa', $idOrigem);
        } else {
            $builder->where('id_projeto', $idOrigem)
                ->where('id_etapa IS NULL');
        }

        $builder->selectMax('ordem');
        $query = $builder->get();
        $result = $query->getRowArray();

        return ($result['ordem'] ?? 0) + 1;
    }

    public function proximaOrdem($idOrigem, $tipoOrigem = 'etapa')
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $proximaOrdem = $this->getProximaOrdem($idOrigem, $tipoOrigem);
            return $this->response->setJSON([
                'success' => true,
                'proximaOrdem' => $proximaOrdem
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao calcular próxima ordem: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao calcular próxima ordem'
            ]);
        }
    }

    // No controller Acoes.php
    public function index($idOrigem = null, $tipoOrigem = 'etapa')
    {
        if (empty($idOrigem)) {
            return redirect()->back();
        }

        // Apenas carrega informações básicas para o template
        $data = [];

        if ($tipoOrigem === 'etapa') {
            $etapa = $this->etapasModel->find($idOrigem);
            if (!$etapa) return redirect()->back();

            $projeto = $this->projetosModel->find($etapa['id_projeto']);
            $plano = $this->planosModel->find($projeto['id_plano']);

            $data = [
                'etapa' => $etapa,
                'projeto' => $projeto,
                'plano' => $plano,
                'idOrigem' => $idOrigem,
                'tipoOrigem' => 'etapa',
                'acessoDireto' => false
            ];
        } else {
            $projeto = $this->projetosModel->find($idOrigem);
            $plano = $this->planosModel->find($projeto['id_plano']);

            $data = [
                'projeto' => $projeto,
                'plano' => $plano,
                'idOrigem' => $idOrigem,
                'tipoOrigem' => 'projeto',
                'acessoDireto' => true
            ];
        }

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
    }

    // Novo endpoint para carregar dados via AJAX
    public function getAcoes($idOrigem, $tipoOrigem)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $builder = $this->acoesModel;

        if ($tipoOrigem === 'etapa') {
            $builder->where('id_etapa', $idOrigem);
        } else {
            $builder->where('id_projeto', $idOrigem)
                ->where('id_etapa IS NULL');
        }

        $builder->select('acoes.*, GROUP_CONCAT(DISTINCT users.username SEPARATOR ", ") as responsaveis')
            ->join('responsaveis', 'responsaveis.nivel_id = acoes.id AND responsaveis.nivel = "acao"', 'left')
            ->join('users', 'users.id = responsaveis.usuario_id', 'left')
            ->groupBy('acoes.id');

        $acoes = $builder->orderBy('ordem', 'ASC')->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $acoes
        ]);
    }

    public function cadastrar($idOrigem, $tipoOrigem = 'etapa')
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]',
            'responsavel' => 'permit_empty|max_length[255]',
            'equipe' => 'permit_empty|max_length[255]',
            'tempo_estimado_dias' => 'permit_empty|integer',
            'entrega_estimada' => 'permit_empty|valid_date',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
        ];

        if ($this->validate($rules)) {
            try {
                $proximaOrdem = $this->getProximaOrdem($idOrigem, $tipoOrigem);

                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $proximaOrdem,
                    'responsavel' => $this->request->getPost('responsavel'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias') ?: null,
                    'entrega_estimada' => $this->request->getPost('entrega_estimada') ?: null,
                    'data_inicio' => $this->request->getPost('data_inicio') ?: null,
                    'data_fim' => $this->request->getPost('data_fim') ?: null
                ];

                if ($tipoOrigem === 'projeto') {
                    $projeto = $this->projetosModel->find($idOrigem);
                    if (!$projeto) {
                        $response['message'] = 'Projeto não encontrado';
                        return $this->response->setJSON($response);
                    }
                    $data['id_projeto'] = $idOrigem;
                    $data['id_etapa'] = null;
                    $idPlano = $projeto['id_plano'];
                } else {
                    $etapa = $this->etapasModel->find($idOrigem);
                    if (!$etapa) {
                        $response['message'] = 'Etapa não encontrada';
                        return $this->response->setJSON($response);
                    }
                    $data['id_projeto'] = $etapa['id_projeto'];
                    $data['id_etapa'] = $idOrigem;
                    $projeto = $this->projetosModel->find($etapa['id_projeto']);
                    $idPlano = $projeto['id_plano'];
                }

                // Calculate status automatically
                $statusProjeto = $projeto['status'] ?? null;
                $data['status'] = $this->acoesModel->calcularStatus($data, $statusProjeto);

                $this->acoesModel->transStart();
                $insertId = $this->acoesModel->insert($data);

                if (!$insertId) {
                    throw new \Exception('Falha ao inserir ação no banco de dados');
                }

                $acaoCompleta = array_merge(['id' => $insertId], $data);
                if (!$this->logController->registrarCriacao('acao', $acaoCompleta, 'Cadastro inicial da ação')) {
                    throw new \Exception('Falha ao registrar log de criação');
                }

                $this->acoesModel->transComplete();

                $response['success'] = true;
                $response['message'] = 'Ação cadastrada com sucesso!';
                $response['id'] = $insertId;
            } catch (\Exception $e) {
                $this->acoesModel->transRollback();
                $response['message'] = 'Erro ao cadastrar ação: ' . $e->getMessage();
                log_message('error', 'Erro no cadastro de ação: ' . $e->getMessage());
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $acao = $this->acoesModel->find($id);

        if ($acao) {
            $response['success'] = true;
            $response['data'] = $acao;
        } else {
            $response['message'] = 'Ação não encontrada';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar($idOrigem, $tipoOrigem = 'etapa')
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required',
            'nome' => 'required|min_length[3]|max_length[255]',
            'ordem' => 'required|integer',
            'responsavel' => 'permit_empty|max_length[255]',
            'entrega_estimada' => 'permit_empty|valid_date',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => [
                'permit_empty',
                'valid_date',
                function ($value, $data, &$error) {
                    if (!empty($value) && empty($data['data_inicio'])) {
                        $error = 'Não é possível definir data de fim sem data de início';
                        return false;
                    }
                    return true;
                }
            ],
        ];

        if ($this->validate($rules)) {
            try {
                $id = $this->request->getPost('id');
                $acaoAntiga = $this->acoesModel->find($id);

                if (!$acaoAntiga) {
                    $response['message'] = 'Ação não encontrada';
                    return $this->response->setJSON($response);
                }

                // Validação de evidências para data fim
                if (!empty($this->request->getPost('data_fim'))) {
                    $temEvidencias = $this->evidenciasModel->where('nivel', 'acao')
                        ->where('id_nivel', $id)
                        ->countAllResults() > 0;

                    if (!$temEvidencias) {
                        $response['message'] = 'Para definir uma data de fim, é necessário cadastrar pelo menos uma evidência.';
                        return $this->response->setJSON($response);
                    }
                }

                $data = [
                    'id' => $id,
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $this->request->getPost('ordem'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'entrega_estimada' => $this->ajustarData($this->request->getPost('entrega_estimada')),
                    'data_inicio' => $this->ajustarData($this->request->getPost('data_inicio')),
                    'data_fim' => $this->ajustarData($this->request->getPost('data_fim')),
                    'status' => $this->calcularStatusNovo($this->request->getPost())
                ];

                $this->acoesModel->transStart();
                $updated = $this->acoesModel->save($data);

                if (!$updated) {
                    throw new \Exception('Falha ao atualizar ação no banco de dados');
                }

                $acaoAtualizada = $this->acoesModel->find($id);

                if (!$this->logController->registrarEdicao('acao', $acaoAntiga, $acaoAtualizada, 'Edição realizada via interface')) {
                    throw new \Exception('Falha ao registrar log de edição');
                }

                $this->acoesModel->transComplete();

                $response['success'] = true;
                $response['message'] = 'Ação atualizada com sucesso!';
                $response['data'] = $acaoAtualizada; // Incluir dados atualizados na resposta
            } catch (\Exception $e) {
                $this->acoesModel->transRollback();
                $response['message'] = 'Erro ao atualizar ação: ' . $e->getMessage();
                log_message('error', 'Erro na atualização de ação: ' . $e->getMessage());
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    private function calcularStatusNovo($postData)
    {
        // 1. Se tem data_fim, status é Finalizado
        if (!empty($postData['data_fim'])) {
            if (empty($postData['data_inicio'])) {
                throw new \RuntimeException('Não é possível definir data de fim sem data de início');
            }
            return 'Finalizado';
        }

        // 2. Verifica se está atrasado
        if (
            !empty($postData['entrega_estimada']) &&
            empty($postData['data_fim']) &&
            strtotime($postData['entrega_estimada']) < strtotime(date('Y-m-d'))
        ) {
            return 'Atrasado';
        }

        // 3. Se tem data_inicio, status é Em andamento
        if (!empty($postData['data_inicio'])) {
            return 'Em andamento';
        }

        // 4. Caso contrário, mantém o status atual ou define como Não iniciado
        return $postData['status'] ?? 'Não iniciado';
    }

    private function ajustarData($dataString)
    {
        if (empty($dataString)) {
            return null;
        }

        // Converte para objeto DateTime e formata corretamente
        $date = new \DateTime($dataString);
        return $date->format('Y-m-d');
    }

    public function excluir($idOrigem, $tipoOrigem = 'etapa')
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        try {
            $acao = $this->acoesModel->find($id);

            if (!$acao) {
                $response['message'] = 'Ação não encontrada';
                return $this->response->setJSON($response);
            }

            $this->acoesModel->transStart();

            if (!$this->logController->registrarExclusao('acao', $acao, 'Exclusão realizada via interface')) {
                throw new \Exception('Falha ao registrar log de exclusão');
            }

            $excluido = $this->acoesModel->where('id', $id)->delete();

            if (!$excluido) {
                throw new \Exception('Falha ao excluir ação no banco de dados');
            }

            $this->acoesModel->transComplete();

            $response['success'] = true;
            $response['message'] = 'Ação excluída com sucesso!';
        } catch (\Exception $e) {
            $this->acoesModel->transRollback();
            $response['message'] = 'Erro ao excluir ação: ' . $e->getMessage();
            log_message('error', 'Erro na exclusão de ação: ' . $e->getMessage());
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($idOrigem, $tipoOrigem)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $filters = $this->request->getPost();

        $builder = $this->acoesModel;
        $builder->select('acoes.*, GROUP_CONCAT(DISTINCT users.username SEPARATOR ", ") as responsaveis')
            ->join('responsaveis', 'responsaveis.nivel_id = acoes.id AND responsaveis.nivel = "acao"', 'left')
            ->join('users', 'users.id = responsaveis.usuario_id', 'left')
            ->groupBy('acoes.id');

        if ($tipoOrigem === 'etapa') {
            $builder->where('id_etapa', $idOrigem);
        } else {
            $builder->where('id_projeto', $idOrigem)
                ->where('id_etapa IS NULL');
        }

        // Aplicar filtros
        if (!empty($filters['nome'])) {
            $builder->like('acoes.nome', $filters['nome']);
        }

        if (!empty($filters['responsavel'])) {
            $builder->like('users.username', $filters['responsavel']);
        }

        if (!empty($filters['status'])) {
            $builder->where('acoes.status', $filters['status']);
        }

        if (!empty($filters['data_filtro'])) {
            $dataFiltro = $filters['data_filtro'];
            $builder->groupStart()
                ->where('acoes.data_inicio <=', $dataFiltro)
                ->groupStart()
                ->where('acoes.data_fim >=', $dataFiltro)
                ->orWhere('acoes.data_fim IS NULL')
                ->groupEnd()
                ->groupEnd();
        }

        $acoes = $builder->orderBy('acoes.ordem', 'ASC')->findAll();

        return $this->response->setJSON(['success' => true, 'data' => $acoes]);
    }

    public function dadosAcao($id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $acao = $this->acoesModel->find($id);

        if ($acao) {
            $response['success'] = true;
            $response['data'] = $acao;
        } else {
            $response['message'] = 'Ação não encontrada';
        }

        return $this->response->setJSON($response);
    }

    public function solicitarEdicao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'id' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $acaoAtual = $this->acoesModel->find($postData['id']);
                if (!$acaoAtual) {
                    $response['message'] = 'Ação não encontrada';
                    return $this->response->setJSON($response);
                }

                $projeto = $this->projetosModel->find($acaoAtual['id_projeto']);
                if (!$projeto) {
                    $response['message'] = 'Projeto relacionado não encontrado';
                    return $this->response->setJSON($response);
                }

                // Remove campos que não devem ser incluídos nos dados atuais
                unset($acaoAtual['id'], $acaoAtual['created_at'], $acaoAtual['updated_at']);

                $alteracoes = [];
                $camposEditaveis = [
                    'nome',
                    'responsavel',
                    'tempo_estimado_dias',
                    'entrega_estimada',
                    'data_inicio',
                    'data_fim',
                    'status',
                    'ordem'
                ];

                foreach ($camposEditaveis as $campo) {
                    if (isset($postData[$campo])) {
                        $valorAtual = $acaoAtual[$campo] ?? null;
                        $valorNovo = $postData[$campo] ?? null;

                        // Comparação mais robusta considerando tipos diferentes
                        if ((string)$valorAtual !== (string)$valorNovo) {
                            $alteracoes[$campo] = [
                                'de' => $valorAtual,
                                'para' => $valorNovo
                            ];
                        }
                    }
                }

                // Processar equipe
                if (!empty($postData['adicionar_membro'])) {
                    $alteracoes['equipe']['adicionar'] = explode(',', $postData['adicionar_membro']);
                }
                if (!empty($postData['remover_membro'])) {
                    $alteracoes['equipe']['remover'] = explode(',', $postData['remover_membro']);
                }

                // Processar evidências
                $evidenciasSolicitadas = [];

                if (!empty($postData['evidencias_adicionadas'])) {
                    $evidenciasAdicionadas = json_decode($postData['evidencias_adicionadas'], true);
                    if (!empty($evidenciasAdicionadas)) {
                        $evidenciasSolicitadas['adicionar'] = array_map(function ($ev) {
                            return [
                                'tipo' => $ev['tipo'],
                                'conteudo' => $ev['conteudo'],
                                'descricao' => $ev['descricao'] ?? null
                            ];
                        }, $evidenciasAdicionadas);
                    }
                }

                if (!empty($postData['evidencias_removidas'])) {
                    $evidenciasRemovidas = json_decode($postData['evidencias_removidas'], true);
                    if (!empty($evidenciasRemovidas)) {
                        $evidenciasSolicitadas['remover'] = $evidenciasRemovidas;
                    }
                }

                if (!empty($evidenciasSolicitadas)) {
                    $alteracoes['evidencias'] = $evidenciasSolicitadas;
                }

                // Verificar se há alterações válidas
                /* if (empty($alteracoes)) {
                    $response['message'] = 'Nenhuma alteração detectada';
                    return $this->response->setJSON($response);
                } */

                $data = [
                    'nivel' => 'acao',
                    'id_solicitante' => auth()->id(),
                    'id_plano' => $projeto['id_plano'],
                    'id_projeto' => $acaoAtual['id_projeto'],
                    'id_etapa' => $acaoAtual['id_etapa'],
                    'id_acao' => $postData['id'],
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($acaoAtual, JSON_UNESCAPED_UNICODE),
                    'dados_alterados' => json_encode($alteracoes, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de edição enviada com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarEdicao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function solicitarExclusao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'id' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $acao = $this->acoesModel->find($postData['id']);
                if (!$acao) {
                    $response['message'] = 'Ação não encontrada';
                    return $this->response->setJSON($response);
                }

                $projeto = $this->projetosModel->find($acao['id_projeto']);
                if (!$projeto) {
                    $response['message'] = 'Projeto relacionado não encontrado';
                    return $this->response->setJSON($response);
                }

                $dadosAtuais = [
                    'nome' => $acao['nome'],
                    'responsavel' => $acao['responsavel'],
                    'equipe' => $acao['equipe'],
                    'status' => $acao['status'],
                    'entrega_estimada' => $acao['entrega_estimada'],
                    'data_inicio' => $acao['data_inicio'],
                    'data_fim' => $acao['data_fim'],
                    'id_etapa' => $acao['id_etapa'],
                    'id_projeto' => $acao['id_projeto']
                ];

                $data = [
                    'nivel' => 'acao',
                    'id_solicitante' => auth()->id(),
                    'id_plano' => $projeto['id_plano'],
                    'id_projeto' => $acao['id_projeto'],
                    'id_etapa' => $acao['id_etapa'],
                    'id_acao' => $postData['id'],
                    'tipo' => 'Exclusão',
                    'dados_atuais' => json_encode($dadosAtuais, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de exclusão enviada com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarExclusao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function solicitarInclusao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $proximaOrdem = $this->getProximaOrdem(
                    $postData['id_etapa'] ?? $postData['id_projeto'],
                    isset($postData['id_etapa']) ? 'etapa' : 'projeto'
                );

                $dadosAlterados = [
                    'nome' => $postData['nome'],
                    'responsavel' => $postData['responsavel'] ?? null,
                    'tempo_estimado_dias' => $postData['tempo_estimado_dias'] ?? null,
                    'entrega_estimada' => $postData['entrega_estimada'] ?? null,
                    'data_inicio' => $postData['data_inicio'] ?? null,
                    'data_fim' => $postData['data_fim'] ?? null,
                    'ordem' => $proximaOrdem
                ];

                // Calculate status automatically
                $statusProjeto = null;
                if (isset($postData['id_etapa']) && !empty($postData['id_etapa'])) {
                    $etapa = $this->etapasModel->find($postData['id_etapa']);
                    if ($etapa) {
                        $projeto = $this->projetosModel->find($etapa['id_projeto']);
                        $statusProjeto = $projeto['status'] ?? null;
                    }
                } elseif (isset($postData['id_projeto']) && !empty($postData['id_projeto'])) {
                    $projeto = $this->projetosModel->find($postData['id_projeto']);
                    $statusProjeto = $projeto['status'] ?? null;
                }

                $dadosAlterados['status'] = $this->acoesModel->calcularStatus($dadosAlterados, $statusProjeto);

                $idPlano = null;
                $idProjeto = null;
                $idEtapa = null;

                if (isset($postData['id_etapa']) && !empty($postData['id_etapa'])) {
                    $etapa = $this->etapasModel->find($postData['id_etapa']);
                    if (!$etapa) {
                        $response['message'] = 'Etapa não encontrada';
                        return $this->response->setJSON($response);
                    }

                    $projeto = $this->projetosModel->find($etapa['id_projeto']);
                    if (!$projeto) {
                        $response['message'] = 'Projeto relacionado não encontrado';
                        return $this->response->setJSON($response);
                    }

                    $idPlano = $projeto['id_plano'];
                    $idProjeto = $etapa['id_projeto'];
                    $idEtapa = $postData['id_etapa'];

                    $dadosAlterados['id_projeto'] = $idProjeto;
                    $dadosAlterados['id_etapa'] = $idEtapa;
                } elseif (isset($postData['id_projeto']) && !empty($postData['id_projeto'])) {
                    $projeto = $this->projetosModel->find($postData['id_projeto']);
                    if (!$projeto) {
                        $response['message'] = 'Projeto não encontrado';
                        return $this->response->setJSON($response);
                    }

                    $idPlano = $projeto['id_plano'];
                    $idProjeto = $postData['id_projeto'];

                    $dadosAlterados['id_projeto'] = $idProjeto;
                    $dadosAlterados['id_etapa'] = null;
                } else {
                    $response['message'] = 'Nenhum projeto ou etapa especificado';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'nivel' => 'acao',
                    'id_solicitante' => auth()->id(),
                    'id_plano' => $idPlano,
                    'id_projeto' => $idProjeto,
                    'id_etapa' => $idEtapa,
                    'tipo' => 'Inclusão',
                    'dados_alterados' => json_encode($dadosAlterados, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de inclusão enviada com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarInclusao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function salvarOrdem($idOrigem, $tipoOrigem = 'etapa')
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $ordens = $this->request->getPost('ordem');

        if (empty($ordens) || !is_array($ordens)) {
            $response['message'] = 'Nenhuma ordem foi enviada';
            return $this->response->setJSON($response);
        }

        if (count($ordens) !== count(array_unique($ordens))) {
            $response['message'] = 'Existem ordens duplicadas';
            return $this->response->setJSON($response);
        }

        try {
            $this->acoesModel->transStart();

            foreach ($ordens as $id => $ordem) {
                $this->acoesModel->update($id, ['ordem' => (int)$ordem]);
            }

            $this->acoesModel->transComplete();

            if ($this->acoesModel->transStatus() === false) {
                throw new \Exception('Erro ao atualizar ordens no banco de dados');
            }

            $response['success'] = true;
            $response['message'] = 'Ordem das ações atualizada com sucesso!';
        } catch (\Exception $e) {
            log_message('error', 'Erro ao salvar ordem: ' . $e->getMessage());
            $response['message'] = 'Erro ao salvar ordem: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function getEquipe($acaoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $db = db_connect();
            $equipe = $db->table('acoes_equipe')
                ->select('users.id, users.username, auth_identities.secret as email')
                ->join('users', 'users.id = acoes_equipe.usuario_id')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"')
                ->where('acao_id', $acaoId)
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $equipe
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar equipe: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar equipe: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function adicionarMembroEquipe()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'acao_id' => 'required|numeric',
            'usuario_id' => 'required|numeric'
        ];

        if ($this->validate($rules)) {
            try {
                // Verifica se a ação existe
                $acao = $this->acoesModel->find($postData['acao_id']);
                if (!$acao) {
                    $response['message'] = 'Ação não encontrada';
                    return $this->response->setJSON($response);
                }

                // Verifica se o usuário existe
                $userModel = new \CodeIgniter\Shield\Models\UserModel();
                $usuario = $userModel->find($postData['usuario_id']);
                if (!$usuario) {
                    $response['message'] = 'Usuário não encontrado';
                    return $this->response->setJSON($response);
                }

                // Verifica se o relacionamento já existe
                $builder = db_connect()->table('acoes_equipe');
                $existe = $builder->where([
                    'acao_id' => $postData['acao_id'],
                    'usuario_id' => $postData['usuario_id']
                ])->countAllResults();

                if ($existe > 0) {
                    $response['message'] = 'Este usuário já está na equipe desta ação';
                    return $this->response->setJSON($response);
                }

                // Adiciona o membro à equipe
                $builder->insert([
                    'acao_id' => $postData['acao_id'],
                    'usuario_id' => $postData['usuario_id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $response['success'] = true;
                $response['message'] = 'Usuário adicionado à equipe com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro ao adicionar membro à equipe: ' . $e->getMessage());
                $response['message'] = 'Erro ao adicionar usuário à equipe: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }
    public function removerMembroEquipe()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'acao_id' => 'required|numeric',
            'usuario_id' => 'required|numeric'
        ];

        if ($this->validate($rules)) {
            try {
                $this->acoesModel->removerMembroEquipe($postData['acao_id'], $postData['usuario_id']);

                $response['success'] = true;
                $response['message'] = 'Usuário removido da equipe com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro ao remover membro da equipe: ' . $e->getMessage());
                $response['message'] = 'Erro ao remover usuário da equipe';
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function buscarUsuarios()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $acaoId = $this->request->getGet('acao_id');
        $term = $this->request->getGet('term');

        try {
            $db = db_connect();

            // Obter IDs dos usuários já na equipe (se houver ação_id)
            $idsEquipe = [];
            if ($acaoId) {
                $equipe = $db->table('acoes_equipe')
                    ->select('usuario_id')
                    ->where('acao_id', $acaoId)
                    ->get()
                    ->getResultArray();
                $idsEquipe = array_column($equipe, 'usuario_id');
            }

            // Buscar usuários ativos
            $builder = $db->table('users')
                ->select('users.id, users.username, auth_identities.secret as email')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"')
                ->where('users.active', 1);

            // Aplicar filtro por termo de busca
            if (!empty($term)) {
                $builder->groupStart()
                    ->like('users.username', $term)
                    ->orLike('auth_identities.secret', $term)
                    ->groupEnd();
            }

            // Excluir usuários já na equipe
            if (!empty($idsEquipe)) {
                $builder->whereNotIn('users.id', $idsEquipe);
            }

            $usuarios = $builder->orderBy('users.username', 'ASC')
                ->get()
                ->getResultArray();

            // Retorna no formato esperado pelo Select2 e pela nossa lista
            return $this->response->setJSON([
                'success' => true,
                'data' => $usuarios,
                'results' => array_map(function ($user) {
                    return [
                        'id' => $user['id'],
                        'text' => "{$user['username']} ({$user['email']})",
                        'username' => $user['username'],
                        'email' => $user['email']
                    ];
                }, $usuarios)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar usuários: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar usuários',
                'data' => [],
                'results' => []
            ]);
        }
    }

    public function getEquipeFormatada($acaoId)
    {
        try {
            $equipe = $this->acoesModel->getUsernamesEquipe($acaoId);
            $equipeFormatada = implode(', ', array_column($equipe, 'username'));

            return $this->response->setJSON([
                'success' => true,
                'equipe' => $equipeFormatada
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar equipe formatada: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar equipe',
                'equipe' => ''
            ]);
        }
    }

    public function gerenciarEvidencias($acaoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $acao = $this->acoesModel->find($acaoId);
        if (!$acao) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ação não encontrada']);
        }

        $evidencias = $this->evidenciasModel->where('nivel', 'acao')
            ->where('id_nivel', $acaoId)
            ->orderBy('created_at', 'DESC')  // Mantemos DESC para exibir as mais recentes primeiro
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'html' => view('components/acoes/modal-evidencias', [
                'acao' => $acao,
                'evidencias' => $evidencias,
                'totalEvidencias' => count($evidencias)
            ])
        ]);
    }

    public function adicionarEvidencia($acaoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $tipo = $this->request->getPost('tipo');
        $rules = [
            'tipo' => 'required|in_list[texto,link]',
            'descricao' => 'permit_empty'
        ];

        if ($tipo === 'texto') {
            $rules['evidencia_texto'] = 'required|min_length[3]';
        } else {
            $rules['evidencia_link'] = 'required|valid_url';
        }

        if ($this->validate($rules)) {
            try {
                $evidencia = $tipo === 'texto' ? $this->request->getPost('evidencia_texto') : $this->request->getPost('evidencia_link');

                $data = [
                    'tipo' => $tipo,
                    'evidencia' => $evidencia,
                    'descricao' => $this->request->getPost('descricao'),
                    'nivel' => 'acao',
                    'id_nivel' => $acaoId,
                    'created_by' => auth()->id(),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $insertId = $this->evidenciasModel->insert($data);
                $novaEvidencia = $this->evidenciasModel->find($insertId);

                // Retornar a evidência criada para atualização em tempo real
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Evidência adicionada com sucesso!',
                    'evidencia' => $novaEvidencia
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Erro ao adicionar evidência: ' . $e->getMessage());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Erro ao adicionar evidência: ' . $e->getMessage()
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro de validação: ' . implode('<br>', $this->validator->getErrors())
            ]);
        }
    }
    public function removerEvidencia($evidenciaId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $evidencia = $this->evidenciasModel->find($evidenciaId);
            if (!$evidencia) {
                return $this->response->setJSON(['success' => false, 'message' => 'Evidência não encontrada']);
            }

            $acaoId = $evidencia['id_nivel'];
            $this->evidenciasModel->delete($evidenciaId);

            // Obter todas as evidências atualizadas
            $evidencias = $this->evidenciasModel->where('nivel', 'acao')
                ->where('id_nivel', $acaoId)
                ->orderBy('created_at', 'DESC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Evidência removida com sucesso!',
                'html' => view('components/acoes/conteudo-evidencias', [
                    'acao' => $this->acoesModel->find($acaoId),
                    'evidencias' => $evidencias,
                    'totalEvidencias' => count($evidencias)
                ])
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao remover evidência: ' . $e->getMessage()
            ]);
        }
    }

    public function carregarEquipeParaSolicitacao($acaoId)
    {
        try {
            $equipeAtual = $this->acoesModel->getEquipeAcao($acaoId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $equipeAtual,
                'equipeOriginal' => array_column($equipeAtual, 'id') // IDs dos membros originais
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao carregar equipe: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao carregar equipe'
            ]);
        }
    }
    public function listarEvidencias($acaoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $evidencias = $this->evidenciasModel->where('nivel', 'acao')
                ->where('id_nivel', $acaoId)
                ->orderBy('created_at', 'DESC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'evidencias' => $evidencias
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao listar evidências: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao listar evidências'
            ]);
        }
    }
    public function carregarAcoesParaOrdenacao($idOrigem, $tipoOrigem)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $builder = $this->acoesModel;

            if ($tipoOrigem === 'etapa') {
                $builder->where('id_etapa', $idOrigem);
            } else {
                $builder->where('id_projeto', $idOrigem)
                    ->where('id_etapa IS NULL');
            }

            $acoes = $builder->orderBy('ordem', 'ASC')->findAll();

            if (empty($acoes)) {
                return $this->response->setJSON([
                    'success' => true,
                    'html' => '<tr><td colspan="3" class="text-center">Nenhuma ação encontrada</td></tr>'
                ]);
            }

            $html = '';
            foreach ($acoes as $acao) {
                $html .= '
            <tr data-id="' . $acao['id'] . '">
                <td>' . esc($acao['nome']) . '</td>
                <td class="text-center">' . $acao['ordem'] . '</td>
                <td>
                    <select name="ordem[' . $acao['id'] . ']"
                        class="form-control form-control-sm ordem-select"
                        data-original="' . $acao['ordem'] . '">';

                for ($i = 1; $i <= count($acoes); $i++) {
                    $selected = $i == $acao['ordem'] ? 'selected' : '';
                    $html .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
                }

                $html .= '
                    </select>
                </td>
            </tr>';
            }

            return $this->response->setJSON([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao carregar ações para ordenação: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao carregar ações para ordenação',
                'html' => '<tr><td colspan="3" class="text-center text-danger">Erro ao carregar ações</td></tr>'
            ]);
        }
    }
    public function getAcoesAtrasadasUsuario()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $userId = auth()->id();

        try {
            $db = db_connect();

            $builder = $db->table('acoes as a')
                ->select('a.id, a.nome, a.entrega_estimada,
                     DATEDIFF(CURDATE(), a.entrega_estimada) as dias_atraso,
                     p.nome as projeto_nome')
                ->join('acoes_equipe as ae', 'ae.acao_id = a.id')
                ->join('projetos as p', 'p.id = a.id_projeto')
                ->where('ae.usuario_id', $userId)
                ->where('a.status', 'Atrasado')
                ->where('a.data_fim IS NULL') // Ainda não foi finalizada
                ->orderBy('a.entrega_estimada', 'ASC')
                ->get();

            $acoesAtrasadas = $builder->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $acoesAtrasadas
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar ações atrasadas: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar ações atrasadas'
            ]);
        }
    }

    public function getResponsaveis($acaoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $responsaveisModel = new \App\Models\ResponsaveisModel();
            $responsaveis = $responsaveisModel->getResponsaveis('acao', $acaoId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $responsaveis
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar responsáveis: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar responsáveis'
            ]);
        }
    }
}
