<?php

namespace App\Controllers;

use App\Models\VisaoGeralModel;

class VisaoGeral extends BaseController
{
    protected $visaoGeralModel;

    public function __construct()
    {
        $this->visaoGeralModel = new VisaoGeralModel();
    }

    public function index(): string
    {
        // Busca todos os registros
        $dados = $this->visaoGeralModel->getVisaoGeral();

        // Busca os valores distintos para os filtros
        $filtros = $this->visaoGeralModel->getFiltrosDistinct();

        $data = [
            'dados' => $dados,
            'filtros' => $filtros
        ];

        $this->content_data['content'] = view('sys/visao-geral', $data);
        return view('layout', $this->content_data);
    }

    public function filtrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/visao-geral');
        }

        // Recebe os parâmetros de filtro
        $filtros = [
            'priorizacao_gab' => $this->request->getPost('priorizacao_gab'),
            'plano' => $this->request->getPost('plano'),
            'projeto' => $this->request->getPost('projeto'),
            'acao' => $this->request->getPost('acao'),
            'etapa' => $this->request->getPost('etapa'),
            'responsaveis' => $this->request->getPost('responsaveis'),
            'status' => $this->request->getPost('status'),
            'data_inicio' => $this->request->getPost('data_inicio'),
            'data_fim' => $this->request->getPost('data_fim')
        ];

        // Aplica os filtros
        $dados = $this->visaoGeralModel->getVisaoGeral($filtros);

        return $this->response->setJSON([
            'success' => true,
            'data' => $dados,
            'totalRegistros' => count($dados)
        ]);
    }
}
