<?php

namespace App\Controllers;

use App\Models\ProjetoModel;
use CodeIgniter\API\ResponseTrait;

class MeusProjetos extends BaseController
{
    use ResponseTrait;

    protected $projetoModel;

    public function __construct()
    {
        $this->projetoModel = new ProjetoModel();
    }

    public function index(): string
    {
        // Busca todos os projetos (futuramente filtrar por usuário logado)
        // $projetos = $this->projetoModel->where('id_usuario', user_id())->findAll();
        $projetos = $this->projetoModel->findAll();

        // Formata os dados para exibição
        foreach ($projetos as &$projeto) {
            $projeto['data_formatada'] = !empty($projeto['data_publicacao'])
                ? date('d/m/Y', strtotime($projeto['data_publicacao']))
                : '';

            // Garante que todos os campos estejam definidos
            $projeto['perspectiva_estrategica'] = $projeto['perspectiva_estrategica'] ?? '';
            $projeto['interessados'] = $projeto['interessados'] ?? '';
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

        // Seleciona explicitamente os campos necessários
        $builder->select('id, nome, objetivo, perspectiva_estrategica, interessados, status, data_publicacao');

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

        // Formata os dados para resposta
        foreach ($projetos as &$projeto) {
            $projeto['data_formatada'] = !empty($projeto['data_publicacao'])
                ? date('d/m/Y', strtotime($projeto['data_publicacao']))
                : '';

            // Garante valores padrão para campos opcionais
            $projeto['perspectiva_estrategica'] = $projeto['perspectiva_estrategica'] ?? '';
            $projeto['interessados'] = $projeto['interessados'] ?? '';
        }

        return $this->respond([
            'success' => true,
            'data' => $projetos,
            'csrfName' => csrf_token(),
            'csrfHash' => csrf_hash()
        ]);
    }
}
