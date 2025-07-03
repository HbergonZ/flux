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

        // Formata os dados para incluir todas as informações necessárias
        $dadosFormatados = array_map(function ($item) {
            return [
                'priorizacao_gab' => $item['priorizacao_gab'],
                'plano' => $item['plano'],
                'nome_projeto' => $item['nome_projeto'],
                'etapa' => $item['etapa'],
                'acao' => $item['acao'],
                'responsaveis' => $item['responsaveis'],
                'entrega_estimada_formatada' => !empty($item['entrega_estimada']) ? date('d/m/Y', strtotime($item['entrega_estimada'])) : '',
                'data_inicio_formatada' => !empty($item['data_inicio']) ? date('d/m/Y', strtotime($item['data_inicio'])) : '',
                'data_fim_formatada' => !empty($item['data_fim']) ? date('d/m/Y', strtotime($item['data_fim'])) : '',
                'status' => $item['status']
            ];
        }, $dados);

        return $this->response->setJSON([
            'success' => true,
            'data' => $dadosFormatados,
            'totalRegistros' => count($dados)
        ]);
    }

    public function getProjetosPorPlano()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/visao-geral');
        }

        $plano = $this->request->getPost('plano');

        $db = $this->visaoGeralModel->db;

        $builder = $db->table('projetos')
            ->select('projetos.nome as nome_projeto')
            ->join('planos', 'planos.id = projetos.id_plano', 'left');

        if (!empty($plano)) {
            $builder->where('planos.nome', $plano);
        }

        $projetos = $builder->groupBy('projetos.nome')
            ->orderBy('projetos.nome')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $projetos
        ]);
    }
}
