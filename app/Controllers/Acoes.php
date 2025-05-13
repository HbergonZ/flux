<?php

namespace App\Controllers;

use App\Models\AcoesModel;
use App\Models\ProjetosModel;
use App\Models\EtapasModel;
use App\Models\SolicitacoesModel;

class Acoes extends BaseController
{
    protected $acoesModel;
    protected $projetosModel;
    protected $etapasModel;
    protected $solicitacoesModel;

    public function __construct()
    {
        $this->acoesModel = new AcoesModel();
        $this->projetosModel = new ProjetosModel();
        $this->etapasModel = new EtapasModel();
        $this->solicitacoesModel = new SolicitacoesModel();
    }

    public function index($idProjeto = null)
    {
        if (empty($idProjeto)) {
            return redirect()->to('/projetos');
        }

        $projeto = $this->projetosModel->find($idProjeto);
        if (!$projeto) {
            return redirect()->to('/projetos');
        }

        $acoes = $this->acoesModel->getAcoesByProjeto($idProjeto);

        $data = [
            'tipo' => 'projeto',
            'idVinculo' => $idProjeto,
            'idPlano' => $projeto['id_plano'],
            'nomeVinculo' => $projeto['projeto'],
            'acoes' => $acoes,
            'projeto' => $projeto
        ];

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
    }

    public function etapa($idEtapa = null)
    {
        if (empty($idEtapa)) {
            return redirect()->to('/etapas');
        }

        $etapa = $this->etapasModel->find($idEtapa);
        if (!$etapa) {
            return redirect()->to('/etapas');
        }

        $projeto = $this->projetosModel->find($etapa['id_projeto']);
        if (!$projeto) {
            return redirect()->to('/projetos');
        }

        $acoes = $this->acoesModel->getAcoesByEtapa($idEtapa);

        $data = [
            'tipo' => 'etapa',
            'idVinculo' => $idEtapa,
            'idProjeto' => $etapa['id_projeto'],
            'nomeVinculo' => $etapa['etapa'],
            'acoes' => $acoes,
            'projeto' => [
                'id' => $projeto['id'],
                'projeto' => $projeto['projeto'],
                'id_plano' => $projeto['id_plano'],
                'nome_etapa' => $etapa['etapa']
            ]
        ];

        $this->content_data['content'] = view('sys/acoes', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'acao' => 'required|max_length[255]',
            'projeto' => 'required|max_length[255]',
            'responsavel' => 'required|max_length[255]',
            'equipe' => 'required|max_length[255]',
            'tempo_estimado_dias' => 'required|integer',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
            'status' => 'required|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'acao' => $this->request->getPost('acao'),
                    'projeto' => $this->request->getPost('projeto'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'data_inicio' => $this->request->getPost('data_inicio'),
                    'data_fim' => $this->request->getPost('data_fim'),
                    'status' => $this->request->getPost('status'),
                    'id_projeto' => $tipo === 'projeto' ? $idVinculo : null,
                    'id_etapa' => $tipo === 'etapa' ? $idVinculo : null
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

    public function atualizar($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id_acao' => 'required',
            'acao' => 'required|max_length[255]',
            'projeto' => 'required|max_length[255]',
            'responsavel' => 'required|max_length[255]',
            'equipe' => 'required|max_length[255]',
            'tempo_estimado_dias' => 'required|integer',
            'data_inicio' => 'valid_date',
            'data_fim' => 'valid_date',
            'status' => 'required|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id_acao' => $this->request->getPost('id_acao'),
                    'acao' => $this->request->getPost('acao'),
                    'projeto' => $this->request->getPost('projeto'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
                    'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
                    'data_inicio' => $this->request->getPost('data_inicio'),
                    'data_fim' => $this->request->getPost('data_fim'),
                    'status' => $this->request->getPost('status')
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

    public function excluir($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        if ($this->acoesModel->delete($id)) {
            $response['success'] = true;
            $response['message'] = 'Ação excluída com sucesso!';
        } else {
            $response['message'] = 'Erro ao excluir ação';
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $filtros = [
            'acao' => $this->request->getPost('acao'),
            'projeto' => $this->request->getPost('projeto'),
            'responsavel' => $this->request->getPost('responsavel'),
            'equipe' => $this->request->getPost('equipe'),
            'tempo_estimado_dias' => $this->request->getPost('tempo_estimado_dias'),
            'data_inicio' => $this->request->getPost('data_inicio'),
            'data_fim' => $this->request->getPost('data_fim'),
            'status' => $this->request->getPost('status')
        ];

        $builder = $tipo === 'projeto'
            ? $this->acoesModel->where('id_projeto', $idVinculo)
            : $this->acoesModel->where('id_etapa', $idVinculo);

        if (!empty($filtros['acao'])) {
            $builder->like('acao', $filtros['acao']);
        }

        if (!empty($filtros['projeto'])) {
            $builder->like('projeto', $filtros['projeto']);
        }

        if (!empty($filtros['responsavel'])) {
            $builder->like('responsavel', $filtros['responsavel']);
        }

        if (!empty($filtros['equipe'])) {
            $builder->like('equipe', $filtros['equipe']);
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

        $acoes = $builder->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $acoes]);
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
                $camposEditaveis = ['acao', 'projeto', 'responsavel', 'equipe', 'tempo_estimado_dias', 'data_inicio', 'data_fim', 'status'];

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
                    'id_projeto' => $acaoAtual['id_projeto'],
                    'id_etapa' => $acaoAtual['id_etapa'],
                    'id_plano' => $postData['id_plano'] ?? null,
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
                    'projeto' => $acao['projeto'],
                    'responsavel' => $acao['responsavel'],
                    'equipe' => $acao['equipe'],
                    'tempo_estimado_dias' => $acao['tempo_estimado_dias'],
                    'data_inicio' => $acao['data_inicio'],
                    'data_fim' => $acao['data_fim'],
                    'status' => $acao['status']
                ];

                $data = [
                    'nivel' => 'acao',
                    'id_acao' => $postData['id_acao'],
                    'id_projeto' => $acao['id_projeto'],
                    'id_etapa' => $acao['id_etapa'],
                    'id_plano' => $postData['id_plano'] ?? null,
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
            'projeto' => 'required|max_length[255]',
            'responsavel' => 'required|max_length[255]',
            'equipe' => 'required|max_length[255]',
            'tempo_estimado_dias' => 'required|integer',
            'data_inicio' => 'permit_empty|valid_date',
            'data_fim' => 'permit_empty|valid_date',
            'status' => 'required|in_list[Em andamento,Finalizado,Paralisado,Não iniciado]',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $dadosAlterados = [
                    'acao' => $postData['acao'],
                    'projeto' => $postData['projeto'],
                    'responsavel' => $postData['responsavel'],
                    'equipe' => $postData['equipe'],
                    'tempo_estimado_dias' => $postData['tempo_estimado_dias'],
                    'data_inicio' => $postData['data_inicio'],
                    'data_fim' => $postData['data_fim'],
                    'status' => $postData['status'],
                    'id_projeto' => $postData['id_projeto'] ?? null,
                    'id_etapa' => $postData['id_etapa'] ?? null
                ];

                $data = [
                    'nivel' => 'acao',
                    'id_projeto' => !empty($postData['id_projeto']) ? $postData['id_projeto'] : null,
                    'id_plano' => !empty($postData['id_plano']) ? $postData['id_plano'] : null,
                    'id_etapa' => !empty($postData['id_etapa']) ? $postData['id_etapa'] : null,
                    'tipo' => 'Inclusão',
                    'dados_alterados' => json_encode($dadosAlterados),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'solicitante' => auth()->user()->username,
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                if (array_key_exists('id_acao', $data)) {
                    unset($data['id_acao']);
                }

                log_message('debug', 'Dados sendo inseridos: ' . print_r($data, true));

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de inclusão enviada com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarInclusao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();

                $response['debug'] = [
                    'postData' => $postData,
                    'trace' => $e->getTraceAsString()
                ];
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
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
}
