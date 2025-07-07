<?php

namespace App\Controllers;

use App\Models\EtapasModel;
use App\Models\PlanosModel;
use App\Models\AcoesModel;
use App\Models\ProjetosModel;
use App\Models\SolicitacoesModel;
use App\Controllers\LogController;

class Etapas extends BaseController
{
    protected $etapasModel;
    protected $acoesModel;
    protected $planosModel;
    protected $projetosModel;
    protected $solicitacoesModel;
    protected $logController;

    public function __construct()
    {
        $this->etapasModel = new EtapasModel();
        $this->projetosModel = new ProjetosModel();
        $this->acoesModel = new AcoesModel();
        $this->planosModel = new PlanosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->logController = new LogController();
    }

    public function index($idPlano = null, $idProjeto = null)
    {
        if (empty($idProjeto)) {
            return redirect()->back();
        }

        // Carrega o projeto usando o ID da rota
        $projeto = $this->projetosModel->find($idProjeto);
        if (!$projeto) {
            return redirect()->back();
        }

        // Carrega o plano usando o ID da rota, não o ID do projeto
        $plano = $this->planosModel->find($idPlano);
        if (!$plano) {
            return redirect()->back();
        }

        // Verifica se o projeto pertence ao plano
        if ($projeto['id_plano'] != $plano['id']) {
            return redirect()->back()->with('error', 'Projeto não pertence ao plano especificado');
        }

        $etapas = $this->etapasModel->getEtapasByProjeto($idProjeto);

        $data = [
            'projeto' => $projeto,
            'plano' => $plano,
            'etapas' => $etapas,
            'idProjeto' => $idProjeto,
            'idPlano' => $idPlano // Adicione isso para usar na view
        ];

        $this->content_data['content'] = view('sys/etapas', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/projetos/$idProjeto/etapas");
        }

        $response = ['success' => false, 'message' => ''];

        try {
            if (!auth()->user()->inGroup('admin')) {
                throw new \Exception('Você não tem permissão para esta ação');
            }

            $rules = [
                'nome' => 'required|min_length[3]|max_length[255]',
                'ordem' => 'required|integer'
            ];

            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                throw new \Exception(implode("\n", $errors));
            }

            $data = [
                'nome' => $this->request->getPost('nome'),
                'ordem' => $this->request->getPost('ordem'),
                'id_projeto' => $idProjeto
            ];

            $this->etapasModel->transStart();
            $insertId = $this->etapasModel->insert($data);

            if (!$insertId) {
                throw new \Exception('Falha ao inserir etapa no banco de dados');
            }

            $etapaInserida = array_merge(['id' => $insertId], $data);

            // Registrar log de criação
            if (!$this->logController->registrarCriacao('etapa', $etapaInserida, 'Cadastro inicial da etapa')) {
                throw new \Exception('Falha ao registrar log de criação');
            }

            $this->etapasModel->transComplete();

            $response['success'] = true;
            $response['message'] = 'Etapa cadastrada com sucesso!';
            $response['data'] = $etapaInserida;
        } catch (\Exception $e) {
            $this->etapasModel->transRollback();
            $response['message'] = $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function editar($idEtapa = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
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

    public function atualizar($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/projetos/$idProjeto/etapas");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required',
            'nome' => 'required|min_length[3]|max_length[255]',
            'ordem' => 'permit_empty|integer'
        ];

        if ($this->validate($rules)) {
            try {
                $id = $this->request->getPost('id');
                $etapaAntiga = $this->etapasModel->find($id);

                if (!$etapaAntiga || $etapaAntiga['id_projeto'] != $idProjeto) {
                    $response['message'] = 'Etapa não encontrada ou não pertence a este projeto';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'id' => $id,
                    'nome' => $this->request->getPost('nome'),
                    'ordem' => $this->request->getPost('ordem')
                ];

                $this->etapasModel->transStart();
                $this->etapasModel->save($data);

                $etapaAtualizada = $this->etapasModel->find($id);

                // Registrar log de edição
                if (!$this->logController->registrarEdicao('etapa', $etapaAntiga, $etapaAtualizada, 'Edição realizada via interface')) {
                    throw new \Exception('Falha ao registrar log de edição');
                }

                $this->etapasModel->transComplete();

                $response['success'] = true;
                $response['message'] = 'Etapa atualizada com sucesso!';
            } catch (\Exception $e) {
                $this->etapasModel->transRollback();
                $response['message'] = 'Erro ao atualizar etapa: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function excluir($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/projetos/$idProjeto/etapas");
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        try {
            $this->etapasModel->transStart();

            $etapa = $this->etapasModel->find($id);
            if (!$etapa || $etapa['id_projeto'] != $idProjeto) {
                throw new \Exception('Etapa não encontrada ou não pertence a este projeto');
            }

            // Contar e excluir ações vinculadas
            $acoes = $this->acoesModel->where('id_etapa', $id)->findAll();
            $contagemAcoes = count($acoes);

            foreach ($acoes as $acao) {
                // Registrar log de exclusão da ação
                if (!$this->logController->registrarExclusao('acao', $acao, 'Exclusão em cascata da etapa')) {
                    throw new \Exception('Falha ao registrar log de exclusão da ação');
                }

                if (!$this->acoesModel->delete($acao['id'])) {
                    throw new \Exception("Falha ao excluir ação ID: {$acao['id']}");
                }
            }

            // Registrar log de exclusão da etapa
            if (!$this->logController->registrarExclusao('etapa', $etapa, 'Exclusão realizada via interface')) {
                throw new \Exception('Falha ao registrar log de exclusão da etapa');
            }

            // Excluir a etapa
            if (!$this->etapasModel->delete($id)) {
                throw new \Exception('Falha ao excluir etapa');
            }

            $this->etapasModel->transComplete();

            $response['success'] = true;
            $response['message'] = 'Etapa e suas ações foram excluídas com sucesso!';
            $response['contagem'] = [
                'acoes' => $contagemAcoes
            ];
        } catch (\Exception $e) {
            $this->etapasModel->transRollback();
            $response['message'] = 'Erro ao excluir etapa: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/projetos/$idProjeto/etapas");
        }

        $filtro = $this->request->getPost('nome');

        $builder = $this->etapasModel->where('id_projeto', $idProjeto);

        if (!empty($filtro)) {
            $builder->like('nome', $filtro);
        }

        $etapas = $builder->orderBy('data_criacao', 'ASC')->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $etapas]);
    }

    public function dadosEtapa($idEtapa = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
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

    public function solicitarEdicao()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'id_etapa' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $etapaAtual = $this->etapasModel->find($postData['id_etapa']);
                if (!$etapaAtual) {
                    $response['message'] = 'Etapa não encontrada';
                    return $this->response->setJSON($response);
                }

                $alteracoes = [];
                $camposEditaveis = ['nome', 'ordem'];

                foreach ($camposEditaveis as $campo) {
                    if (isset($postData[$campo]) && $postData[$campo] != $etapaAtual[$campo]) {
                        $alteracoes[$campo] = [
                            'de' => $etapaAtual[$campo],
                            'para' => $postData[$campo]
                        ];
                    }
                }

                if (empty($alteracoes)) {
                    $response['message'] = 'Nenhuma alteração detectada';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'nivel' => 'etapa',
                    'id_etapa' => $postData['id_etapa'],
                    'id_projeto' => $etapaAtual['id_projeto'],
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($etapaAtual, JSON_UNESCAPED_UNICODE),
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
            'id_etapa' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $etapa = $this->etapasModel->find($postData['id_etapa']);
                if (!$etapa) {
                    $response['message'] = 'Etapa não encontrada';
                    return $this->response->setJSON($response);
                }

                $dadosAtuais = [
                    'nome' => $etapa['nome'],
                    'ordem' => $etapa['ordem'],
                    'id_projeto' => $etapa['id_projeto']
                ];

                $data = [
                    'nivel' => 'etapa',
                    'id_etapa' => $postData['id_etapa'],
                    'id_projeto' => $etapa['id_projeto'],
                    'tipo' => 'Exclusão',
                    'dados_atuais' => json_encode($dadosAtuais, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'solicitante' => auth()->user()->username,
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de exclusão enviada com sucesso!';
            } catch (\Exception $e) {
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
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                // Obter o ID do usuário logado
                $userId = auth()->id();

                $dadosAlterados = [
                    'nome' => $postData['nome'],
                    'ordem' => $postData['ordem'] ?? null,
                    'id_projeto' => $postData['id_projeto'],
                    'id_plano' => $postData['id_plano'], // Adiciona o ID do plano
                    'id_solicitante' => $userId // Adiciona o ID do solicitante
                ];

                $data = [
                    'nivel' => 'etapa',
                    'id_projeto' => $postData['id_projeto'],
                    'id_plano' => $postData['id_plano'], // Adiciona o ID do plano
                    'id_solicitante' => $userId, // Adiciona o ID do solicitante
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
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function proximaOrdem($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $proximaOrdem = $this->etapasModel->getProximaOrdem($idProjeto);
            return $this->response->setJSON([
                'success' => true,
                'proximaOrdem' => $proximaOrdem
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao calcular próxima ordem'
            ]);
        }
    }

    public function salvarOrdem($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $ordens = $this->request->getPost('ordem');

        if (empty($ordens) || !is_array($ordens)) {
            $response['message'] = 'Nenhuma ordem foi enviada';
            return $this->response->setJSON($response);
        }

        // Verificar se há ordens duplicadas
        if (count($ordens) !== count(array_unique($ordens))) {
            $response['message'] = 'Existem ordens duplicadas';
            return $this->response->setJSON($response);
        }

        try {
            $this->etapasModel->transStart();

            foreach ($ordens as $id => $ordem) {
                $this->etapasModel->update($id, ['ordem' => (int)$ordem]);
            }

            $this->etapasModel->transComplete();

            if ($this->etapasModel->transStatus() === false) {
                throw new \Exception('Erro ao atualizar ordens no banco de dados');
            }

            $response['success'] = true;
            $response['message'] = 'Ordem das etapas atualizada com sucesso!';
        } catch (\Exception $e) {
            $response['message'] = 'Erro ao salvar ordem: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }
    public function verificarRelacionamentos($idEtapa)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'contagem' => [
            'acoes' => 0
        ]];

        try {
            $acoesModel = new AcoesModel();

            $etapa = $this->etapasModel->find($idEtapa);
            if (!$etapa) {
                $response['message'] = 'Etapa não encontrada';
                return $this->response->setJSON($response);
            }

            // Contar ações vinculadas
            $acoes = $acoesModel->where('id_etapa', $idEtapa)->findAll();
            $response['contagem']['acoes'] = count($acoes);

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['message'] = 'Erro ao verificar relacionamentos: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }
    public function progresso($idEtapa)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $acoesModel = new AcoesModel();

            // Obter todas as ações desta etapa
            $acoes = $acoesModel->where('id_etapa', $idEtapa)->findAll();
            $totalAcoes = count($acoes);

            // Contar ações finalizadas (incluindo finalizadas com atraso)
            $acoesFinalizadas = 0;
            foreach ($acoes as $acao) {
                if (in_array($acao['status'], ['Finalizado', 'Finalizado com atraso'])) {
                    $acoesFinalizadas++;
                }
            }

            // Calcular percentual
            $percentual = $totalAcoes > 0 ? round(($acoesFinalizadas / $totalAcoes) * 100) : 0;

            // Determinar a classe CSS baseada no percentual
            $progressClass = 'bg-secondary';
            if ($percentual >= 80) {
                $progressClass = 'bg-success';
            } elseif ($percentual >= 50) {
                $progressClass = 'bg-warning';
            } elseif ($percentual > 0) {
                $progressClass = 'bg-danger';
            }

            return $this->response->setJSON([
                'success' => true,
                'percentual' => $percentual,
                'class' => $progressClass,
                'texto' => "$acoesFinalizadas de $totalAcoes ações finalizadas",
                'total_acoes' => $totalAcoes,
                'acoes_finalizadas' => $acoesFinalizadas
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao calcular progresso: ' . $e->getMessage()
            ]);
        }
    }
    public function carregarEtapas($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $etapas = $this->etapasModel->getEtapasByProjeto($idProjeto);

            // Formatar os dados para a tabela
            $data = [];
            foreach ($etapas as $etapa) {
                $data[] = [
                    'ordem' => $etapa['ordem'] ?? '-',
                    'nome' => $etapa['nome'],
                    'data_criacao' => $etapa['data_criacao'],
                    'data_atualizacao' => $etapa['data_atualizacao'],
                    'progresso' => '', // Será preenchido pelo JavaScript
                    'opcoes' => '', // Será preenchido pelo JavaScript
                    'id' => $etapa['id'],
                    'id_slug' => $etapa['id'] . '-' . str_replace(' ', '-', strtolower($etapa['nome']))
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao carregar etapas: ' . $e->getMessage()
            ]);
        }
    }
}
