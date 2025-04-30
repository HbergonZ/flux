<?php

namespace App\Controllers;

use App\Models\EtapasModel;
use App\Models\AcoesModel;
use App\Models\MetasModel;

class Etapas extends BaseController
{
    protected $etapasModel;
    protected $acoesModel;
    protected $metasModel;

    public function __construct()
    {
        $this->etapasModel = new EtapasModel();
        $this->acoesModel = new AcoesModel();
        $this->metasModel = new MetasModel();
    }

    // Para acessar via ações (quando não tem meta)
    public function index($idAcao = null)
    {
        if (empty($idAcao)) {
            return redirect()->to('/acoes');
        }

        $acao = $this->acoesModel->find($idAcao);
        if (!$acao) {
            return redirect()->to('/acoes');
        }

        $etapas = $this->etapasModel->getEtapasByAcao($idAcao);

        $data = [
            'tipo' => 'acao',
            'idVinculo' => $idAcao,
            'nomeVinculo' => $acao['acao'],
            'etapas' => $etapas
        ];

        $this->content_data['content'] = view('sys/etapas', $data);
        return view('layout', $this->content_data);
    }

    // Para acessar via metas
    public function meta($idMeta = null)
    {
        if (empty($idMeta)) {
            return redirect()->to('/metas');
        }

        $meta = $this->metasModel->find($idMeta);
        if (!$meta) {
            return redirect()->to('/metas');
        }

        $etapas = $this->etapasModel->getEtapasByMeta($idMeta);

        $data = [
            'tipo' => 'meta',
            'idVinculo' => $idMeta,
            'nomeVinculo' => $meta['nome'],
            'etapas' => $etapas
        ];

        $this->content_data['content'] = view('sys/etapas', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/etapas/$tipo/$idVinculo");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'etapa' => 'required|max_length[255]',
            'acao' => 'required|max_length[255]',
            'coordenacao' => 'required|max_length[255]',
            'responsavel' => 'required|max_length[255]',
            'tempo_estimado_dias' => 'required|integer',
            'data_inicio' => 'required|valid_date',
            'data_fim' => 'required|valid_date',
            'status' => 'required|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'etapa' => $this->request->getPost('etapa'),
                    'acao' => $this->request->getPost('acao'),
                    'coordenacao' => $this->request->getPost('coordenacao'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'data_inicio' => $this->request->getPost('data_inicio'),
                    'data_fim' => $this->request->getPost('data_fim'),
                    'status' => $this->request->getPost('status'),
                    'id_acao' => $tipo === 'acao' ? $idVinculo : null,
                    'id_meta' => $tipo === 'meta' ? $idVinculo : null
                ];

                $this->etapasModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Etapa cadastrada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar etapa: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($idEtapa = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/acoes');
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $etapa = $this->etapasModel->find($idEtapa);

        if ($etapa) {
            $response['success'] = true;
            $response['data'] = $etapa;
        } else {
            $response['message'] = 'Etapa não encontrada';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/etapas/$tipo/$idVinculo");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id_etapa' => 'required',
            'etapa' => 'required|max_length[255]',
            'acao' => 'required|max_length[255]',
            'coordenacao' => 'required|max_length[255]',
            'responsavel' => 'required|max_length[255]',
            'tempo_estimado_dias' => 'required|integer',
            'data_inicio' => 'required|valid_date',
            'data_fim' => 'required|valid_date',
            'status' => 'required|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id_etapa' => $this->request->getPost('id_etapa'),
                    'etapa' => $this->request->getPost('etapa'),
                    'acao' => $this->request->getPost('acao'),
                    'coordenacao' => $this->request->getPost('coordenacao'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'data_inicio' => $this->request->getPost('data_inicio'),
                    'data_fim' => $this->request->getPost('data_fim'),
                    'status' => $this->request->getPost('status')
                ];

                $this->etapasModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Etapa atualizada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao atualizar etapa: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function excluir($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/etapas/$tipo/$idVinculo");
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        if ($this->etapasModel->delete($id)) {
            $response['success'] = true;
            $response['message'] = 'Etapa excluída com sucesso!';
        } else {
            $response['message'] = 'Erro ao excluir etapa';
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/etapas/$tipo/$idVinculo");
        }

        $filtros = [
            'etapa' => $this->request->getPost('etapa'),
            'acao' => $this->request->getPost('acao'),
            'coordenacao' => $this->request->getPost('coordenacao'),
            'responsavel' => $this->request->getPost('responsavel'),
            'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
            'data_inicio' => $this->request->getPost('data_inicio'),
            'data_fim' => $this->request->getPost('data_fim'),
            'status' => $this->request->getPost('status')
        ];

        $builder = $tipo === 'acao'
            ? $this->etapasModel->where('id_acao', $idVinculo)
            : $this->etapasModel->where('id_meta', $idVinculo);

        if (!empty($filtros['etapa'])) {
            $builder->like('etapa', $filtros['etapa']);
        }

        if (!empty($filtros['acao'])) {
            $builder->like('acao', $filtros['acao']);
        }

        if (!empty($filtros['coordenacao'])) {
            $builder->like('coordenacao', $filtros['coordenacao']);
        }

        if (!empty($filtros['responsavel'])) {
            $builder->like('responsavel', $filtros['responsavel']);
        }

        if (!empty($filtros['tempo_estimado_dias'])) {
            $builder->where('tempo_estimado_dias', $filtros['tempo_estimado_dias']);
        }

        if (!empty($filtros['data_inicio'])) {
            $builder->where('data_inicio >=', $filtros['data_inicio']);
        }

        if (!empty($filtros['data_fim'])) {
            $builder->where('data_fim <=', $filtros['data_fim']);
        }

        if (!empty($filtros['status'])) {
            $builder->where('status', $filtros['status']);
        }

        $etapas = $builder->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $etapas]);
    }
}
