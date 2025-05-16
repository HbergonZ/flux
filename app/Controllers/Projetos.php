<?php

namespace App\Controllers;

use App\Models\ProjetosModel;
use App\Models\PlanosModel;
use App\Models\EixosModel;
use App\Models\SolicitacoesModel;

class Projetos extends BaseController
{
    protected $projetosModel;
    protected $planosModel;
    protected $eixosModel;
    protected $solicitacoesModel;

    public function __construct()
    {
        $this->projetosModel = new ProjetosModel();
        $this->planosModel = new PlanosModel();
        $this->eixosModel = new EixosModel();
        $this->solicitacoesModel = new SolicitacoesModel();
    }

    public function index($idPlano = null)
    {
        // Redirecionamento para URL canônica se acessado pela rota antiga
        if (strpos(current_url(), 'projetos/') !== false && strpos(current_url(), '/etapas') === false) {
            return redirect()->to(site_url("planos/$idPlano/projetos"));
        }

        if (empty($idPlano)) {
            return redirect()->to('/planos');
        }

        $plano = $this->planosModel->find($idPlano);
        if (!$plano) {
            return redirect()->to('/planos');
        }

        $projetos = $this->projetosModel->getProjetosByPlano($idPlano);
        $eixos = $this->eixosModel->findAll();

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
        if (!$this->request->isAJAX()) {
            return redirect()->to("/planos/$idPlano/projetos");
        }

        $response = ['success' => false, 'message' => ''];

        if (!auth()->user()->inGroup('admin')) {
            $response['message'] = 'Você não tem permissão para esta ação';
            return $this->response->setJSON($response);
        }

        $rules = [
            'identificador' => 'required|max_length[10]',
            'nome' => 'required|max_length[255]',
            'descricao' => 'permit_empty',
            'projeto_vinculado' => 'permit_empty|max_length[255]',
            'priorizacao_gab' => 'permit_empty|in_list[0,1]',
            'id_eixo' => 'permit_empty|integer',
            'responsaveis' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'identificador' => $this->request->getPost('identificador'),
                    'nome' => $this->request->getPost('nome'),
                    'descricao' => $this->request->getPost('descricao'),
                    'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
                    'priorizacao_gab' => $this->request->getPost('priorizacao_gab') ?? 0,
                    'id_plano' => $idPlano,
                    'responsaveis' => $this->request->getPost('responsaveis')
                ];

                $idEixo = $this->request->getPost('id_eixo');
                $data['id_eixo'] = !empty($idEixo) ? $idEixo : null;

                $this->projetosModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Projeto cadastrado com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar projeto: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($idProjeto = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/planos');
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $projeto = $this->projetosModel->find($idProjeto);

        if ($projeto) {
            $response['success'] = true;
            $response['data'] = $projeto;
        } else {
            $response['message'] = 'Projeto não encontrado';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/planos/$idPlano/projetos");
        }

        $response = ['success' => false, 'message' => ''];

        if (!auth()->user()->inGroup('admin')) {
            $response['message'] = 'Você não tem permissão para esta ação';
            return $this->response->setJSON($response);
        }

        $rules = [
            'id' => 'required',
            'identificador' => 'required|max_length[10]',
            'nome' => 'required|max_length[255]',
            'descricao' => 'permit_empty',
            'projeto_vinculado' => 'permit_empty|max_length[255]',
            'priorizacao_gab' => 'permit_empty|in_list[0,1]',
            'id_eixo' => 'permit_empty|integer',
            'responsaveis' => 'permit_empty'
        ];

        if ($this->validate($rules)) {
            try {
                $id = $this->request->getPost('id');
                $projeto = $this->projetosModel->find($id);

                if (!$projeto || $projeto['id_plano'] != $idPlano) {
                    $response['message'] = 'Projeto não encontrado ou não pertence a este plano';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'id' => $id,
                    'identificador' => $this->request->getPost('identificador'),
                    'nome' => $this->request->getPost('nome'),
                    'descricao' => $this->request->getPost('descricao'),
                    'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
                    'priorizacao_gab' => $this->request->getPost('priorizacao_gab') ?? 0,
                    'id_eixo' => $this->request->getPost('id_eixo') ?: null,
                    'responsaveis' => $this->request->getPost('responsaveis')
                ];

                $this->projetosModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Projeto atualizado com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro ao atualizar projeto: ' . $e->getMessage());
                $response['message'] = 'Erro ao atualizar projeto: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function excluir($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/planos/$idPlano/projetos");
        }

        $response = ['success' => false, 'message' => ''];

        if (!auth()->user()->inGroup('admin')) {
            $response['message'] = 'Você não tem permissão para esta ação';
            return $this->response->setJSON($response);
        }

        $idProjeto = $this->request->getPost('id'); // Obtém o ID do projeto do POST

        if (empty($idProjeto)) {
            $response['message'] = 'ID do projeto não fornecido';
            return $this->response->setJSON($response);
        }

        try {
            $projeto = $this->projetosModel->find($idProjeto);
            if (!$projeto || $projeto['id_plano'] != $idPlano) {
                $response['message'] = 'Projeto não encontrado ou não pertence a este plano';
                return $this->response->setJSON($response);
            }

            // Verificar dependências
            $etapasModel = new \App\Models\EtapasModel();
            $acoesModel = new \App\Models\AcoesModel();

            if (
                $etapasModel->where('id_projeto', $idProjeto)->countAllResults() > 0 ||
                $acoesModel->where('id_projeto', $idProjeto)->where('id_etapa', null)->countAllResults() > 0
            ) {
                $response['message'] = 'Não é possível excluir o projeto pois existem etapas ou ações vinculadas';
                return $this->response->setJSON($response);
            }

            if ($this->projetosModel->delete($idProjeto)) {
                $response['success'] = true;
                $response['message'] = 'Projeto excluído com sucesso!';
            } else {
                $response['message'] = 'Erro ao excluir projeto';
            }
        } catch (\Exception $e) {
            $response['message'] = 'Erro ao excluir projeto: ' . $e->getMessage();
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($idPlano)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/planos/$idPlano/projetos");
        }

        $filtros = [
            'nome' => $this->request->getPost('nome'),
            'projeto_vinculado' => $this->request->getPost('projeto_vinculado'),
            'id_eixo' => $this->request->getPost('id_eixo')
        ];

        $projetos = $this->projetosModel->getProjetosFiltrados($idPlano, $filtros);
        return $this->response->setJSON(['success' => true, 'data' => $projetos]);
    }

    public function dadosProjeto($idProjeto = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $projeto = $this->projetosModel->find($idProjeto);

        if ($projeto) {
            $response['success'] = true;
            $response['data'] = $projeto;
        } else {
            $response['message'] = 'Projeto não encontrado';
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
            'id_projeto' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $projetoAtual = $this->projetosModel->find($postData['id_projeto']);
                if (!$projetoAtual) {
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
                    $response['message'] = 'Nenhuma alteração detectada';
                    return $this->response->setJSON($response);
                }

                $data = [
                    'nivel' => 'projeto',
                    'id_projeto' => $postData['id_projeto'],
                    'id_plano' => $projetoAtual['id_plano'],
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($projetoAtual, JSON_UNESCAPED_UNICODE),
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
            'id_projeto' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $projeto = $this->projetosModel->find($postData['id_projeto']);
                if (!$projeto) {
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
                    'id_projeto' => $postData['id_projeto'],
                    'id_plano' => $postData['id_plano'],
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
            'identificador' => 'required|max_length[10]',
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
                    'id_plano' => $postData['id_plano'],
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

    public function etapas($idProjeto)
    {
        return redirect()->to(site_url("projetos/$idProjeto/etapas"));
    }

    public function acoes($idProjeto)
    {
        if (empty($idProjeto)) {
            return redirect()->back();
        }

        $projeto = $this->projetosModel->find($idProjeto);
        if (!$projeto) {
            return redirect()->back();
        }

        $plano = $this->planosModel->find($projeto['id_plano']);
        $acoesModel = new \App\Models\AcoesModel();

        $acoes = $acoesModel->where('id_projeto', $idProjeto)
            ->orderBy('ordem', 'ASC')
            ->findAll();

        $data = [
            'projeto' => $projeto,
            'plano' => $plano,
            'acoes' => $acoes,
            'idProjeto' => $idProjeto,
            'acessoDireto' => true
        ];

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrarAcaoDireta($idProjeto)
    {
        if (!$this->request->isAJAX()) {
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

                $acoesModel = new \App\Models\AcoesModel();
                $acoesModel->insert($data);
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
}
