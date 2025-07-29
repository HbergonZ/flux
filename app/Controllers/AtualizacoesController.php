<?php

namespace App\Controllers;

class AtualizacoesController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Atualizações do Sistema'
        ];

        return view('layout', ['content' => view('sys/atualizacoes', $data)]);
    }
}
