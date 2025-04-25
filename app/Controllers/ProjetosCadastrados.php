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
}
