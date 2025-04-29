<?php

namespace App\Controllers;

use App\Models\AcoesModel;
use App\Models\PlanosModel;

class Acoes extends BaseController
{
    protected $acoesModel;
    protected $planosModel;

    public function __construct()
    {
        $this->acoesModel = new AcoesModel();
        $this->planosModel = new PlanosModel();
    }

    public function index($idPlano = null)
    {
        if (empty($idPlano)) {
            return redirect()->to('/planos');
        }

        // Busca o plano para exibir o nome
        $plano = $this->planosModel->find($idPlano);
        if (!$plano) {
            return redirect()->to('/planos');
        }

        // Busca as ações vinculadas ao plano
        $acoes = $this->acoesModel->getAcoesByPlano($idPlano);

        $data = [
            'plano' => $plano,
            'acoes' => $acoes,
            'idPlano' => $idPlano
        ];

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/acoes/$idPlano");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'identificador' => 'required|max_length[10]',
            'acao' => 'required|max_length[255]',
            'descricao' => 'permit_empty',
            'projeto_vinculado' => 'permit_empty|max_length[255]',
            'id_eixo' => 'permit_empty|integer',
            'responsaveis' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'identificador' => $this->request->getPost('identificador'),
                    'acao' => $this->request->getPost('acao'),
                    'descricao' => $this->request->getPost('descricao'),
                    'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
                    'id_eixo' => $this->request->getPost('id_eixo'),
                    'id_projeto' => $idPlano,
                    'responsaveis' => $this->request->getPost('responsaveis')
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
            return redirect()->to('/planos');
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

    public function atualizar($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/acoes/$idPlano");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required',
            'identificador' => 'required|max_length[10]',
            'acao' => 'required|max_length[255]',
            'descricao' => 'permit_empty',
            'projeto_vinculado' => 'permit_empty|max_length[255]',
            'id_eixo' => 'permit_empty|integer',
            'responsaveis' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id' => $this->request->getPost('id'),
                    'identificador' => $this->request->getPost('identificador'),
                    'acao' => $this->request->getPost('acao'),
                    'descricao' => $this->request->getPost('descricao'),
                    'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
                    'id_eixo' => $this->request->getPost('id_eixo'),
                    'responsaveis' => $this->request->getPost('responsaveis')
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

    public function excluir($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/acoes/$idPlano");
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        if ($this->acoesModel->delete($id)) {
            $response['success'] = true;
            $response['message'] = 'Ação excluída com sucesso!';
        } else {
            $response['message'] = 'Erro ao excluir ação';
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/acoes/$idPlano");
        }

        $filtros = [
            'identificador' => $this->request->getPost('identificador'),
            'acao' => $this->request->getPost('acao'),
            'projeto_vinculado' => $this->request->getPost('projeto_vinculado')
        ];

        $builder = $this->acoesModel->where('id_projeto', $idPlano);

        if (!empty($filtros['identificador'])) {
            $builder->like('identificador', $filtros['identificador']);
        }

        if (!empty($filtros['acao'])) {
            $builder->like('acao', $filtros['acao']);
        }

        if (!empty($filtros['projeto_vinculado'])) {
            $builder->like('projeto_vinculado', $filtros['projeto_vinculado']);
        }

        $acoes = $builder->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $acoes]);
    }
}
