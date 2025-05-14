<?php

namespace App\Controllers;

use App\Models\AcoesModel;
use App\Models\EtapasModel;
use App\Models\ProjetosModel;
use App\Models\SolicitacoesModel;

class Acoes extends BaseController
{
    protected $acoesModel;
    protected $etapasModel;
    protected $projetosModel;
    protected $solicitacoesModel;

    public function __construct()
    {
        $this->acoesModel = new AcoesModel();
        $this->etapasModel = new EtapasModel();
        $this->projetosModel = new ProjetosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
    }

    public function index($idOrigem = null, $tipoOrigem = 'etapa')
    {
        if (empty($idOrigem)) {
            return redirect()->back();
        }

        $data = [];

        if ($tipoOrigem === 'etapa') {
            // Lógica para ações de etapa
            $etapa = $this->etapasModel->find($idOrigem);
            if (!$etapa) {
                return redirect()->back();
            }

            $projeto = $this->projetosModel->find($etapa['id_projeto']);
            $acoes = $this->acoesModel->where('id_etapa', $idOrigem)
                ->orderBy('ordem', 'ASC')
                ->findAll();

            $data = [
                'etapa' => $etapa,
                'projeto' => $projeto,
                'acoes' => $acoes,
                'idOrigem' => $idOrigem,
                'tipoOrigem' => 'etapa',
                'acessoDireto' => false
            ];
        } else {
            // Lógica para ações diretas de projeto
            $projeto = $this->projetosModel->find($idOrigem);
            if (!$projeto) {
                return redirect()->back();
            }

            $acoes = $this->acoesModel->where('id_projeto', $idOrigem)
                ->orderBy('ordem', 'ASC')
                ->findAll();

            $data = [
                'projeto' => $projeto,
                'acoes' => $acoes,
                'idOrigem' => $idOrigem,
                'tipoOrigem' => 'projeto',
                'acessoDireto' => true
            ];
        }

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($idEtapa)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/etapas/$idEtapa/acoes");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]',
            'responsavel' => 'permit_empty|max_length[255]',
            'equipe' => 'permit_empty|max_length[255]',
            'tempo_estimado_dias' => 'permit_empty|integer',
            'inicio_estimado' => 'permit_empty|valid_date',
            'fim_estimado' => 'permit_empty|valid_date',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
            'status' => 'permit_empty|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]',
            'ordem' => 'permit_empty|integer'
        ];

        if ($this->validate($rules)) {
            try {
                $etapa = $this->etapasModel->find($idEtapa);
                if (!$etapa) {
                    $response['message'] = 'Etapa não encontrada';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'inicio_estimado' => $this->request->getPost('inicio_estimado'),
                    'fim_estimado' => $this->request->getPost('fim_estimado'),
                    'data_inicio' => $this->request->getPost('data_inicio'),
                    'data_fim' => $this->request->getPost('data_fim'),
                    'status' => $this->request->getPost('status'),
                    'ordem' => $this->request->getPost('ordem'),
                    'id_projeto' => $etapa['id_projeto'],
                    'id_etapa' => $idEtapa
                ];

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

    public function atualizar($idEtapa)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/etapas/$idEtapa/acoes");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id_acao' => 'required',
            'nome' => 'required|min_length[3]|max_length[255]',
            'responsavel' => 'permit_empty|max_length[255]',
            'equipe' => 'permit_empty|max_length[255]',
            'tempo_estimado_dias' => 'permit_empty|integer',
            'inicio_estimado' => 'permit_empty|valid_date',
            'fim_estimado' => 'permit_empty|valid_date',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
            'status' => 'permit_empty|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]',
            'ordem' => 'permit_empty|integer'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id_acao' => $this->request->getPost('id_acao'),
                    'nome' => $this->request->getPost('nome'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'inicio_estimado' => $this->request->getPost('inicio_estimado'),
                    'fim_estimado' => $this->request->getPost('fim_estimado'),
                    'data_inicio' => $this->request->getPost('data_inicio'),
                    'data_fim' => $this->request->getPost('data_fim'),
                    'status' => $this->request->getPost('status'),
                    'ordem' => $this->request->getPost('ordem')
                ];

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

    public function excluir($idEtapa)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/etapas/$idEtapa/acoes");
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

        $filtro = $this->request->getPost('nome');

        $builder = $this->acoesModel;

        if ($tipoOrigem === 'etapa') {
            $builder->where('id_etapa', $idOrigem);
        } else {
            $builder->where('id_projeto', $idOrigem)
                ->where('id_etapa IS NULL');
        }

        if (!empty($filtro)) {
            $builder->like('nome', $filtro);
        }

        $acoes = $builder->orderBy('ordem', 'ASC')->findAll();
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

                $alteracoes = [];
                $camposEditaveis = [
                    'nome',
                    'responsavel',
                    'equipe',
                    'tempo_estimado_dias',
                    'inicio_estimado',
                    'fim_estimado',
                    'data_inicio',
                    'data_fim',
                    'status',
                    'ordem'
                ];

                foreach ($camposEditaveis as $campo) {
                    if (isset($postData[$campo]) && $postData[$campo] != $acaoAtual[$campo]) {
                        $alteracoes[$campo] = [
                            'de' => $acaoAtual[$campo],
                            'para' => $postData[$campo]
                        ];
                    }
                }

                if (empty($alteracoes)) {
                    $response['message'] = 'Nenhuma alteração detectada';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'nivel' => 'acao',
                    'id_acao' => $postData['id_acao'],
                    'id_etapa' => $acaoAtual['id_etapa'],
                    'id_projeto' => $acaoAtual['id_projeto'],
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($acaoAtual),
                    'dados_alterados' => json_encode($alteracoes),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'solicitante' => auth()->user()->username,
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

                $dadosAtuais = [
                    'nome' => $acao['nome'],
                    'responsavel' => $acao['responsavel'],
                    'equipe' => $acao['equipe'],
                    'status' => $acao['status'],
                    'id_etapa' => $acao['id_etapa'],
                    'id_projeto' => $acao['id_projeto']
                ];

                $data = [
                    'nivel' => 'acao',
                    'id_acao' => $postData['id_acao'],
                    'id_etapa' => $acao['id_etapa'],
                    'id_projeto' => $acao['id_projeto'],
                    'tipo' => 'Exclusão',
                    'dados_atuais' => json_encode($dadosAtuais),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'solicitante' => auth()->user()->username,
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
                $etapa = $this->etapasModel->find($postData['id_etapa']);
                if (!$etapa) {
                    $response['message'] = 'Etapa não encontrada';
                    return $this->response->setJSON($response);
                }

                $dadosAlterados = [
                    'nome' => $postData['nome'],
                    'responsavel' => $postData['responsavel'] ?? null,
                    'equipe' => $postData['equipe'] ?? null,
                    'tempo_estimado_dias' => $postData['tempo_estimado_dias'] ?? null,
                    'inicio_estimado' => $postData['inicio_estimado'] ?? null,
                    'fim_estimado' => $postData['fim_estimado'] ?? null,
                    'data_inicio' => $postData['data_inicio'] ?? null,
                    'data_fim' => $postData['data_fim'] ?? null,
                    'status' => $postData['status'] ?? 'Não iniciado',
                    'ordem' => $postData['ordem'] ?? null,
                    'id_projeto' => $etapa['id_projeto'],
                    'id_etapa' => $postData['id_etapa']
                ];

                $data = [
                    'nivel' => 'acao',
                    'id_etapa' => $postData['id_etapa'],
                    'id_projeto' => $etapa['id_projeto'],
                    'tipo' => 'Inclusão',
                    'dados_alterados' => json_encode($dadosAlterados),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'solicitante' => auth()->user()->username,
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
    public function acoesProjeto($idProjeto)
    {
        $projeto = $this->projetosModel->find($idProjeto);
        if (!$projeto) {
            return redirect()->back();
        }

        $acoes = $this->acoesModel->where('id_projeto', $idProjeto)
            ->where('id_etapa IS NULL')
            ->orderBy('ordem', 'ASC')
            ->findAll();

        $data = [
            'projeto' => $projeto,
            'acoes' => $acoes,
            'idProjeto' => $idProjeto,
            'acessoDireto' => true
        ];

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
    }
}
