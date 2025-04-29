<?php

namespace App\Controllers;

use App\Models\ProjetoModel;

class MeusProjetos extends BaseController
{
    protected $projetoModel;

    public function __construct()
    {
        $this->projetoModel = new ProjetoModel();
    }

    public function index(): string
    {
        // Busca todos os projetos (futuramente filtrar por usuário)
        $projetos = $this->projetoModel->findAll();

        // Formata os dados para exibição
        foreach ($projetos as &$projeto) {
            $projeto['data_formatada'] = !empty($projeto['data_publicacao'])
                ? date('d/m/Y', strtotime($projeto['data_publicacao']))
                : '';
        }

        $data = [
            'projetos' => $projetos,
            'tituloPagina' => 'Meus Projetos'
        ];

        $this->content_data['content'] = view('sys/meus-projetos', $data);
        return view('layout', $this->content_data);
    }

    public function filtrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/meus-projetos');
        }

        $filtros = $this->request->getPost();
        $builder = $this->projetoModel->builder();

        // Aplica filtros
        if (!empty($filtros['nome'])) {
            $builder->like('nome', $filtros['nome']);
        }

        if (!empty($filtros['status'])) {
            $builder->where('status', $filtros['status']);
        }

        // Futuramente adicionar filtro por usuário:
        // $builder->where('id_usuario', user_id());

        $projetos = $builder->get()->getResultArray();

        // Formata os dados
        foreach ($projetos as &$projeto) {
            $projeto['data_formatada'] = !empty($projeto['data_publicacao'])
                ? date('d/m/Y', strtotime($projeto['data_publicacao']))
                : '';
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $projetos
        ]);
    }
}
