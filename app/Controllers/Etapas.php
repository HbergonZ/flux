<?php

namespace App\Controllers;

use App\Models\EtapasModel;
use App\Models\AcoesModel;
use App\Models\MetasModel;
use App\Models\SolicitacoesModel;

class Etapas extends BaseController
{
    protected $etapasModel;
    protected $acoesModel;
    protected $metasModel;
    protected $solicitacoesModel;

    public function __construct()
    {
        $this->etapasModel = new EtapasModel();
        $this->acoesModel = new AcoesModel();
        $this->metasModel = new MetasModel();
        $this->solicitacoesModel = new SolicitacoesModel();
    }

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
            'idPlano' => $acao['id_plano'],
            'nomeVinculo' => $acao['acao'],
            'etapas' => $etapas,
            'acao' => $acao
        ];

        $this->content_data['content'] = view('sys/etapas', $data);
        return view('layout', $this->content_data);
    }

    public function meta($idMeta = null)
    {
        if (empty($idMeta)) {
            return redirect()->to('/metas');
        }

        $meta = $this->metasModel->find($idMeta);
        if (!$meta) {
            return redirect()->to('/metas');
        }

        $acao = $this->acoesModel->find($meta['id_acao']);
        if (!$acao) {
            return redirect()->to('/acoes');
        }

        $etapas = $this->etapasModel->getEtapasByMeta($idMeta);

        $data = [
            'tipo' => 'meta',
            'idVinculo' => $idMeta,
            'idAcao' => $meta['id_acao'],
            'nomeVinculo' => $meta['nome'],
            'etapas' => $etapas,
            'acao' => [
                'id' => $acao['id'],
                'acao' => $acao['acao'],
                'id_plano' => $acao['id_plano'],
                'nome_meta' => $meta['nome'] // Adiciona o nome da meta aqui
            ]
        ];

        $this->content_data['content'] = view('sys/etapas', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'etapa' => 'required|max_length[255]',
            'acao' => 'required|max_length[255]',
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
                    'etapa' => $this->request->getPost('etapa'),
                    'acao' => $this->request->getPost('acao'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
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

    public function atualizar($tipo, $idVinculo)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id_etapa' => 'required',
            'etapa' => 'required|max_length[255]',
            'acao' => 'required|max_length[255]',
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
                    'id_etapa' => $this->request->getPost('id_etapa'),
                    'etapa' => $this->request->getPost('etapa'),
                    'acao' => $this->request->getPost('acao'),
                    'responsavel' => $this->request->getPost('responsavel'),
                    'equipe' => $this->request->getPost('equipe'),
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
            return redirect()->back();
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
            return redirect()->back();
        }

        $filtros = [
            'etapa' => $this->request->getPost('etapa'),
            'acao' => $this->request->getPost('acao'),
            'responsavel' => $this->request->getPost('responsavel'),
            'equipe' => $this->request->getPost('equipe'),
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

        $etapas = $builder->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $etapas]);
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

                // Verificar alterações
                $alteracoes = [];
                $camposEditaveis = ['etapa', 'acao', 'responsavel', 'equipe', 'tempo_estimado_dias', 'data_inicio', 'data_fim', 'status'];

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

                // Preparar dados para a solicitação
                $data = [
                    'nivel' => 'etapa',
                    'id_etapa' => $postData['id_etapa'],
                    'id_acao' => $etapaAtual['id_acao'],
                    'id_meta' => $etapaAtual['id_meta'],
                    'id_plano' => $postData['id_plano'] ?? null,
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($etapaAtual),
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
                    'etapa' => $etapa['etapa'],
                    'acao' => $etapa['acao'],
                    'responsavel' => $etapa['responsavel'],
                    'equipe' => $etapa['equipe'],
                    'tempo_estimado_dias' => $etapa['tempo_estimado_dias'],
                    'data_inicio' => $etapa['data_inicio'],
                    'data_fim' => $etapa['data_fim'],
                    'status' => $etapa['status']
                ];

                $data = [
                    'nivel' => 'etapa', // Define o nível como etapa
                    'id_etapa' => $postData['id_etapa'],
                    'id_acao' => $etapa['id_acao'],
                    'id_meta' => $etapa['id_meta'],
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
            'etapa' => 'required|max_length[255]',
            'acao' => 'required|max_length[255]',
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
                    'etapa' => $postData['etapa'],
                    'acao' => $postData['acao'],
                    'responsavel' => $postData['responsavel'],
                    'equipe' => $postData['equipe'],
                    'tempo_estimado_dias' => $postData['tempo_estimado_dias'],
                    'data_inicio' => $postData['data_inicio'],
                    'data_fim' => $postData['data_fim'],
                    'status' => $postData['status'],
                    'id_acao' => $postData['id_acao'] ?? null,
                    'id_meta' => $postData['id_meta'] ?? null
                ];

                $data = [
                    'nivel' => 'etapa',
                    'id_acao' => !empty($postData['id_acao']) ? $postData['id_acao'] : null,
                    'id_plano' => !empty($postData['id_plano']) ? $postData['id_plano'] : null,
                    'id_meta' => !empty($postData['id_meta']) ? $postData['id_meta'] : null,
                    'tipo' => 'Inclusão',
                    'dados_alterados' => json_encode($dadosAlterados),
                    'justificativa_solicitante' => $postData['justificativa'],
                    'solicitante' => auth()->user()->username,
                    'status' => 'pendente',
                    'data_solicitacao' => date('Y-m-d H:i:s')
                ];

                // Garantir que id_etapa não está sendo enviado de forma alguma
                if (array_key_exists('id_etapa', $data)) {
                    unset($data['id_etapa']);
                }

                // Debug: Verifique os dados antes de inserir
                log_message('debug', 'Dados sendo inseridos: ' . print_r($data, true));

                $this->solicitacoesModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Solicitação de inclusão enviada com sucesso!';
            } catch (\Exception $e) {
                log_message('error', 'Erro em solicitarInclusao: ' . $e->getMessage());
                $response['message'] = 'Erro ao processar solicitação: ' . $e->getMessage();

                // Adicione informações adicionais de debug
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
}
