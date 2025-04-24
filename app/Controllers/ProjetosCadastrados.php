<?php

namespace App\Controllers;

class ProjetosCadastrados extends BaseController
{
    public function index(): string
    {
        //Conteúdo da página interna
        $this->content_data['content'] = view('sys/projetos-cadastrados');

        //Conteúdo da estrutura externa
        return view('layout', $this->content_data);
    }
}
