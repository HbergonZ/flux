<?php

namespace App\Controllers;

use App\Models\ProjetoModel;

class ProjetosCadastrados extends BaseController
{
    protected $projetoModel;

    public function __construct()
    {
        $this->projetoModel = new ProjetoModel();
    }

    public function index(): string
    {
        // Busca todos os projetos do banco
        $projetos = $this->projetoModel->findAll();

        // Passa os dados para a view
        $data['projetos'] = $projetos;

        // Conteúdo da página interna
        $this->content_data['content'] = view('sys/projetos-cadastrados', $data);

        // Conteúdo da estrutura externa
        return view('layout', $this->content_data);
    }

    public function cadastrar()
    {
        // Verifica se é uma requisição AJAX
        if (!$this->request->isAJAX()) {
            return redirect()->to('/projetos-cadastrados');
        }

        $response = ['success' => false, 'message' => ''];

        // Valida os dados
        $rules = [
            'nome' => 'required|min_length[3]|max_length[500]',
            'descricao' => 'required',
            'status' => 'required|in_list[Em andamento,Não iniciado,Finalizado,Paralisado]',
            'data_publicacao' => 'required|valid_date'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'descricao' => $this->request->getPost('descricao'),
                    'status' => $this->request->getPost('status'),
                    'data_publicacao' => $this->request->getPost('data_publicacao')
                ];

                $this->projetoModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Projeto cadastrado com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar projeto: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/projetos-cadastrados');
        }

        $response = ['success' => false, 'message' => '', 'data' => null];

        // Busca o projeto
        $projeto = $this->projetoModel->find($id);

        if ($projeto) {
            $response['success'] = true;
            $response['data'] = $projeto;
        } else {
            $response['message'] = 'Projeto não encontrado';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/projetos-cadastrados');
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required|numeric',
            'nome' => 'required|min_length[3]|max_length[500]',
            'descricao' => 'required',
            'status' => 'required|in_list[Em andamento,Não iniciado,Finalizado,Paralisado]',
            'data_publicacao' => 'required|valid_date'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id' => $this->request->getPost('id'),
                    'nome' => $this->request->getPost('nome'),
                    'descricao' => $this->request->getPost('descricao'),
                    'status' => $this->request->getPost('status'),
                    'data_publicacao' => $this->request->getPost('data_publicacao')
                ];

                $this->projetoModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Projeto atualizado com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao atualizar projeto: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/projetos-cadastrados');
        }

        $response = ['success' => false, 'message' => ''];

        $id = $this->request->getPost('id');

        if ($this->projetoModel->delete($id)) {
            $response['success'] = true;
            $response['message'] = 'Projeto excluído com sucesso!';
        } else {
            $response['message'] = 'Erro ao excluir projeto';
        }

        return $this->response->setJSON($response);
    }
    public function filtrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/projetos-cadastrados');
        }

        // Recebe os parâmetros de filtro
        $filtros = [
            'nome' => $this->request->getPost('nome'),
            'status' => $this->request->getPost('status'),
            'data_inicio' => $this->request->getPost('data_inicio'),
            'data_fim' => $this->request->getPost('data_fim')
        ];

        // Se todos os filtros estiverem vazios, retorna todos os projetos
        if (empty(array_filter($filtros))) {
            $projetos = $this->projetoModel->findAll();
            return $this->response->setJSON([
                'success' => true,
                'data' => $projetos
            ]);
        }

        // Aplica os filtros
        $builder = $this->projetoModel->builder();

        if (!empty($filtros['nome'])) {
            $builder->like('nome', $filtros['nome']);
        }

        if (!empty($filtros['status'])) {
            $builder->where('status', $filtros['status']);
        }

        if (!empty($filtros['data_inicio'])) {
            $builder->where('data_publicacao >=', $filtros['data_inicio']);
        }

        if (!empty($filtros['data_fim'])) {
            $builder->where('data_publicacao <=', $filtros['data_fim']);
        }

        $projetos = $builder->get()->getResultArray();

        // Formata as datas antes de enviar
        foreach ($projetos as &$projeto) {
            $projeto['data_formatada'] = date('d/m/Y', strtotime($projeto['data_publicacao']));
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $projetos
        ]);
    }
}
