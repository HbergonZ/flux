<?php

namespace App\Controllers;

use App\Models\PlanosModel;
use App\Models\ProjetosModel;
use App\Models\EtapasModel;
use App\Models\AcoesModel;
use App\Models\SolicitacoesModel;
use App\Controllers\LogController;

class Planos extends BaseController
{
    protected $planoModel;
    protected $solicitacoesModel;
    protected $logController;

    public function __construct()
    {
        $this->planoModel = new PlanosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->logController = new LogController();
    }

    public function index(): string
    {
        $planos = $this->planoModel->findAll();
        $data['planos'] = $planos;

        $this->content_data['content'] = view('sys/planos', $data);
        return view('layout', $this->content_data);
    }

    public function verificarRelacionamentos($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $response = ['success' => false, 'message' => '', 'contagem' => [
            'projetos' => 0,
            'etapas' => 0,
            'acoes' => 0
        ]];

        try {
            $projetosModel = new ProjetosModel();
            $etapasModel = new EtapasModel();
            $acoesModel = new AcoesModel();

            $projetos = $projetosModel->where('id_plano', $id)->findAll();
            $response['contagem']['projetos'] = count($projetos);

            foreach ($projetos as $projeto) {
                $etapas = $etapasModel->where('id_projeto', $projeto['id'])->findAll();
                $response['contagem']['etapas'] += count($etapas);

                foreach ($etapas as $etapa) {
                    $acoes = $acoesModel->where('id_etapa', $etapa['id'])->findAll();
                    $response['contagem']['acoes'] += count($acoes);
                }

                $acoesDiretas = $acoesModel->where('id_projeto', $projeto['id'])
                    ->where('id_etapa IS NULL')
                    ->findAll();
                $response['contagem']['acoes'] += count($acoesDiretas);
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            log_message('error', 'Erro ao verificar relacionamentos: ' . $e->getMessage());
            $response['message'] = 'Erro ao verificar relacionamentos: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
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

                $this->planoModel->transStart();
                $insertId = $this->planoModel->insert($data);

                if (!$insertId) {
                    throw new \Exception('Falha ao inserir plano no banco de dados');
                }

                $planoCompleto = array_merge(['id' => $insertId], $data);

                if (!$this->logController->registrarCriacao('plano', $planoCompleto, 'Cadastro inicial do plano')) {
                    throw new \Exception('Falha ao registrar log de criação');
                }

                $this->planoModel->transComplete();

                $response['success'] = true;
                $response['message'] = 'Plano cadastrado com sucesso!';
                $response['id'] = $insertId;
            } catch (\Exception $e) {
                $this->planoModel->transRollback();
                $response['message'] = 'Erro ao cadastrar plano: ' . $e->getMessage();
                log_message('error', 'Erro no cadastro de plano: ' . $e->getMessage());
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
                $id = $this->request->getPost('id');
                $planoAntigo = $this->planoModel->find($id);
                if (!$planoAntigo) {
                    $response['message'] = 'Plano não encontrado';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'id' => $id,
                    'nome' => $this->request->getPost('nome'),
                    'sigla' => $this->request->getPost('sigla'),
                    'descricao' => $this->request->getPost('descricao')
                ];

                $this->planoModel->transStart();
                $updated = $this->planoModel->save($data);

                if (!$updated) {
                    throw new \Exception('Falha ao atualizar plano no banco de dados');
                }

                $planoAtualizado = $this->planoModel->find($id);

                if (!$this->logController->registrarEdicao('plano', $planoAntigo, $planoAtualizado, 'Edição realizada via interface')) {
                    throw new \Exception('Falha ao registrar log de edição');
                }

                $this->planoModel->transComplete();

                $response['success'] = true;
                $response['message'] = 'Plano atualizado com sucesso!';
            } catch (\Exception $e) {
                $this->planoModel->transRollback();
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

        if (!auth()->user()->inGroup('admin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você não tem permissão para esta ação'
            ]);
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        $db = \Config\Database::connect();
        $projetosModel = new ProjetosModel($db);
        $etapasModel = new EtapasModel($db);
        $acoesModel = new AcoesModel($db);

        try {
            $db->transStart();

            $plano = $this->planoModel->find($id);
            if (!$plano) {
                throw new \Exception('Plano não encontrado');
            }

            $projetos = $projetosModel->where('id_plano', $id)->findAll();
            $contagem = [
                'projetos' => count($projetos),
                'etapas' => 0,
                'acoes' => 0
            ];

            foreach ($projetos as $projeto) {
                $etapas = $etapasModel->where('id_projeto', $projeto['id'])->findAll();
                $contagem['etapas'] += count($etapas);

                foreach ($etapas as $etapa) {
                    $acoes = $acoesModel->where('id_etapa', $etapa['id'])->findAll();
                    $contagem['acoes'] += count($acoes);

                    foreach ($acoes as $acao) {
                        if (!$this->logController->registrarExclusao('acao', $acao, 'Exclusão em cascata do plano')) {
                            throw new \Exception('Falha ao registrar log de exclusão da ação');
                        }

                        if (!$acoesModel->where('id', $acao['id'])->delete()) {
                            throw new \Exception("Falha ao excluir ação ID: {$acao['id']}");
                        }
                    }

                    if (!$this->logController->registrarExclusao('etapa', $etapa, 'Exclusão em cascata do plano')) {
                        throw new \Exception('Falha ao registrar log de exclusão da etapa');
                    }

                    if (!$etapasModel->where('id', $etapa['id'])->delete()) {
                        throw new \Exception("Falha ao excluir etapa ID: {$etapa['id']}");
                    }
                }

                $acoesDiretas = $acoesModel->where('id_projeto', $projeto['id'])
                    ->where('id_etapa IS NULL')
                    ->findAll();
                $contagem['acoes'] += count($acoesDiretas);

                foreach ($acoesDiretas as $acao) {
                    if (!$this->logController->registrarExclusao('acao', $acao, 'Exclusão em cascata do plano')) {
                        throw new \Exception('Falha ao registrar log de exclusão da ação direta');
                    }

                    if (!$acoesModel->where('id', $acao['id'])->delete()) {
                        throw new \Exception("Falha ao excluir ação direta ID: {$acao['id']}");
                    }
                }

                if (!$this->logController->registrarExclusao('projeto', $projeto, 'Exclusão em cascata do plano')) {
                    throw new \Exception('Falha ao registrar log de exclusão do projeto');
                }

                if (!$projetosModel->where('id', $projeto['id'])->delete()) {
                    throw new \Exception("Falha ao excluir projeto ID: {$projeto['id']}");
                }
            }

            if (!$this->logController->registrarExclusao('plano', $plano, 'Exclusão realizada via interface')) {
                throw new \Exception('Falha ao registrar log de exclusão do plano');
            }

            if (!$this->planoModel->where('id', $id)->delete()) {
                throw new \Exception('Falha ao excluir plano');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Falha na transação de exclusão em cascata');
            }

            $response['success'] = true;
            $response['message'] = 'Plano e todos os seus relacionamentos foram excluídos com sucesso!';
            $response['contagem'] = $contagem;
        } catch (\Exception $e) {
            $db->transRollback();
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
