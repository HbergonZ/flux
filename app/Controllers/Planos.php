<?php

namespace App\Controllers;

use App\Models\PlanosModel;
use App\Models\SolicitacoesModel;

class Planos extends BaseController
{
    protected $planoModel;
    protected $solicitacoesModel;

    public function __construct()
    {
        $this->planoModel = new PlanosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
    }

    public function index(): string
    {
        $planos = $this->planoModel->findAll();
        $data['planos'] = $planos;


        $this->content_data['content'] = view('sys/planos', $data);
        return view('layout', $this->content_data);
    }
    public function cadastrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]',
            'sigla' => 'required|max_length[50]',
            'descricao' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'sigla' => $this->request->getPost('sigla'),
                    'descricao' => $this->request->getPost('descricao')
                ];

                $this->planoModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Plano cadastrado com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar plano: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        // Verificar permissões
        if (!auth()->user()->inGroup('admin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você não tem permissão para esta ação'
            ]);
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $plano = $this->planoModel->find($id);

        if ($plano) {
            $response['success'] = true;
            $response['data'] = $plano;
        } else {
            $response['message'] = 'Plano não encontrado';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        // Verificar permissões
        if (!auth()->user()->inGroup('admin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você não tem permissão para esta ação'
            ]);
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required',
            'nome' => 'required|min_length[3]|max_length[255]',
            'sigla' => 'required|max_length[50]',
            'descricao' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id' => $this->request->getPost('id'),
                    'nome' => $this->request->getPost('nome'),
                    'sigla' => $this->request->getPost('sigla'),
                    'descricao' => $this->request->getPost('descricao')
                ];

                $this->planoModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Plano atualizado com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro ao atualizar plano: ' . $e->getMessage());
                $response['message'] = 'Erro ao atualizar plano: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }


    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        // Verificar permissões
        if (!auth()->user()->inGroup('admin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você não tem permissão para esta ação'
            ]);
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        try {
            // Verificar se existem projetos associados a este plano
            $projetosModel = new \App\Models\ProjetosModel();
            $projetosAssociados = $projetosModel->where('id_plano', $id)->countAllResults();

            if ($projetosAssociados > 0) {
                $response['message'] = 'Não é possível excluir este plano pois existem projetos associados a ele.';
                return $this->response->setJSON($response);
            }

            if ($this->planoModel->delete($id)) {
                $response['success'] = true;
                $response['message'] = 'Plano excluído com sucesso!';
            } else {
                $response['message'] = 'Erro ao excluir plano';
            }
        } catch (\Exception $e) {
            log_message('error', 'Erro ao excluir plano: ' . $e->getMessage());
            $response['message'] = 'Erro ao excluir plano: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function filtrar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $filtros = [
            'nome' => $this->request->getPost('nome'),
            'sigla' => $this->request->getPost('sigla')
        ];

        if (empty(array_filter($filtros))) {
            $planos = $this->planoModel->findAll();
            return $this->response->setJSON(['success' => true, 'data' => $planos]);
        }

        $builder = $this->planoModel->builder();

        if (!empty($filtros['nome'])) {
            $builder->like('nome', $filtros['nome']);
        }

        if (!empty($filtros['sigla'])) {
            $builder->like('sigla', $filtros['sigla']);
        }

        $planos = $builder->get()->getResultArray();
        return $this->response->setJSON(['success' => true, 'data' => $planos]);
    }
    public function dadosPlano($id = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $plano = $this->planoModel->find($id);

        if ($plano) {
            $response['success'] = true;
            $response['data'] = $plano;
        } else {
            $response['message'] = 'Plano não encontrado';
        }

        return $this->response->setJSON($response);
    }

    public function solicitarEdicao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'id_plano' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $planoAtual = $this->planoModel->find($postData['id_plano']);
                if (!$planoAtual) {
                    $response['message'] = 'Plano não encontrado';
                    return $this->response->setJSON($response);
                }

                // Verificar alterações
                $alteracoes = [];
                $camposEditaveis = ['nome', 'sigla', 'descricao'];

                foreach ($camposEditaveis as $campo) {
                    if (isset($postData[$campo]) && $postData[$campo] != $planoAtual[$campo]) {
                        $alteracoes[$campo] = [
                            'de' => $planoAtual[$campo],
                            'para' => $postData[$campo]
                        ];
                    }
                }

                if (empty($alteracoes)) {
                    $response['message'] = 'Nenhuma alteração detectada';
                    return $this->response->setJSON($response);
                }

                // Preparar dados para a solicitação
                $data = [
                    'nivel' => 'plano',
                    'id_plano' => $postData['id_plano'],
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($planoAtual, JSON_UNESCAPED_UNICODE),
                    'dados_alterados' => json_encode($alteracoes, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'solicitante' => auth()->user()->username,
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de edição enviada com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarEdicao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function solicitarExclusao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'id_plano' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $plano = $this->planoModel->find($postData['id_plano']);
                if (!$plano) {
                    $response['message'] = 'Plano não encontrado';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'nivel' => 'plano',
                    'id_plano' => $postData['id_plano'],
                    'tipo' => 'Exclusão',
                    'dados_atuais' => json_encode($plano, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'solicitante' => auth()->user()->username,
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de exclusão enviada com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarExclusao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function solicitarInclusao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]',
            'sigla' => 'required|max_length[50]',
            'descricao' => 'permit_empty',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $dadosAlterados = [
                    'nome' => $postData['nome'],
                    'sigla' => $postData['sigla'],
                    'descricao' => $postData['descricao'] ?? null
                ];

                $data = [
                    'nivel' => 'plano',
                    'tipo' => 'Inclusão',
                    'dados_alterados' => json_encode($dadosAlterados, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'solicitante' => auth()->user()->username,
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de inclusão enviada com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarInclusao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }
}
