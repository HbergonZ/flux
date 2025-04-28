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
            return redirect()->to('/projetos-cadastrados');
        }

        $projeto = $this->projetoModel->find($id);

        if (!$projeto) {
            return redirect()->to('/projetos-cadastrados')->with('error', 'Projeto não encontrado');
        }

        // Busca as etapas relacionadas a este projeto (sem filtros inicialmente)
        $etapas = $this->etapaModel->where('id_projeto', $id)->findAll();

        $data['projeto'] = $projeto;
        $data['etapas'] = $etapas;

        $this->content_data['content'] = view('sys/visao-projeto', $data);
        return view('layout', $this->content_data);
    }

    public function filtrar($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/visao-projeto/{$idProjeto}");
        }

        // Recebe os parâmetros de filtro
        $filtros = [
            'etapa' => $this->request->getPost('etapa'),
            'acao' => $this->request->getPost('acao'),
            'status' => $this->request->getPost('status'),
            'responsavel' => $this->request->getPost('responsavel'),
            'coordenacao' => $this->request->getPost('coordenacao'),
            'data_inicio' => $this->request->getPost('data_inicio'),
            'data_fim' => $this->request->getPost('data_fim')
        ];

        // Aplica os filtros
        $builder = $this->etapaModel->builder();
        $builder->where('id_projeto', $idProjeto);

        if (!empty($filtros['etapa'])) {
            $builder->like('etapa', $filtros['etapa']);
        }

        if (!empty($filtros['acao'])) {
            $builder->like('acao', $filtros['acao']);
        }

        if (!empty($filtros['status'])) {
            $builder->where('status', $filtros['status']);
        }

        if (!empty($filtros['responsavel'])) {
            $builder->like('responsavel', $filtros['responsavel']);
        }

        if (!empty($filtros['coordenacao'])) {
            $builder->like('coordenacao', $filtros['coordenacao']);
        }

        if (!empty($filtros['data_inicio'])) {
            $builder->where('data_inicio >=', $filtros['data_inicio']);
        }

        if (!empty($filtros['data_fim'])) {
            $builder->where('data_fim <=', $filtros['data_fim']);
        }

        $etapas = $builder->get()->getResultArray();

        // Formata as datas antes de enviar para o cliente
        foreach ($etapas as &$etapa) {
            $etapa['data_inicio_formatada'] = !empty($etapa['data_inicio']) ? date('d/m/Y', strtotime($etapa['data_inicio'])) : '';
            $etapa['data_fim_formatada'] = !empty($etapa['data_fim']) ? date('d/m/Y', strtotime($etapa['data_fim'])) : '';
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $etapas
        ]);
    }
    public function dadosEtapa($idEtapa, $idAcao)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $etapa = $this->etapaModel
            ->where('id_etapa', $idEtapa)
            ->where('id_acao', $idAcao)
            ->first();

        if (!$etapa) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Etapa/Ação não encontrada'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $etapa
        ]);
    }

    public function solicitarEdicao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Validação dos dados
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_etapa' => 'required|numeric',
            'id_acao' => 'required|numeric',
            'id_projeto' => 'required|numeric',
            'dados_atuais' => 'required',
            'dados_alterados' => 'required',
            'justificativa' => 'required|min_length[10]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $validation->getErrors())
            ]);
        }

        // Insere a solicitação no banco de dados
        $db = \Config\Database::connect();
        $builder = $db->table('solicitacoes_edicao');

        try {
            $builder->insert([
                'id_etapa' => $this->request->getPost('id_etapa'),
                'id_acao' => $this->request->getPost('id_acao'),
                'id_projeto' => $this->request->getPost('id_projeto'),
                'dados_atuais' => $this->request->getPost('dados_atuais'),
                'dados_alterados' => $this->request->getPost('dados_alterados'),
                'justificativa' => $this->request->getPost('justificativa'),
                'status' => 'pendente'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Solicitação de edição registrada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao registrar solicitação: ' . $e->getMessage()
            ]);
        }
    }
}
