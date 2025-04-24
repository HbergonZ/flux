<?php

namespace App\Controllers;

class VisaoProjeto extends BaseController
{
    public function index(): string
    {
        //Conteúdo da página interna
        $this->content_data['content'] = view('sys/visao-projeto');

        //Conteúdo da estrutura externa
        return view('layout', $this->content_data);
    }
}
