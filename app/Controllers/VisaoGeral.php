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

        // Formata os dados para a view
        $dadosFormatados = array_map(function ($registro) {
            return [
                'priorizacao_gab' => (int)$registro['priorizacao_gab'],
                'plano' => $registro['plano'] ?? '',
                'acao' => $registro['acao'] ?? '',
                'meta' => $registro['meta'] ?? '',
                'ordem' => $registro['ordem'] ?? '',
                'etapa' => $registro['etapa'] ?? '',
                'responsavel' => $registro['responsavel'] ?? '',
                'equipe' => $registro['equipe'] ?? '',
                'status' => $registro['status'] ?? '',
                'data_inicio_formatada' => !empty($registro['data_inicio']) ? date('d/m/Y', strtotime($registro['data_inicio'])) : '',
                'data_fim_formatada' => !empty($registro['data_fim']) ? date('d/m/Y', strtotime($registro['data_fim'])) : ''
            ];
        }, $dados);

        $data = [
            'dados' => $dadosFormatados,
            'filtros' => $filtros,
            'colunas_padrao' => json_encode([
                'priorizacao' => true,
                'plano' => true,
                'acao' => true,
                'meta' => true,
                'ordem' => true,
                'etapa' => true,
                'responsavel' => true,
                'equipe' => true,
                'status' => true,
                'data_inicio' => true,
                'data_fim' => true
            ])
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
        $dados = $this->visaoGeralModel->getVisaoGeral($filtros);

        // Formata os dados para resposta
        $dadosFormatados = array_map(function ($registro) {
            return [
                'priorizacao_gab' => (int)$registro['priorizacao_gab'],
                'plano' => $registro['plano'] ?? '',
                'acao' => $registro['acao'] ?? '',
                'meta' => $registro['meta'] ?? '',
                'ordem' => $registro['ordem'] ?? '',
                'etapa' => $registro['etapa'] ?? '',
                'responsavel' => $registro['responsavel'] ?? '',
                'equipe' => $registro['equipe'] ?? '',
                'status' => $registro['status'] ?? '',
                'data_inicio_formatada' => !empty($registro['data_inicio']) ? date('d/m/Y', strtotime($registro['data_inicio'])) : '',
                'data_fim_formatada' => !empty($registro['data_fim']) ? date('d/m/Y', strtotime($registro['data_fim'])) : ''
            ];
        }, $dados);

        return $this->response->setJSON([
            'success' => true,
            'data' => $dadosFormatados,
            'totalRegistros' => count($dados)
        ]);
    }
}
