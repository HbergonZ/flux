<?php

namespace App\Controllers;

use App\Models\ProjetoModel;
use App\Models\EtapaModel;

class VisaoProjeto extends BaseController
{
    protected $projetoModel;
    protected $etapaModel;

    public function __construct()
    {
        $this->projetoModel = new ProjetoModel();
        $this->etapaModel = new EtapaModel();
    }

    public function index($id = null)
    {
        if (empty($id)) {
            // Se nenhum ID foi passado, redirecione para a lista de projetos
            return redirect()->to('/projetos-cadastrados');
        }

        // Busca o projeto específico
        $projeto = $this->projetoModel->find($id);

        if (!$projeto) {
            // Se o projeto não existe, redirecione com mensagem de erro
            return redirect()->to('/projetos-cadastrados')->with('error', 'Projeto não encontrado');
        }

        // Busca as etapas relacionadas a este projeto
        $etapas = $this->etapaModel->where('id_projeto', $id)->findAll();

        // Prepara os dados para a view
        $data['projeto'] = $projeto;
        $data['etapas'] = $etapas;

        // Conteúdo da página interna
        $this->content_data['content'] = view('sys/visao-projeto', $data);

        // Conteúdo da estrutura externa
        return view('layout', $this->content_data);
    }
}
