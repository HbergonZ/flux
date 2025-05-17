<?php

namespace App\Controllers;

use App\Models\AcoesModel;
use App\Models\EtapasModel;
use App\Models\ProjetosModel;
use App\Models\PlanosModel;
use App\Models\SolicitacoesModel;

class Acoes extends BaseController
{
    protected $acoesModel;
    protected $etapasModel;
    protected $projetosModel;
    protected $planosModel;
    protected $solicitacoesModel;

    public function __construct()
    {
        $this->acoesModel = new AcoesModel();
        $this->etapasModel = new EtapasModel();
        $this->projetosModel = new ProjetosModel();
        $this->planosModel = new PlanosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
    }

    public function index($idOrigem = null, $tipoOrigem = 'etapa')
    {
        if (empty($idOrigem)) {
            return redirect()->back();
        }

        $data = [];

        if ($tipoOrigem === 'etapa') {
            // Visualização por etapa
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
                'plano' => $plano, // Adicionando os dados do plano
                'acoes' => $acoes,
                'idOrigem' => $idOrigem,
                'tipoOrigem' => 'etapa',
                'acessoDireto' => false
            ];
        } else {
            // Visualização por projeto
            $projeto = $this->projetosModel->find($idOrigem);
            if (!$projeto) {
                return redirect()->back();
            }

            $plano = $this->planosModel->find($projeto['id_plano']);
            if (!$plano) {
                return redirect()->back();
            }

            // Busca todas as ações do projeto (diretas e de etapas)
            $acoes = $this->acoesModel->where('id_projeto', $idOrigem)
                ->orderBy('ordem', 'ASC')
                ->findAll();

            // Busca as etapas para exibição na view
            $etapas = $this->etapasModel->where('id_projeto', $idOrigem)
                ->findAll();

            $data = [
                'projeto' => $projeto,
                'plano' => $plano, // Adicionando os dados do plano
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
            'ordem' => 'required|integer',
            'responsavel' => 'permit_empty|max_length[255]',
            'equipe' => 'permit_empty|max_length[255]',
            'tempo_estimado_dias' => 'permit_empty|integer',
            'entrega_estimada' => 'permit_empty|valid_date',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
            'status' => 'permit_empty|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $this->request->getPost('ordem'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'entrega_estimada' => $this->request->getPost('entrega_estimada') ?: null,
                    'data_inicio' => $this->request->getPost('data_inicio') ?: null,
                    'data_fim' => $this->request->getPost('data_fim') ?: null,
                    'status' => $this->request->getPost('status') ?? 'Não iniciado'
                ];

                if ($tipoOrigem === 'projeto') {
                    $projeto = $this->projetosModel->find($idOrigem);
                    if (!$projeto) {
                        $response['message'] = 'Projeto não encontrado';
                        return $this->response->setJSON($response);
                    }
                    $data['id_projeto'] = $idOrigem;
                    $data['id_etapa'] = null;
                } else {
                    $etapa = $this->etapasModel->find($idOrigem);
                    if (!$etapa) {
                        $response['message'] = 'Etapa não encontrada';
                        return $this->response->setJSON($response);
                    }
                    $data['id_projeto'] = $etapa['id_projeto'];
                    $data['id_etapa'] = $idOrigem;
                }

                $this->acoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Ação cadastrada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar ação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($idAcao = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $acao = $this->acoesModel->find($idAcao);

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
            'id_acao' => 'required',
            'nome' => 'required|min_length[3]|max_length[255]',
            'ordem' => 'required|integer',
            'responsavel' => 'permit_empty|max_length[255]',
            'equipe' => 'permit_empty|max_length[255]',
            'tempo_estimado_dias' => 'permit_empty|integer',
            'entrega_estimada' => 'permit_empty|valid_date',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
            'status' => 'permit_empty|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id_acao' => $this->request->getPost('id_acao'),
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $this->request->getPost('ordem'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'entrega_estimada' => $this->request->getPost('entrega_estimada') ?: null,
                    'data_inicio' => $this->request->getPost('data_inicio') ?: null,
                    'data_fim' => $this->request->getPost('data_fim') ?: null,
                    'status' => $this->request->getPost('status') ?? 'Não iniciado'
                ];

                if ($tipoOrigem === 'projeto') {
                    $data['id_etapa'] = null;
                    $data['id_projeto'] = $idOrigem;
                } else {
                    $data['id_etapa'] = $idOrigem;
                }

                $this->acoesModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Ação atualizada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao atualizar ação: ' . $e->getMessage();
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
        $id = $this->request->getPost('id_acao');

        try {
            if ($this->acoesModel->delete($id)) {
                $response['success'] = true;
                $response['message'] = 'Ação excluída com sucesso!';
            } else {
                $response['message'] = 'Erro ao excluir ação: registro não encontrado';
            }
        } catch (\Exception $e) {
            $response['message'] = 'Erro ao excluir ação: ' . $e->getMessage();
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

        // Aplicar filtros
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

            // Filtro para ações onde a data_filtro está entre data_inicio e data_fim
            // OU se data_fim é nulo e data_filtro é após data_inicio
            $builder->groupStart()
                ->where('data_inicio <=', $dataFiltro)
                ->groupStart()
                ->where('data_fim >=', $dataFiltro)
                ->orWhere('data_fim IS NULL')
                ->groupEnd()
                ->groupEnd();
        }

        $acoes = $builder->orderBy('data_inicio', 'ASC')->findAll();

        return $this->response->setJSON(['success' => true, 'data' => $acoes]);
    }

    public function dadosAcao($idAcao = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $acao = $this->acoesModel->find($idAcao);

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
            'id_acao' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $acaoAtual = $this->acoesModel->find($postData['id_acao']);
                if (!$acaoAtual) {
                    $response['message'] = 'Ação não encontrada';
                    return $this->response->setJSON($response);
                }

                $projeto = $this->projetosModel->find($acaoAtual['id_projeto']);
                if (!$projeto) {
                    $response['message'] = 'Projeto relacionado não encontrado';
                    return $this->response->setJSON($response);
                }

                unset($acaoAtual['id_acao'], $acaoAtual['created_at'], $acaoAtual['updated_at']);

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
                    'nivel' => 'ação',
                    'id_solicitante' => auth()->id(),
                    'id_plano' => $projeto['id_plano'],
                    'id_projeto' => $acaoAtual['id_projeto'],
                    'id_etapa' => $acaoAtual['id_etapa'],
                    'id_acao' => $postData['id_acao'],
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
            'id_acao' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $acao = $this->acoesModel->find($postData['id_acao']);
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
                    'nivel' => 'ação',
                    'id_solicitante' => auth()->id(),
                    'id_plano' => $projeto['id_plano'],
                    'id_projeto' => $acao['id_projeto'],
                    'id_etapa' => $acao['id_etapa'],
                    'id_acao' => $postData['id_acao'],
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
                $dadosAlterados = [
                    'nome' => $postData['nome'],
                    'responsavel' => $postData['responsavel'] ?? null,
                    'equipe' => $postData['equipe'] ?? null,
                    'tempo_estimado_dias' => $postData['tempo_estimado_dias'] ?? null,
                    'entrega_estimada' => $postData['entrega_estimada'] ?? null,
                    'data_inicio' => $postData['data_inicio'] ?? null,
                    'data_fim' => $postData['data_fim'] ?? null,
                    'status' => $postData['status'] ?? 'Não iniciado',
                    'ordem' => $postData['ordem'] ?? null
                ];

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
                    'nivel' => 'ação',
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

        try {
            $this->acoesModel->transStart();

            foreach ($ordens as $idAcao => $ordem) {
                $this->acoesModel->update($idAcao, ['ordem' => (int)$ordem]);
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
}
