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

    public function index($idOrigem = null, $tipoOrigem = 'etapa')
    {
        if (empty($idOrigem)) {
            return redirect()->back();
        }

        $data = [];
        $etapa = null;

        if ($tipoOrigem === 'etapa') {
            $etapa = $this->etapasModel->find($idOrigem);
            if (!$etapa) {
                return redirect()->back();
            }

            $projeto = $this->projetosModel->find($etapa['id_projeto']);
            if (!$projeto) {
                return redirect()->back();
            }

            $plano = $this->planosModel->find($projeto['id_plano']);
            if (!$plano) {
                return redirect()->back();
            }

            $acoes = $this->acoesModel->where('id_etapa', $idOrigem)
                ->orderBy('ordem', 'ASC')
                ->findAll();

            $data = [
                'etapa' => $etapa,
                'projeto' => $projeto,
                'plano' => $plano,
                'acoes' => $acoes,
                'idOrigem' => $idOrigem,
                'tipoOrigem' => 'etapa',
                'acessoDireto' => false
            ];
        } else {
            $projeto = $this->projetosModel->find($idOrigem);
            if (!$projeto) {
                return redirect()->back();
            }

            $plano = $this->planosModel->find($projeto['id_plano']);
            if (!$plano) {
                return redirect()->back();
            }

            $acoes = $this->acoesModel->where('id_projeto', $idOrigem)
                ->orderBy('ordem', 'ASC')
                ->findAll();

            $etapas = $this->etapasModel->where('id_projeto', $idOrigem)
                ->findAll();

            $data = [
                'etapa' => null,
                'projeto' => $projeto,
                'plano' => $plano,
                'acoes' => $acoes,
                'etapas' => $etapas,
                'idOrigem' => $idOrigem,
                'tipoOrigem' => 'projeto',
                'acessoDireto' => true
            ];
        }

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
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
                    'equipe' => $this->request->getPost('equipe'),
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
            'equipe' => 'permit_empty|max_length[255]',
            'tempo_estimado_dias' => 'permit_empty|integer',
            'entrega_estimada' => 'permit_empty|valid_date',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
        ];

        if ($this->validate($rules)) {
            try {
                $id = $this->request->getPost('id');
                $acaoAntiga = $this->acoesModel->find($id);
                if (!$acaoAntiga) {
                    $response['message'] = 'Ação não encontrada';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'id' => $id,
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $this->request->getPost('ordem'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'entrega_estimada' => $this->request->getPost('entrega_estimada') ?: null,
                    'data_inicio' => $this->request->getPost('data_inicio') ?: null,
                    'data_fim' => $this->request->getPost('data_fim') ?: null
                ];

                if ($tipoOrigem === 'projeto') {
                    $data['id_etapa'] = null;
                    $data['id_projeto'] = $idOrigem;
                } else {
                    $data['id_etapa'] = $idOrigem;
                }

                // Calculate status automatically
                $statusProjeto = $this->projetosModel->find($data['id_projeto'])['status'] ?? null;
                $data['status'] = $this->acoesModel->calcularStatus($data, $statusProjeto);

                $this->acoesModel->transStart();
                $updated = $this->acoesModel->save($data);

                if (!$updated) {
                    throw new \Exception('Falha ao atualizar ação no banco de dados');
                }

                $acaoAtualizada = $this->acoesModel->find($id);

                if (!$this->logController->registrarEdicao('acao', $acaoAntiga, $acaoAtualizada, 'Edição realizada via interface')) {
                    throw new \Exception('Falha ao registrar log de edição');
                }

                if (!empty($data['data_fim'])) {
                    $temEvidencias = $this->evidenciasModel->where('nivel', 'acao')
                        ->where('id_nivel', $id)
                        ->countAllResults() > 0;

                    if (!$temEvidencias) {
                        $response['message'] = 'Para definir uma data de fim, é necessário cadastrar pelo menos uma evidência.';
                        return $this->response->setJSON($response);
                    }
                }

                $this->acoesModel->transComplete();

                $response['success'] = true;
                $response['message'] = 'Ação atualizada com sucesso!';
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

        if ($tipoOrigem === 'etapa') {
            $builder->where('id_etapa', $idOrigem);
        } else {
            $builder->where('id_projeto', $idOrigem)
                ->where('id_etapa IS NULL');
        }

        if (!empty($filters['nome'])) {
            $builder->like('nome', $filters['nome']);
        }

        if (!empty($filters['responsavel'])) {
            $builder->like('responsavel', $filters['responsavel']);
        }

        if (!empty($filters['equipe'])) {
            $builder->like('equipe', $filters['equipe']);
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (!empty($filters['data_filtro'])) {
            $dataFiltro = $filters['data_filtro'];
            $builder->groupStart()
                ->where('data_inicio <=', $dataFiltro)
                ->groupStart()
                ->where('data_fim >=', $dataFiltro)
                ->orWhere('data_fim IS NULL')
                ->groupEnd()
                ->groupEnd();
        }

        $acoes = $builder->orderBy('ordem', 'ASC')->findAll();

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

                unset($acaoAtual['id'], $acaoAtual['created_at'], $acaoAtual['updated_at']);

                if (!empty($postData['data_fim'])) {
                    if (empty($postData['evidencias'])) {
                        $response['message'] = 'Ao definir uma data fim, é obrigatório informar as evidências.';
                        return $this->response->setJSON($response);
                    }

                    // Adiciona as evidências aos dados alterados
                    $alteracoes['evidencias'] = [
                        'de' => null,
                        'para' => $postData['evidencias']
                    ];
                }

                $alteracoes = [];
                $camposEditaveis = [
                    'nome',
                    'responsavel',
                    'equipe',
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

                        if ($valorAtual != $valorNovo) {
                            $alteracoes[$campo] = [
                                'de' => $valorAtual,
                                'para' => $valorNovo
                            ];
                        }
                    }
                }

                if (empty($alteracoes)) {
                    $response['message'] = 'Nenhuma alteração detectada';
                    return $this->response->setJSON($response);
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
                    'equipe' => $postData['equipe'] ?? null,
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
        $acaoId = $this->request->getGet('acao_id');

        try {
            $db = db_connect();

            // Busca usuários que não estão na equipe desta ação
            $subquery = $db->table('acoes_equipe')
                ->select('usuario_id')
                ->where('acao_id', $acaoId);

            $usuarios = $db->table('users')
                ->select('users.id, users.username, auth_identities.secret as email')
                ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"')
                ->where('users.active', 1)
                ->whereNotIn('users.id', $subquery)
                ->orderBy('users.username', 'ASC')
                ->get()
                ->getResultArray();

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

        $rules = [
            'tipo' => 'required|in_list[texto,link]',
            'evidencia_texto' => 'permit_empty|min_length[3]',
            'evidencia_link' => 'permit_empty|valid_url',
            'descricao' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $tipo = $this->request->getPost('tipo');
                $evidencia = $tipo === 'texto' ? $this->request->getPost('evidencia_texto') : $this->request->getPost('evidencia_link');

                $data = [
                    'tipo' => $tipo,
                    'evidencia' => $evidencia,
                    'descricao' => $this->request->getPost('descricao'),
                    'nivel' => 'acao',
                    'id_nivel' => $acaoId,
                    'created_by' => auth()->id()
                ];

                $this->evidenciasModel->insert($data);

                // Obter todas as evidências atualizadas
                $evidencias = $this->evidenciasModel->where('nivel', 'acao')
                    ->where('id_nivel', $acaoId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Evidência adicionada com sucesso!',
                    'evidencias' => $evidencias,
                    'totalEvidencias' => count($evidencias)
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $this->validator->getErrors())
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

            $this->evidenciasModel->delete($evidenciaId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Evidência removida com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao remover evidência: ' . $e->getMessage()
            ]);
        }
    }
}
