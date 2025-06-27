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

        $builder->select('acoes.*, GROUP_CONCAT(DISTINCT users.name SEPARATOR ", ") as responsaveis')
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
            'responsaveis_ids' => 'permit_empty',
            'entrega_estimada' => 'permit_empty|valid_date',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
            'evidencias_adicionadas' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $proximaOrdem = $this->getProximaOrdem($idOrigem, $tipoOrigem);
                $responsaveisModel = new \App\Models\ResponsaveisModel();

                // Dados básicos da ação
                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $proximaOrdem,
                    'responsavel' => '', // Agora usamos o sistema de responsáveis
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias') ?: null,
                    'entrega_estimada' => $this->request->getPost('entrega_estimada') ?: null,
                    'data_inicio' => $this->request->getPost('data_inicio') ?: null,
                    'data_fim' => $this->request->getPost('data_fim') ?: null
                ];

                // Define a origem (etapa ou projeto direto)
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

                // Calcula o status automaticamente
                $statusProjeto = $projeto['status'] ?? null;
                $data['status'] = $this->acoesModel->calcularStatus($data, $statusProjeto);

                if ($this->acoesModel->transStatus()) {
                    // Atualiza status de todas as ações após inserção
                    $this->acoesModel->atualizarStatusAcoes($idOrigem, $tipoOrigem);
                }

                // Validação adicional para data fim
                if (!empty($data['data_fim'])) {
                    if (empty($data['data_inicio'])) {
                        $response['message'] = 'Não é possível definir data de fim sem data de início';
                        return $this->response->setJSON($response);
                    }
                }

                $this->acoesModel->transStart();
                $insertId = $this->acoesModel->insert($data);

                if (!$insertId) {
                    throw new \Exception('Falha ao inserir ação no banco de dados');
                }

                // Processar responsáveis
                $responsaveisIds = $this->request->getPost('responsaveis_ids');
                if (!empty($responsaveisIds)) {
                    $idsArray = explode(',', $responsaveisIds);

                    foreach ($idsArray as $usuarioId) {
                        if (!empty($usuarioId)) {
                            $responsaveisModel->insert([
                                'nivel' => 'acao',
                                'nivel_id' => $insertId,
                                'usuario_id' => $usuarioId
                            ]);
                        }
                    }
                }

                // Processar evidências (se houver)
                $evidenciasAdicionadas = $this->request->getPost('evidencias_adicionadas');
                if (!empty($evidenciasAdicionadas)) {
                    $evidencias = json_decode($evidenciasAdicionadas, true);
                    $evidenciasModel = new \App\Models\EvidenciasModel();

                    foreach ($evidencias as $evidencia) {
                        $evidenciaData = [
                            'tipo' => $evidencia['tipo'],
                            'evidencia' => $evidencia['conteudo'],
                            'descricao' => $evidencia['descricao'] ?? null,
                            'nivel' => 'acao',
                            'id_nivel' => $insertId,
                            'created_by' => auth()->id(),
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $evidenciasModel->insert($evidenciaData);
                    }
                }

                // Registrar log
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

    // Novo método para buscar usuários
    public function buscarUsuariosParaResponsaveis()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $term = $this->request->getGet('term'); // Para busca com Select2
            $acaoId = $this->request->getGet('acao_id'); // Para filtrar usuários já associados

            $db = db_connect();
            $builder = $db->table('users u')
                ->select('u.id, u.name, ai.secret as email')
                ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
                ->orderBy('u.name', 'ASC');

            // Filtro por termo de busca
            if (!empty($term)) {
                $builder->groupStart()
                    ->like('u.name', $term)
                    ->orLike('ai.secret', $term)
                    ->groupEnd();
            }

            // Filtro para não incluir usuários já responsáveis
            if (!empty($acaoId)) {
                $builder->whereNotIn('u.id', function ($query) use ($acaoId) {
                    $query->select('usuario_id')
                        ->from('responsaveis')
                        ->where('nivel', 'acao')
                        ->where('nivel_id', $acaoId);
                });
            }

            $usuarios = $builder->get()->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $usuarios
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar usuários: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar usuários'
            ]);
        }
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
            'responsaveis_ids' => 'permit_empty',
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
            'evidencias_adicionadas' => 'permit_empty',
            'evidencias_removidas' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $id = $this->request->getPost('id');
                $acaoAntiga = $this->acoesModel->find($id);

                if (!$acaoAntiga) {
                    $response['message'] = 'Ação não encontrada';
                    return $this->response->setJSON($response);
                }

                // Validação adicional para data fim
                if (!empty($this->request->getPost('data_fim'))) {
                    $temEvidencias = $this->evidenciasModel->where('nivel', 'acao')
                        ->where('id_nivel', $id)
                        ->countAllResults() > 0;

                    if (!$temEvidencias) {
                        $response['message'] = 'Para definir uma data de fim, é necessário cadastrar pelo menos uma evidência.';
                        return $this->response->setJSON($response);
                    }
                }

                $this->acoesModel->transStart();

                // Dados básicos da ação
                $data = [
                    'id' => $id,
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $this->request->getPost('ordem'),
                    'responsavel' => '', // Agora usamos o sistema de responsáveis
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias') ?: null,
                    'entrega_estimada' => $this->request->getPost('entrega_estimada') ?: null,
                    'data_inicio' => $this->request->getPost('data_inicio') ?: null,
                    'data_fim' => $this->request->getPost('data_fim') ?: null,
                    'status' => $this->calcularStatusNovo($this->request->getPost())
                ];

                $updated = $this->acoesModel->save($data);

                if (!$updated) {
                    throw new \Exception('Falha ao atualizar ação no banco de dados');
                }

                // Processar responsáveis
                $responsaveisIds = $this->request->getPost('responsaveis_ids');
                $this->processarResponsaveis($id, $responsaveisIds);

                // Processar evidências adicionadas
                $evidenciasAdicionadas = $this->request->getPost('evidencias_adicionadas');
                if (!empty($evidenciasAdicionadas)) {
                    $evidencias = json_decode($evidenciasAdicionadas, true);
                    foreach ($evidencias as $evidencia) {
                        $evidenciaData = [
                            'tipo' => $evidencia['tipo'],
                            'evidencia' => $evidencia['conteudo'],
                            'descricao' => $evidencia['descricao'] ?? null,
                            'nivel' => 'acao',
                            'id_nivel' => $id,
                            'created_by' => auth()->id(),
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $this->evidenciasModel->insert($evidenciaData);
                    }
                }

                // Processar evidências removidas
                $evidenciasRemovidas = $this->request->getPost('evidencias_removidas');
                if (!empty($evidenciasRemovidas)) {
                    $idsRemover = json_decode($evidenciasRemovidas, true);
                    $this->evidenciasModel->whereIn('id', $idsRemover)
                        ->where('nivel', 'acao')
                        ->where('id_nivel', $id)
                        ->delete();
                }

                // Registrar log
                $acaoAtualizada = $this->acoesModel->find($id);
                if (!$this->logController->registrarEdicao('acao', $acaoAntiga, $acaoAtualizada, 'Edição realizada via interface')) {
                    throw new \Exception('Falha ao registrar log de edição');
                }

                if ($this->acoesModel->transStatus()) {
                    // Atualiza status de todas as ações após inserção
                    $this->acoesModel->atualizarStatusAcoes($idOrigem, $tipoOrigem);
                }

                $this->acoesModel->transComplete();

                $response['success'] = true;
                $response['message'] = 'Ação atualizada com sucesso!';
                $response['data'] = $acaoAtualizada;
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

    private function processarResponsaveis($acaoId, $responsaveisIds)
    {
        $responsaveisModel = new \App\Models\ResponsaveisModel();

        // Remove todos os responsáveis atuais
        $responsaveisModel->where('nivel', 'acao')
            ->where('nivel_id', $acaoId)
            ->delete();

        // Adiciona os novos responsáveis
        if (!empty($responsaveisIds)) {
            $idsArray = explode(',', $responsaveisIds);
            $data = [];

            foreach ($idsArray as $usuarioId) {
                if (!empty($usuarioId)) {
                    $data[] = [
                        'nivel' => 'acao',
                        'nivel_id' => $acaoId,
                        'usuario_id' => $usuarioId
                    ];
                }
            }

            if (!empty($data)) {
                $responsaveisModel->insertBatch($data);
            }
        }
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

            if ($this->acoesModel->transStatus()) {
                // Atualiza status de todas as ações após inserção
                $this->acoesModel->atualizarStatusAcoes($idOrigem, $tipoOrigem);
            }

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
        $builder->select('acoes.*, GROUP_CONCAT(DISTINCT users.name SEPARATOR ", ") as responsaveis')
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
            $builder->like('users.name', $filters['responsavel']);
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

    public function getUsuariosDisponiveis($acaoId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $term = $this->request->getGet('term'); // Termo de busca opcional

            $db = db_connect();
            $builder = $db->table('users u')
                ->select('u.id, u.name, ai.secret as email')
                ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
                ->orderBy('u.name', 'ASC');

            // Filtro para não incluir usuários já responsáveis
            $builder->whereNotIn('u.id', function ($query) use ($acaoId) {
                $query->select('usuario_id')
                    ->from('responsaveis')
                    ->where('nivel', 'acao')
                    ->where('nivel_id', $acaoId);
            });

            // Filtro por termo de busca se existir
            if (!empty($term)) {
                $builder->groupStart()
                    ->like('u.name', $term)
                    ->orLike('ai.secret', $term)
                    ->groupEnd();
            }

            $usuarios = $builder->get()->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $usuarios
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao buscar usuários disponíveis: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao buscar usuários disponíveis'
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
            $responsaveis = $responsaveisModel->getResponsaveisAcao($acaoId);

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

    public function adicionarResponsavel()
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
                $responsaveisModel = new \App\Models\ResponsaveisModel();

                $data = [
                    'nivel' => 'acao',
                    'nivel_id' => $postData['acao_id'],
                    'usuario_id' => $postData['usuario_id']
                ];

                $responsaveisModel->insert($data);

                $response['success'] = true;
                $response['message'] = 'Responsável adicionado com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao adicionar responsável: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function removerResponsavel()
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
                $responsaveisModel = new \App\Models\ResponsaveisModel();

                $responsaveisModel->where('nivel', 'acao')
                    ->where('nivel_id', $postData['acao_id'])
                    ->where('usuario_id', $postData['usuario_id'])
                    ->delete();

                $response['success'] = true;
                $response['message'] = 'Responsável removido com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao remover responsável: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }
}
