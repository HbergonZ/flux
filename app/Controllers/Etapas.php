<?php

namespace App\Controllers;

use App\Models\EtapasModel;
use App\Models\PlanosModel;
use App\Models\ProjetosModel;
use App\Models\SolicitacoesModel;

class Etapas extends BaseController
{
    protected $etapasModel;
    protected $planosModel;
    protected $projetosModel;
    protected $solicitacoesModel;

    public function __construct()
    {
        $this->etapasModel = new EtapasModel();
        $this->projetosModel = new ProjetosModel();
        $this->planosModel = new PlanosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
    }

    public function index($idProjeto = null)
    {
        if (empty($idProjeto)) {
            return redirect()->back();
        }

        $projeto = $this->projetosModel->find($idProjeto);
        if (!$projeto) {
            return redirect()->back();
        }

        $plano = $this->planosModel->find($projeto['id_plano']);
        if (!$plano) {
            return redirect()->back();
        }

        $etapas = $this->etapasModel->getEtapasByProjeto($idProjeto);

        $data = [
            'projeto' => $projeto,
            'plano' => $plano,
            'etapas' => $etapas,
            'idProjeto' => $idProjeto
        ];

        $this->content_data['content'] = view('sys/etapas', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/projetos/$idProjeto/etapas");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]',
            'ordem' => 'permit_empty|integer'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $this->request->getPost('ordem'),
                    'id_projeto' => $idProjeto
                ];

                $this->etapasModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Etapa cadastrada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar etapa: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($idEtapa = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $etapa = $this->etapasModel->find($idEtapa);

        if ($etapa) {
            $response['success'] = true;
            $response['data'] = $etapa;
        } else {
            $response['message'] = 'Etapa não encontrada';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/projetos/$idProjeto/etapas");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required',
            'nome' => 'required|min_length[3]|max_length[255]',
            'ordem' => 'permit_empty|integer'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id' => $this->request->getPost('id'),
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $this->request->getPost('ordem')
                ];

                $this->etapasModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Etapa atualizada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao atualizar etapa: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function excluir($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/projetos/$idProjeto/etapas");
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        try {
            if ($this->etapasModel->delete($id)) {
                $response['success'] = true;
                $response['message'] = 'Etapa excluída com sucesso!';
            } else {
                $response['message'] = 'Erro ao excluir etapa: registro não encontrado';
            }
        } catch (\Exception $e) {
            $response['message'] = 'Erro ao excluir etapa: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/projetos/$idProjeto/etapas");
        }

        $filtro = $this->request->getPost('nome');

        $builder = $this->etapasModel->where('id_projeto', $idProjeto);

        if (!empty($filtro)) {
            $builder->like('nome', $filtro);
        }

        $etapas = $builder->orderBy('data_criacao', 'ASC')->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $etapas]);
    }

    public function dadosEtapa($idEtapa = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $etapa = $this->etapasModel->find($idEtapa);

        if ($etapa) {
            $response['success'] = true;
            $response['data'] = $etapa;
        } else {
            $response['message'] = 'Etapa não encontrada';
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
            'id_etapa' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $etapaAtual = $this->etapasModel->find($postData['id_etapa']);
                if (!$etapaAtual) {
                    $response['message'] = 'Etapa não encontrada';
                    return $this->response->setJSON($response);
                }

                $alteracoes = [];
                $camposEditaveis = ['nome', 'ordem'];

                foreach ($camposEditaveis as $campo) {
                    if (isset($postData[$campo]) && $postData[$campo] != $etapaAtual[$campo]) {
                        $alteracoes[$campo] = [
                            'de' => $etapaAtual[$campo],
                            'para' => $postData[$campo]
                        ];
                    }
                }

                if (empty($alteracoes)) {
                    $response['message'] = 'Nenhuma alteração detectada';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'nivel' => 'etapa',
                    'id_etapa' => $postData['id_etapa'],
                    'id_projeto' => $etapaAtual['id_projeto'],
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($etapaAtual, JSON_UNESCAPED_UNICODE),
                    'dados_alterados' => json_encode($alteracoes, JSON_UNESCAPED_UNICODE),
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
            'id_etapa' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $etapa = $this->etapasModel->find($postData['id_etapa']);
                if (!$etapa) {
                    $response['message'] = 'Etapa não encontrada';
                    return $this->response->setJSON($response);
                }

                $dadosAtuais = [
                    'nome' => $etapa['nome'],
                    'ordem' => $etapa['ordem'],
                    'id_projeto' => $etapa['id_projeto']
                ];

                $data = [
                    'nivel' => 'etapa',
                    'id_etapa' => $postData['id_etapa'],
                    'id_projeto' => $etapa['id_projeto'],
                    'tipo' => 'Exclusão',
                    'dados_atuais' => json_encode($dadosAtuais, JSON_UNESCAPED_UNICODE),
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
                $dadosAlterados = [
                    'nome' => $postData['nome'],
                    'ordem' => $postData['ordem'] ?? null,
                    'id_projeto' => $postData['id_projeto']
                ];

                $data = [
                    'nivel' => 'etapa',
                    'id_projeto' => $postData['id_projeto'],
                    'tipo' => 'Inclusão',
                    'dados_alterados' => json_encode($dadosAlterados, JSON_UNESCAPED_UNICODE),
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
}
