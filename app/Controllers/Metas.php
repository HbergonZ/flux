<?php

namespace App\Controllers;

use App\Models\MetasModel;
use App\Models\AcoesModel;

class Metas extends BaseController
{
    protected $metasModel;
    protected $acoesModel;

    public function __construct()
    {
        $this->metasModel = new MetasModel();
        $this->acoesModel = new AcoesModel();
    }

    public function index($idAcao = null)
    {
        if (empty($idAcao)) {
            return redirect()->to('/acoes');
        }

        // Busca a ação para exibir o nome
        $acao = $this->acoesModel->find($idAcao);
        if (!$acao) {
            return redirect()->to('/acoes');
        }

        // Busca as metas vinculadas à ação
        $metas = $this->metasModel->getMetasByAcao($idAcao);

        $data = [
            'acao' => $acao,
            'metas' => $metas,
            'idAcao' => $idAcao
        ];

        $this->content_data['content'] = view('sys/metas', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($idAcao)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/metas/$idAcao");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'id_acao' => $idAcao
                ];

                $this->metasModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Meta cadastrada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar meta: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($idMeta = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/acoes');
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $meta = $this->metasModel->find($idMeta);

        if ($meta) {
            $response['success'] = true;
            $response['data'] = $meta;
        } else {
            $response['message'] = 'Meta não encontrada';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar($idAcao)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/metas/$idAcao");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required',
            'nome' => 'required|min_length[3]|max_length[255]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id' => $this->request->getPost('id'),
                    'nome' => $this->request->getPost('nome')
                ];

                $this->metasModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Meta atualizada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao atualizar meta: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function excluir($idAcao)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/metas/$idAcao");
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        if ($this->metasModel->delete($id)) {
            $response['success'] = true;
            $response['message'] = 'Meta excluída com sucesso!';
        } else {
            $response['message'] = 'Erro ao excluir meta';
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($idAcao)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/metas/$idAcao");
        }

        $filtro = $this->request->getPost('nome');

        $builder = $this->metasModel->where('id_acao', $idAcao);

        if (!empty($filtro)) {
            $builder->like('nome', $filtro);
        }

        $metas = $builder->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $metas]);
    }
}
