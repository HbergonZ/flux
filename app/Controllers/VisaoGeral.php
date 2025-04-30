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
        // Busca todos os registros da view
        $dados = $this->visaoGeralModel->findAll();

        // Busca os valores distintos para os filtros
        $filtros = $this->visaoGeralModel->getFiltrosDistinct();

        // Passa os dados para a view
        $data = [
            'dados' => $dados,
            'filtros' => $filtros
        ];

        // Conteúdo da página interna
        $this->content_data['content'] = view('sys/visao-geral', $data);

        // Conteúdo da estrutura externa
        return view('layout', $this->content_data);
    }

    public function filtrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/visao-geral');
        }

        // Recebe os parâmetros de filtro
        $filtros = [
            'plano' => $this->request->getPost('plano'),
            'acao' => $this->request->getPost('acao'),
            'meta' => $this->request->getPost('meta'),
            'etapa' => $this->request->getPost('etapa'),
            'responsavel' => $this->request->getPost('responsavel'),
            'equipe' => $this->request->getPost('equipe'),
            'status' => $this->request->getPost('status'),
            'data_inicio' => $this->request->getPost('data_inicio'),
            'data_fim' => $this->request->getPost('data_fim')
        ];

        // Aplica os filtros
        $dados = $this->visaoGeralModel->filtrar($filtros);

        // Conta o total de registros filtrados
        $totalRegistros = count($dados);

        // Formata as datas antes de enviar
        foreach ($dados as &$registro) {
            if (!empty($registro['data_inicio'])) {
                $registro['data_inicio_formatada'] = date('d/m/Y', strtotime($registro['data_inicio']));
            } else {
                $registro['data_inicio_formatada'] = '';
            }

            if (!empty($registro['data_fim'])) {
                $registro['data_fim_formatada'] = date('d/m/Y', strtotime($registro['data_fim']));
            } else {
                $registro['data_fim_formatada'] = '';
            }
        }
        unset($registro);

        return $this->response->setJSON([
            'success' => true,
            'data' => $dados,
            'totalRegistros' => $totalRegistros
        ]);
    }
}
