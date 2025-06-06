<?php

namespace App\Controllers;

use App\Models\ProjetosModel;
use App\Models\PlanosModel;
use App\Models\EixosModel;
use App\Models\SolicitacoesModel;
use App\Models\EtapasModel;
use App\Models\AcoesModel;
use App\Controllers\LogController;

class Projetos extends BaseController
{
    protected $projetosModel;
    protected $planosModel;
    protected $eixosModel;
    protected $solicitacoesModel;
    protected $etapasModel;
    protected $acoesModel;
    protected $logController;

    public function __construct()
    {
        $this->projetosModel = new ProjetosModel();
        $this->planosModel = new PlanosModel();
        $this->eixosModel = new EixosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->etapasModel = new EtapasModel();
        $this->acoesModel = new AcoesModel();
        $this->logController = new LogController();
    }

    public function index($idPlano = null)
    {
        log_message('debug', 'Acessando método index do controller Projetos com idPlano: ' . $idPlano);

        // Redirecionamento para URL canônica se acessado pela rota antiga
        if (strpos(current_url(), 'projetos/') !== false && strpos(current_url(), '/etapas') === false) {
            log_message('debug', 'Redirecionando para URL canônica');
            return redirect()->to(site_url("planos/$idPlano/projetos"));
        }

        if (empty($idPlano)) {
            log_message('debug', 'Redirecionando para /planos - idPlano vazio');
            return redirect()->to('/planos');
        }

        $plano = $this->planosModel->find($idPlano);
        if (!$plano) {
            log_message('debug', 'Plano não encontrado, redirecionando para /planos');
            return redirect()->to('/planos');
        }

        $projetos = $this->projetosModel->getProjetosByPlano($idPlano);
        $eixos = $this->eixosModel->findAll();

        log_message('debug', 'Número de projetos encontrados: ' . count($projetos));
        log_message('debug', 'Número de eixos encontrados: ' . count($eixos));

        $data = [
            'plano' => $plano,
            'projetos' => $projetos,
            'eixos' => $eixos,
            'idPlano' => $idPlano
        ];

        $this->content_data['content'] = view('sys/projetos', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($idPlano)
    {
        log_message('debug', 'Iniciando cadastro de projeto para o plano: ' . $idPlano);

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->to("/planos/$idPlano/projetos");
        }

        $response = ['success' => false, 'message' => ''];

        try {
            log_message('debug', 'Verificando permissões do usuário');
            if (!auth()->user()->inGroup('admin')) {
                throw new \Exception('Você não tem permissão para esta ação');
            }

            log_message('debug', 'Validando dados do formulário');

            $rules = [
                'identificador' => 'required|max_length[10]|alpha_numeric',
                'nome' => 'required|max_length[255]',
                'descricao' => 'permit_empty',
                'projeto_vinculado' => 'permit_empty|max_length[255]',
                'priorizacao_gab' => 'permit_empty|in_list[0,1]',
                'id_eixo' => 'permit_empty|integer',
                'responsaveis' => 'permit_empty'
            ];

            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                log_message('debug', 'Erros de validação: ' . print_r($errors, true));
                throw new \Exception(implode("\n", $errors));
            }

            log_message('debug', 'Verificando identificador único');
            $identificadorExistente = $this->projetosModel
                ->where('identificador', $this->request->getPost('identificador'))
                ->where('id_plano', $idPlano)
                ->first();

            if ($identificadorExistente) {
                throw new \Exception('Já existe um projeto com este identificador no plano atual');
            }

            $data = [
                'identificador' => $this->request->getPost('identificador'),
                'nome' => $this->request->getPost('nome'),
                'descricao' => $this->request->getPost('descricao'),
                'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
                'priorizacao_gab' => $this->request->getPost('priorizacao_gab') ?? 0,
                'id_plano' => $idPlano,
                'responsaveis' => $this->request->getPost('responsaveis'),
                'id_eixo' => $this->request->getPost('id_eixo') ?: null
            ];

            log_message('debug', 'Dados preparados para inserção: ' . print_r($data, true));

            $this->projetosModel->transStart();
            $insertId = $this->projetosModel->insert($data);

            if ($this->projetosModel->errors()) {
                $errors = $this->projetosModel->errors();
                log_message('error', 'Erros ao inserir projeto: ' . print_r($errors, true));
                throw new \Exception(implode("\n", $errors));
            }

            $projetoInserido = array_merge(['id' => $insertId], $data);

            // Registrar log de criação
            if (!$this->logController->registrarCriacao('projeto', $projetoInserido, 'Cadastro inicial do projeto')) {
                throw new \Exception('Falha ao registrar log de criação');
            }

            $this->projetosModel->transComplete();

            log_message('debug', 'Projeto inserido com ID: ' . $insertId);

            $response['success'] = true;
            $response['message'] = 'Projeto cadastrado com sucesso!';
            $response['data'] = $projetoInserido;

            log_message('debug', 'Resposta JSON preparada: ' . print_r($response, true));
        } catch (\Exception $e) {
            $this->projetosModel->transRollback();
            log_message('error', 'Erro ao cadastrar projeto: ' . $e->getMessage());
            $response['message'] = $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function editar($idProjeto = null)
    {
        log_message('debug', 'Acessando método editar para o projeto ID: ' . $idProjeto);

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->to('/planos');
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $projeto = $this->projetosModel->find($idProjeto);

        if ($projeto) {
            log_message('debug', 'Projeto encontrado: ' . print_r($projeto, true));
            $response['success'] = true;
            $response['data'] = $projeto;
        } else {
            log_message('debug', 'Projeto não encontrado');
            $response['message'] = 'Projeto não encontrado';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar($idPlano)
    {
        log_message('debug', 'Iniciando atualização de projeto no plano: ' . $idPlano);

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->to("/planos/$idPlano/projetos");
        }

        $response = ['success' => false, 'message' => ''];

        if (!auth()->user()->inGroup('admin')) {
            log_message('debug', 'Usuário não tem permissão para esta ação');
            $response['message'] = 'Você não tem permissão para esta ação';
            return $this->response->setJSON($response);
        }

        $rules = [
            'id' => 'required',
            'identificador' => 'required|max_length[10]|alpha_numeric',
            'nome' => 'required|max_length[255]',
            'descricao' => 'permit_empty',
            'projeto_vinculado' => 'permit_empty|max_length[255]',
            'priorizacao_gab' => 'permit_empty|in_list[0,1]',
            'id_eixo' => 'permit_empty|integer',
            'responsaveis' => 'permit_empty',
            'status' => 'required|in_list[Ativo,Paralisado,Concluído]'
        ];

        if ($this->validate($rules)) {
            $db = \Config\Database::connect();
            $db->transStart(); // Inicia transação manualmente

            try {
                $id = $this->request->getPost('id');
                $projetoAntigo = $this->projetosModel->find($id);

                if (!$projetoAntigo || $projetoAntigo['id_plano'] != $idPlano) {
                    log_message('debug', 'Projeto não encontrado ou não pertence ao plano');
                    $response['message'] = 'Projeto não encontrado ou não pertence a este plano';
                    return $this->response->setJSON($response);
                }

                $novoStatus = $this->request->getPost('status');
                $statusAlterado = ($projetoAntigo['status'] !== $novoStatus);

                $data = [
                    'id' => $id,
                    'identificador' => $this->request->getPost('identificador'),
                    'nome' => $this->request->getPost('nome'),
                    'descricao' => $this->request->getPost('descricao'),
                    'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
                    'priorizacao_gab' => $this->request->getPost('priorizacao_gab') ?? 0,
                    'id_eixo' => $this->request->getPost('id_eixo') ?: null,
                    'responsaveis' => $this->request->getPost('responsaveis'),
                    'status' => $novoStatus
                ];

                log_message('debug', 'Dados preparados para atualização: ' . print_r($data, true));

                // 1. Atualiza o projeto
                $this->projetosModel->save($data);

                // 2. Se o status foi alterado, atualiza as ações
                if ($statusAlterado) {
                    log_message('debug', 'Status alterado de ' . $projetoAntigo['status'] . ' para ' . $novoStatus . '. Atualizando ações...');

                    $acoesModel = new \App\Models\AcoesModel();
                    $result = $acoesModel->atualizarStatusAcoesProjeto($id, $novoStatus, $db); // Passa a conexão

                    log_message('debug', 'Resultado da atualização de ações: ' . print_r($result, true));
                }

                $projetoAtualizado = $this->projetosModel->find($id);

                // Registrar log de edição
                if (!$this->logController->registrarEdicao('projeto', $projetoAntigo, $projetoAtualizado, 'Edição realizada via interface')) {
                    throw new \Exception('Falha ao registrar log de edição');
                }

                $db->transComplete(); // Confirma a transação

                if ($db->transStatus() === false) {
                    throw new \Exception('Falha na transação do banco de dados');
                }

                $response['success'] = true;
                $response['message'] = 'Projeto atualizado com sucesso!';

                log_message('debug', 'Projeto atualizado com sucesso');
            } catch (\Exception $e) {
                $db->transRollback();
                log_message('error', 'Erro ao atualizar projeto: ' . $e->getMessage());
                $response['message'] = 'Erro ao atualizar projeto: ' . $e->getMessage();
            }
        } else {
            $errors = $this->validator->getErrors();
            log_message('debug', 'Erros de validação: ' . print_r($errors, true));
            $response['message'] = implode('<br>', $errors);
        }

        return $this->response->setJSON($response);
    }

    public function excluir($idPlano)
    {
        log_message('debug', 'Iniciando exclusão de projeto no plano: ' . $idPlano);

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->to("/planos/$idPlano/projetos");
        }

        $response = ['success' => false, 'message' => ''];

        if (!auth()->user()->inGroup('admin')) {
            log_message('debug', 'Usuário não tem permissão para esta ação');
            $response['message'] = 'Você não tem permissão para esta ação';
            return $this->response->setJSON($response);
        }

        $idProjeto = $this->request->getPost('id');

        if (empty($idProjeto)) {
            log_message('debug', 'ID do projeto não fornecido');
            $response['message'] = 'ID do projeto não fornecido';
            return $this->response->setJSON($response);
        }

        try {
            $projeto = $this->projetosModel->find($idProjeto);
            if (!$projeto || $projeto['id_plano'] != $idPlano) {
                log_message('debug', 'Projeto não encontrado ou não pertence ao plano');
                $response['message'] = 'Projeto não encontrado ou não pertence a este plano';
                return $this->response->setJSON($response);
            }

            log_message('debug', 'Verificando dependências do projeto');
            $contagem = [
                'etapas' => 0,
                'acoes' => 0
            ];

            $this->projetosModel->transStart();

            // Verificar e excluir etapas e ações vinculadas
            $etapas = $this->etapasModel->where('id_projeto', $idProjeto)->findAll();
            $contagem['etapas'] = count($etapas);

            foreach ($etapas as $etapa) {
                $acoes = $this->acoesModel->where('id_etapa', $etapa['id'])->findAll();
                $contagem['acoes'] += count($acoes);

                foreach ($acoes as $acao) {
                    // Registrar log de exclusão da ação
                    if (!$this->logController->registrarExclusao('acao', $acao, 'Exclusão em cascata do projeto')) {
                        throw new \Exception('Falha ao registrar log de exclusão da ação');
                    }

                    if (!$this->acoesModel->delete($acao['id'])) {
                        throw new \Exception("Falha ao excluir ação ID: {$acao['id']}");
                    }
                }

                // Registrar log de exclusão da etapa
                if (!$this->logController->registrarExclusao('etapa', $etapa, 'Exclusão em cascata do projeto')) {
                    throw new \Exception('Falha ao registrar log de exclusão da etapa');
                }

                if (!$this->etapasModel->delete($etapa['id'])) {
                    throw new \Exception("Falha ao excluir etapa ID: {$etapa['id']}");
                }
            }

            // Verificar e excluir ações diretas vinculadas ao projeto
            $acoesDiretas = $this->acoesModel->where('id_projeto', $idProjeto)
                ->where('id_etapa', null)
                ->findAll();
            $contagem['acoes'] += count($acoesDiretas);

            foreach ($acoesDiretas as $acao) {
                // Registrar log de exclusão da ação direta
                if (!$this->logController->registrarExclusao('acao', $acao, 'Exclusão em cascata do projeto')) {
                    throw new \Exception('Falha ao registrar log de exclusão da ação direta');
                }

                if (!$this->acoesModel->delete($acao['id'])) {
                    throw new \Exception("Falha ao excluir ação direta ID: {$acao['id']}");
                }
            }

            // Registrar log de exclusão do projeto
            if (!$this->logController->registrarExclusao('projeto', $projeto, 'Exclusão realizada via interface')) {
                throw new \Exception('Falha ao registrar log de exclusão do projeto');
            }

            // Excluir o projeto
            if (!$this->projetosModel->delete($idProjeto)) {
                throw new \Exception('Falha ao excluir projeto');
            }

            $this->projetosModel->transComplete();

            $response['success'] = true;
            $response['message'] = 'Projeto excluído com sucesso!';
            $response['contagem'] = $contagem;

            log_message('debug', 'Projeto excluído com sucesso');
        } catch (\Exception $e) {
            $this->projetosModel->transRollback();
            log_message('error', 'Erro ao excluir projeto: ' . $e->getMessage());
            $response['message'] = 'Erro ao excluir projeto: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function verificarRelacionamentos($idProjeto)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'contagem' => [
            'etapas' => 0,
            'acoes' => 0
        ]];

        try {
            $projeto = $this->projetosModel->find($idProjeto);
            if (!$projeto) {
                $response['message'] = 'Projeto não encontrado';
                return $this->response->setJSON($response);
            }

            // Contar etapas vinculadas
            $etapas = $this->etapasModel->where('id_projeto', $idProjeto)->findAll();
            $response['contagem']['etapas'] = count($etapas);

            // Contar ações vinculadas (diretas e através de etapas)
            $acoesDiretas = $this->acoesModel->where('id_projeto', $idProjeto)
                ->where('id_etapa IS NULL')
                ->findAll();
            $response['contagem']['acoes'] = count($acoesDiretas);

            foreach ($etapas as $etapa) {
                $acoes = $this->acoesModel->where('id_etapa', $etapa['id'])->findAll();
                $response['contagem']['acoes'] += count($acoes);
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            log_message('error', 'Erro ao verificar relacionamentos: ' . $e->getMessage());
            $response['message'] = 'Erro ao verificar relacionamentos: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($idPlano)
    {
        log_message('debug', 'Aplicando filtros para projetos no plano: ' . $idPlano);

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->to("/planos/$idPlano/projetos");
        }

        $filtros = [
            'nome' => $this->request->getPost('nome'),
            'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
            'id_eixo' => $this->request->getPost('id_eixo')
        ];

        log_message('debug', 'Filtros aplicados: ' . print_r($filtros, true));

        $projetos = $this->projetosModel->getProjetosFiltrados($idPlano, $filtros);
        return $this->response->setJSON(['success' => true, 'data' => $projetos]);
    }

    public function dadosProjeto($idProjeto = null)
    {
        log_message('debug', 'Obtendo dados do projeto ID: ' . $idProjeto);

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $projeto = $this->projetosModel->find($idProjeto);

        if ($projeto) {
            log_message('debug', 'Projeto encontrado');
            $response['success'] = true;
            $response['data'] = $projeto;
        } else {
            log_message('debug', 'Projeto não encontrado');
            $response['message'] = 'Projeto não encontrado';
        }

        return $this->response->setJSON($response);
    }

    public function solicitarEdicao()
    {
        log_message('debug', 'Processando solicitação de edição de projeto');

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'id_projeto' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $projetoAtual = $this->projetosModel->find($postData['id_projeto']);
                if (!$projetoAtual) {
                    log_message('debug', 'Projeto não encontrado para solicitação de edição');
                    $response['message'] = 'Projeto não encontrado';
                    return $this->response->setJSON($response);
                }

                $alteracoes = [];
                $camposEditaveis = ['identificador', 'nome', 'descricao', 'projeto_vinculado', 'priorizacao_gab', 'id_eixo', 'responsaveis'];

                foreach ($camposEditaveis as $campo) {
                    if (isset($postData[$campo]) && $postData[$campo] != $projetoAtual[$campo]) {
                        $alteracoes[$campo] = [
                            'de' => $projetoAtual[$campo],
                            'para' => $postData[$campo]
                        ];
                    }
                }

                if (empty($alteracoes)) {
                    log_message('debug', 'Nenhuma alteração detectada na solicitação de edição');
                    $response['message'] = 'Nenhuma alteração detectada';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'nivel' => 'projeto',
                    'id_solicitante' => auth()->id(),
                    'id_projeto' => $postData['id_projeto'],
                    'id_plano' => $projetoAtual['id_plano'],
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($projetoAtual, JSON_UNESCAPED_UNICODE),
                    'dados_alterados' => json_encode($alteracoes, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                log_message('debug', 'Dados da solicitação de edição: ' . print_r($data, true));

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de edição enviada com sucesso!';

                log_message('debug', 'Solicitação de edição registrada com sucesso');
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarEdicao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $errors = $this->validator->getErrors();
            log_message('debug', 'Erros de validação na solicitação de edição: ' . print_r($errors, true));
            $response['message'] = implode('<br>', $errors);
        }

        return $this->response->setJSON($response);
    }

    public function solicitarExclusao()
    {
        log_message('debug', 'Processando solicitação de exclusão de projeto');

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'id_projeto' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $projeto = $this->projetosModel->find($postData['id_projeto']);
                if (!$projeto) {
                    log_message('debug', 'Projeto não encontrado para solicitação de exclusão');
                    $response['message'] = 'Projeto não encontrado';
                    return $this->response->setJSON($response);
                }

                $dadosAtuais = [
                    'identificador' => $projeto['identificador'],
                    'nome' => $projeto['nome'],
                    'descricao' => $projeto['descricao'],
                    'projeto_vinculado' => $projeto['projeto_vinculado'],
                    'priorizacao_gab' => $projeto['priorizacao_gab'],
                    'id_eixo' => $projeto['id_eixo'],
                    'responsaveis' => $projeto['responsaveis'],
                    'id_plano' => $projeto['id_plano']
                ];

                $data = [
                    'nivel' => 'projeto',
                    'id_solicitante' => auth()->id(),
                    'id_projeto' => $postData['id_projeto'],
                    'id_plano' => $postData['id_plano'],
                    'tipo' => 'Exclusão',
                    'dados_atuais' => json_encode($dadosAtuais, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                log_message('debug', 'Dados da solicitação de exclusão: ' . print_r($data, true));

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de exclusão enviada com sucesso!';

                log_message('debug', 'Solicitação de exclusão registrada com sucesso');
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarExclusao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $errors = $this->validator->getErrors();
            log_message('debug', 'Erros de validação na solicitação de exclusão: ' . print_r($errors, true));
            $response['message'] = implode('<br>', $errors);
        }

        return $this->response->setJSON($response);
    }

    public function solicitarInclusao()
    {
        log_message('debug', 'Processando solicitação de inclusão de projeto');

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $postData = $this->request->getPost();

        $rules = [
            'identificador' => 'required|max_length[10]|alpha_numeric',
            'nome' => 'required|max_length[255]',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $dadosAlterados = [
                    'identificador' => $postData['identificador'],
                    'nome' => $postData['nome'],
                    'descricao' => $postData['descricao'] ?? null,
                    'projeto_vinculado' => $postData['projeto_vinculado'] ?? null,
                    'priorizacao_gab' => $postData['priorizacao_gab'] ?? 0,
                    'id_eixo' => $postData['id_eixo'] ?? null,
                    'responsaveis' => $postData['responsaveis'] ?? null,
                    'id_plano' => $postData['id_plano']
                ];

                $data = [
                    'nivel' => 'projeto',
                    'id_solicitante' => auth()->id(),
                    'id_plano' => $postData['id_plano'],
                    'tipo' => 'Inclusão',
                    'dados_alterados' => json_encode($dadosAlterados, JSON_UNESCAPED_UNICODE),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                log_message('debug', 'Dados da solicitação de inclusão: ' . print_r($data, true));

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de inclusão enviada com sucesso!';

                log_message('debug', 'Solicitação de inclusão registrada com sucesso');
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarInclusao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();
            }
        } else {
            $errors = $this->validator->getErrors();
            log_message('debug', 'Erros de validação na solicitação de inclusão: ' . print_r($errors, true));
            $response['message'] = implode('<br>', $errors);
        }

        return $this->response->setJSON($response);
    }

    public function etapas($idProjeto)
    {
        log_message('debug', 'Acessando etapas do projeto ID: ' . $idProjeto);

        $projeto = $this->projetosModel->find($idProjeto);
        if (!$projeto) {
            log_message('debug', 'Projeto não encontrado, redirecionando');
            return redirect()->back()->with('error', 'Projeto não encontrado');
        }

        $etapas = $this->etapasModel->where('id_projeto', $idProjeto)->findAll();

        log_message('debug', 'Número de etapas encontradas: ' . count($etapas));

        $data = [
            'projeto' => $projeto,
            'etapas' => $etapas,
            'idProjeto' => $idProjeto,
            'plano' => $this->planosModel->find($projeto['id_plano'])
        ];

        $this->content_data['content'] = view('sys/etapas', $data);
        return view('layout', $this->content_data);
    }

    public function acoes($idProjeto)
    {
        log_message('debug', 'Acessando ações diretas do projeto ID: ' . $idProjeto);

        $projeto = $this->projetosModel->find($idProjeto);
        if (!$projeto) {
            log_message('debug', 'Projeto não encontrado, redirecionando');
            return redirect()->back()->with('error', 'Projeto não encontrado');
        }

        $acoes = $this->acoesModel->where('id_projeto', $idProjeto)
            ->where('id_etapa', null)
            ->orderBy('ordem', 'ASC')
            ->findAll();

        log_message('debug', 'Número de ações diretas encontradas: ' . count($acoes));

        $data = [
            'projeto' => $projeto,
            'plano' => $this->planosModel->find($projeto['id_plano']),
            'acoes' => $acoes,
            'idProjeto' => $idProjeto,
            'acessoDireto' => true
        ];

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrarAcaoDireta($idProjeto)
    {
        log_message('debug', 'Cadastrando ação direta para o projeto ID: ' . $idProjeto);

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->to("/projetos/$idProjeto/acoes");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]',
            'responsavel' => 'permit_empty|max_length[255]',
            'equipe' => 'permit_empty|max_length[255]',
            'tempo_estimado_dias' => 'permit_empty|integer',
            'inicio_estimado' => 'permit_empty|valid_date',
            'fim_estimado' => 'permit_empty|valid_date',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
            'status' => 'permit_empty|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]',
            'ordem' => 'permit_empty|integer'
        ];

        if ($this->validate($rules)) {
            try {
                $projeto = $this->projetosModel->find($idProjeto);
                if (!$projeto) {
                    log_message('debug', 'Projeto não encontrado para cadastrar ação');
                    $response['message'] = 'Projeto não encontrado';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'inicio_estimado' => $this->request->getPost('inicio_estimado'),
                    'fim_estimado' => $this->request->getPost('fim_estimado'),
                    'data_inicio' => $this->request->getPost('data_inicio'),
                    'data_fim' => $this->request->getPost('data_fim'),
                    'status' => $this->request->getPost('status'),
                    'ordem' => $this->request->getPost('ordem'),
                    'id_projeto' => $idProjeto,
                    'id_etapa' => null
                ];

                log_message('debug', 'Dados da ação direta: ' . print_r($data, true));

                $this->acoesModel->transStart();

                $insertId = $this->acoesModel->insert($data);

                if ($this->acoesModel->errors()) {
                    throw new \Exception(implode("\n", $this->acoesModel->errors()));
                }

                $acaoInserida = array_merge(['id' => $insertId], $data);

                // Registrar log de criação
                if (!$this->logController->registrarCriacao('acao', $acaoInserida, 'Cadastro de ação direta no projeto')) {
                    throw new \Exception('Falha ao registrar log de criação');
                }

                $this->acoesModel->transComplete();

                $response['success'] = true;
                $response['message'] = 'Ação cadastrada com sucesso!';
                $response['data'] = $acaoInserida;

                log_message('debug', 'Ação direta cadastrada com sucesso');
            } catch (\Exception $e) {
                $this->acoesModel->transRollback();
                log_message('error', 'Erro ao cadastrar ação direta: ' . $e->getMessage());
                $response['message'] = 'Erro ao cadastrar ação: ' . $e->getMessage();
            }
        } else {
            $errors = $this->validator->getErrors();
            log_message('debug', 'Erros de validação ao cadastrar ação: ' . print_r($errors, true));
            $response['message'] = implode('<br>', $errors);
        }

        return $this->response->setJSON($response);
    }

    public function editarAcaoDireta($idProjeto, $idAcao)
    {
        log_message('debug', "Editando ação direta ID: $idAcao do projeto ID: $idProjeto");

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->to("/projetos/$idProjeto/acoes");
        }

        $response = ['success' => false, 'message' => ''];

        try {
            $projeto = $this->projetosModel->find($idProjeto);
            if (!$projeto) {
                throw new \Exception('Projeto não encontrado');
            }

            $acao = $this->acoesModel->where('id', $idAcao)
                ->where('id_projeto', $idProjeto)
                ->where('id_etapa IS NULL')
                ->first();

            if (!$acao) {
                throw new \Exception('Ação não encontrada ou não pertence a este projeto');
            }

            $rules = [
                'nome' => 'required|min_length[3]|max_length[255]',
                'responsavel' => 'permit_empty|max_length[255]',
                'equipe' => 'permit_empty|max_length[255]',
                'tempo_estimado_dias' => 'permit_empty|integer',
                'inicio_estimado' => 'permit_empty|valid_date',
                'fim_estimado' => 'permit_empty|valid_date',
                'data_inicio' => 'permit_empty|valid_date',
                'data_fim' => 'permit_empty|valid_date',
                'status' => 'permit_empty|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]',
                'ordem' => 'permit_empty|integer'
            ];

            if (!$this->validate($rules)) {
                $errors = $this->validator->getErrors();
                throw new \Exception(implode("\n", $errors));
            }

            $dadosAntigos = $acao;
            $dadosAtualizados = [
                'nome' => $this->request->getPost('nome'),
                'responsavel' => $this->request->getPost('responsavel'),
                'equipe' => $this->request->getPost('equipe'),
                'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                'inicio_estimado' => $this->request->getPost('inicio_estimado'),
                'fim_estimado' => $this->request->getPost('fim_estimado'),
                'data_inicio' => $this->request->getPost('data_inicio'),
                'data_fim' => $this->request->getPost('data_fim'),
                'status' => $this->request->getPost('status'),
                'ordem' => $this->request->getPost('ordem')
            ];

            $this->acoesModel->transStart();

            if (!$this->acoesModel->update($idAcao, $dadosAtualizados)) {
                throw new \Exception('Falha ao atualizar ação');
            }

            $acaoAtualizada = $this->acoesModel->find($idAcao);

            // Registrar log de edição
            if (!$this->logController->registrarEdicao('acao', $dadosAntigos, $acaoAtualizada, 'Edição de ação direta')) {
                throw new \Exception('Falha ao registrar log de edição');
            }

            $this->acoesModel->transComplete();

            $response['success'] = true;
            $response['message'] = 'Ação atualizada com sucesso!';
            $response['data'] = $acaoAtualizada;
        } catch (\Exception $e) {
            $this->acoesModel->transRollback();
            log_message('error', 'Erro ao editar ação direta: ' . $e->getMessage());
            $response['message'] = $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function excluirAcaoDireta($idProjeto, $idAcao)
    {
        log_message('debug', "Excluindo ação direta ID: $idAcao do projeto ID: $idProjeto");

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->to("/projetos/$idProjeto/acoes");
        }

        $response = ['success' => false, 'message' => ''];

        try {
            $projeto = $this->projetosModel->find($idProjeto);
            if (!$projeto) {
                throw new \Exception('Projeto não encontrado');
            }

            $acao = $this->acoesModel->where('id', $idAcao)
                ->where('id_projeto', $idProjeto)
                ->where('id_etapa IS NULL')
                ->first();

            if (!$acao) {
                throw new \Exception('Ação não encontrada ou não pertence a este projeto');
            }

            $this->acoesModel->transStart();

            // Registrar log de exclusão
            if (!$this->logController->registrarExclusao('acao', $acao, 'Exclusão de ação direta')) {
                throw new \Exception('Falha ao registrar log de exclusão');
            }

            if (!$this->acoesModel->delete($idAcao)) {
                throw new \Exception('Falha ao excluir ação');
            }

            $this->acoesModel->transComplete();

            $response['success'] = true;
            $response['message'] = 'Ação excluída com sucesso!';
        } catch (\Exception $e) {
            $this->acoesModel->transRollback();
            log_message('error', 'Erro ao excluir ação direta: ' . $e->getMessage());
            $response['message'] = $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function reordenarAcoesDiretas($idProjeto)
    {
        log_message('debug', "Reordenando ações diretas do projeto ID: $idProjeto");

        if (!$this->request->isAJAX()) {
            log_message('debug', 'Requisição não é AJAX, redirecionando');
            return redirect()->to("/projetos/$idProjeto/acoes");
        }

        $response = ['success' => false, 'message' => ''];

        try {
            $ordens = $this->request->getPost('ordens');
            if (empty($ordens)) {
                throw new \Exception('Nenhuma ordem foi enviada');
            }

            $this->acoesModel->transStart();

            foreach ($ordens as $item) {
                $this->acoesModel
                    ->where('id', $item['id'])
                    ->where('id_projeto', $idProjeto)
                    ->where('id_etapa IS NULL')
                    ->set(['ordem' => $item['ordem']])
                    ->update();
            }

            $this->acoesModel->transComplete();

            $response['success'] = true;
            $response['message'] = 'Ordem das ações atualizada com sucesso!';
        } catch (\Exception $e) {
            $this->acoesModel->transRollback();
            log_message('error', 'Erro ao reordenar ações diretas: ' . $e->getMessage());
            $response['message'] = $e->getMessage();
        }

        return $this->response->setJSON($response);
    }
    public function atualizarStatus($idProjeto)
    {
        $novoStatus = $this->request->getPost('status');

        $this->projetosModel->atualizarStatus($idProjeto, $novoStatus);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status do projeto e ações atualizados!'
        ]);
    }
}
