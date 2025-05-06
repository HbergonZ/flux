<?php

namespace App\Controllers;

use App\Models\AcoesModel;
use App\Models\PlanosModel;
use App\Models\EixosModel;
use App\Models\SolicitacoesModel;

class Acoes extends BaseController
{
    protected $acoesModel;
    protected $planosModel;
    protected $eixosModel;
    protected $solicitacoesModel;

    public function __construct()
    {
        $this->acoesModel = new AcoesModel();
        $this->planosModel = new PlanosModel();
        $this->eixosModel = new EixosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
    }

    public function index($idPlano = null)
    {
        if (empty($idPlano)) {
            return redirect()->to('/planos');
        }

        // Busca o plano para exibir o nome
        $plano = $this->planosModel->find($idPlano);
        if (!$plano) {
            return redirect()->to('/planos');
        }

        // Busca as ações vinculadas ao plano
        $acoes = $this->acoesModel->getAcoesByPlano($idPlano);

        // Busca os eixos para os selects
        $eixos = $this->eixosModel->findAll();

        $data = [
            'plano' => $plano,
            'acoes' => $acoes,
            'eixos' => $eixos,
            'idPlano' => $idPlano
        ];

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/acoes/$idPlano");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'acao' => 'required|max_length[255]',
            'descricao' => 'permit_empty',
            'projeto_vinculado' => 'permit_empty|max_length[255]',
            'id_eixo' => 'permit_empty|integer',
            'responsaveis' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'acao' => $this->request->getPost('acao'),
                    'descricao' => $this->request->getPost('descricao'),
                    'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
                    'id_eixo' => $this->request->getPost('id_eixo'),
                    'id_plano' => $idPlano,
                    'responsaveis' => $this->request->getPost('responsaveis')
                ];

                $this->acoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Ação cadastrada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar ação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($idAcao = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $acao = $this->acoesModel->find($idAcao);

        if ($acao) {
            $response['success'] = true;
            $response['data'] = $acao;
        } else {
            $response['message'] = 'Ação não encontrada';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/acoes/$idPlano");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required',
            'acao' => 'required|max_length[255]',
            'descricao' => 'permit_empty',
            'projeto_vinculado' => 'permit_empty|max_length[255]',
            'id_eixo' => 'permit_empty|integer',
            'responsaveis' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id' => $this->request->getPost('id'),
                    'acao' => $this->request->getPost('acao'),
                    'descricao' => $this->request->getPost('descricao'),
                    'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
                    'id_eixo' => $this->request->getPost('id_eixo'),
                    'responsaveis' => $this->request->getPost('responsaveis')
                ];

                $this->acoesModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Ação atualizada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao atualizar ação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function excluir($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/acoes/$idPlano");
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        if (empty($id)) {
            $response['message'] = 'ID da ação não fornecido';
            return $this->response->setJSON($response);
        }

        try {
            if ($this->acoesModel->delete($id)) {
                $response['success'] = true;
                $response['message'] = 'Ação excluída com sucesso!';
            } else {
                $response['message'] = 'Erro ao excluir ação: registro não encontrado';
            }
        } catch (\Exception $e) {
            $response['message'] = 'Erro ao excluir ação: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/acoes/$idPlano");
        }

        $filtros = [
            'acao' => $this->request->getPost('acao'),
            'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
            'id_eixo' => $this->request->getPost('id_eixo')
        ];

        $builder = $this->acoesModel->where('id_plano', $idPlano);

        if (!empty($filtros['acao'])) {
            $builder->like('acao', $filtros['acao']);
        }

        if (!empty($filtros['projeto_vinculado'])) {
            $builder->like('projeto_vinculado', $filtros['projeto_vinculado']);
        }

        if (!empty($filtros['id_eixo'])) {
            $builder->where('id_eixo', $filtros['id_eixo']);
        }

        $acoes = $builder->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $acoes]);
    }
    public function dadosAcao($idAcao = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $acao = $this->acoesModel->find($idAcao);

        if ($acao) {
            $response['success'] = true;
            $response['data'] = $acao;
        } else {
            $response['message'] = 'Ação não encontrada';
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
            'id_acao' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $acaoAtual = $this->acoesModel->find($postData['id_acao']);
                if (!$acaoAtual) {
                    $response['message'] = 'Ação não encontrada';
                    return $this->response->setJSON($response);
                }

                // Verificar alterações
                $alteracoes = [];
                $camposEditaveis = ['acao', 'descricao', 'projeto_vinculado', 'id_eixo', 'responsaveis'];

                foreach ($camposEditaveis as $campo) {
                    if (isset($postData[$campo]) && $postData[$campo] != $acaoAtual[$campo]) {
                        $alteracoes[$campo] = [
                            'de' => $acaoAtual[$campo],
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
                    'nivel' => 'acao',
                    'id_acao' => $postData['id_acao'],
                    'id_plano' => $postData['id_plano'],
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($acaoAtual),
                    'dados_alterados' => json_encode($alteracoes),
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
            'id_acao' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $acao = $this->acoesModel->find($postData['id_acao']);
                if (!$acao) {
                    $response['message'] = 'Ação não encontrada';
                    return $this->response->setJSON($response);
                }

                $dadosAtuais = [
                    'acao' => $acao['acao'],
                    'descricao' => $acao['descricao'],
                    'projeto_vinculado' => $acao['projeto_vinculado'],
                    'id_eixo' => $acao['id_eixo'],
                    'responsaveis' => $acao['responsaveis'],
                    'id_plano' => $acao['id_plano']
                ];

                $data = [
                    'nivel' => 'acao',
                    'id_acao' => $postData['id_acao'],
                    'id_plano' => $postData['id_plano'],
                    'tipo' => 'Exclusão',
                    'dados_atuais' => json_encode($dadosAtuais),
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
            'acao' => 'required|max_length[255]',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $dadosAlterados = [
                    'acao' => $postData['acao'],
                    'descricao' => $postData['descricao'] ?? null,
                    'projeto_vinculado' => $postData['projeto_vinculado'] ?? null,
                    'id_eixo' => $postData['id_eixo'] ?? null,
                    'responsaveis' => $postData['responsaveis'] ?? null,
                    'id_plano' => $postData['id_plano']
                ];

                $data = [
                    'nivel' => 'acao',
                    'id_plano' => $postData['id_plano'],
                    'tipo' => 'Inclusão',
                    'dados_alterados' => json_encode($dadosAlterados),
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
