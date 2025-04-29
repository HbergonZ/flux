<?php

namespace App\Controllers;

use App\Models\PlanosModel;

class Planos extends BaseController
{
    protected $planoModel;

    public function __construct()
    {
        $this->planoModel = new PlanosModel();
    }

    public function index(): string
    {
        $planos = $this->planoModel->findAll();
        $data['planos'] = $planos;

        $this->content_data['content'] = view('sys/planos', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]',
            'sigla' => 'required|max_length[50]',
            'descricao' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'sigla' => $this->request->getPost('sigla'),
                    'descricao' => $this->request->getPost('descricao')
                ];

                $this->planoModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Plano cadastrado com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar plano: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $plano = $this->planoModel->find($id);

        if ($plano) {
            $response['success'] = true;
            $response['data'] = $plano;
        } else {
            $response['message'] = 'Plano não encontrado';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required',
            'nome' => 'required|min_length[3]|max_length[255]',
            'sigla' => 'required|max_length[50]',
            'descricao' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id' => $this->request->getPost('id'),
                    'nome' => $this->request->getPost('nome'),
                    'sigla' => $this->request->getPost('sigla'),
                    'descricao' => $this->request->getPost('descricao')
                ];

                $this->planoModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Plano atualizado com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao atualizar plano: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        if ($this->planoModel->delete($id)) {
            $response['success'] = true;
            $response['message'] = 'Plano excluído com sucesso!';
        } else {
            $response['message'] = 'Erro ao excluir plano';
        }

        return $this->response->setJSON($response);
    }

    public function filtrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $filtros = [
            'nome' => $this->request->getPost('nome'),
            'sigla' => $this->request->getPost('sigla')
        ];

        if (empty(array_filter($filtros))) {
            $planos = $this->planoModel->findAll();
            return $this->response->setJSON(['success' => true, 'data' => $planos]);
        }

        $builder = $this->planoModel->builder();

        if (!empty($filtros['nome'])) {
            $builder->like('nome', $filtros['nome']);
        }

        if (!empty($filtros['sigla'])) {
            $builder->like('sigla', $filtros['sigla']);
        }

        $planos = $builder->get()->getResultArray();
        return $this->response->setJSON(['success' => true, 'data' => $planos]);
    }
}
