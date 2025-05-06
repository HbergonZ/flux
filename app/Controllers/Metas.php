<?php

namespace App\Controllers;

use App\Models\MetasModel;
use App\Models\AcoesModel;
use App\Models\SolicitacoesModel;

class Metas extends BaseController
{
    protected $metasModel;
    protected $acoesModel;
    protected $solicitacoesModel;

    public function __construct()
    {
        $this->metasModel = new MetasModel();
        $this->acoesModel = new AcoesModel();
        $this->solicitacoesModel = new SolicitacoesModel();
    }

    public function index($idAcao = null)
    {
        if (empty($idAcao)) {
            return redirect()->to('/acoes');
        }

        // Busca a ação para exibir o nome
        $acao = $this->acoesModel->find($idAcao);
        if (!$acao) {
            return redirect()->to('/acoes');
        }

        // Busca as metas vinculadas à ação
        $metas = $this->metasModel->getMetasByAcao($idAcao);

        $data = [
            'acao' => $acao,
            'metas' => $metas,
            'idAcao' => $idAcao
        ];

        $this->content_data['content'] = view('sys/metas', $data);
        return view('layout', $this->content_data);
    }

    public function cadastrar($idAcao)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/metas/$idAcao");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'nome' => 'required|min_length[3]|max_length[255]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'nome' => $this->request->getPost('nome'),
                    'id_acao' => $idAcao
                ];

                $this->metasModel->insert($data);
                $response['success'] = true;
                $response['message'] = 'Meta cadastrada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao cadastrar meta: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function editar($idMeta = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/acoes');
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $meta = $this->metasModel->find($idMeta);

        if ($meta) {
            $response['success'] = true;
            $response['data'] = $meta;
        } else {
            $response['message'] = 'Meta não encontrada';
        }

        return $this->response->setJSON($response);
    }

    public function atualizar($idAcao)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/metas/$idAcao");
        }

        $response = ['success' => false, 'message' => ''];

        $rules = [
            'id' => 'required',
            'nome' => 'required|min_length[3]|max_length[255]'
        ];

        if ($this->validate($rules)) {
            try {
                $data = [
                    'id' => $this->request->getPost('id'),
                    'nome' => $this->request->getPost('nome')
                ];

                $this->metasModel->save($data);
                $response['success'] = true;
                $response['message'] = 'Meta atualizada com sucesso!';
            } catch (\Exception $e) {
                $response['message'] = 'Erro ao atualizar meta: ' . $e->getMessage();
            }
        } else {
            $response['message'] = implode('<br>', $this->validator->getErrors());
        }

        return $this->response->setJSON($response);
    }

    public function excluir($idAcao)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/metas/$idAcao");
        }

        $response = ['success' => false, 'message' => ''];
        $id = $this->request->getPost('id');

        if ($this->metasModel->delete($id)) {
            $response['success'] = true;
            $response['message'] = 'Meta excluída com sucesso!';
        } else {
            $response['message'] = 'Erro ao excluir meta';
        }

        return $this->response->setJSON($response);
    }

    public function filtrar($idAcao)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to("/metas/$idAcao");
        }

        $filtro = $this->request->getPost('nome');

        $builder = $this->metasModel->where('id_acao', $idAcao);

        if (!empty($filtro)) {
            $builder->like('nome', $filtro);
        }

        $metas = $builder->findAll();
        return $this->response->setJSON(['success' => true, 'data' => $metas]);
    }
    public function dadosMeta($idMeta = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $response = ['success' => false, 'message' => '', 'data' => null];
        $meta = $this->metasModel->find($idMeta);

        if ($meta) {
            $response['success'] = true;
            $response['data'] = $meta;
        } else {
            $response['message'] = 'Meta não encontrada';
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
            'id_meta' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $metaAtual = $this->metasModel->find($postData['id_meta']);
                if (!$metaAtual) {
                    $response['message'] = 'Meta não encontrada';
                    return $this->response->setJSON($response);
                }

                // Verificar alterações
                $alteracoes = [];
                $camposEditaveis = ['nome'];

                foreach ($camposEditaveis as $campo) {
                    if (isset($postData[$campo]) && $postData[$campo] != $metaAtual[$campo]) {
                        $alteracoes[$campo] = [
                            'de' => $metaAtual[$campo],
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
                    'nivel' => 'meta',
                    'id_meta' => $postData['id_meta'],
                    'id_acao' => $metaAtual['id_acao'],
                    'id_plano' => $postData['id_plano'],
                    'tipo' => 'Edição',
                    'dados_atuais' => json_encode($metaAtual),
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
            'id_meta' => 'required',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $meta = $this->metasModel->find($postData['id_meta']);
                if (!$meta) {
                    $response['message'] = 'Meta não encontrada';
                    return $this->response->setJSON($response);
                }

                $dadosAtuais = [
                    'nome' => $meta['nome'],
                    'id_acao' => $meta['id_acao']
                ];

                $data = [
                    'nivel' => 'meta',
                    'id_meta' => $postData['id_meta'],
                    'id_acao' => $meta['id_acao'],
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
            'nome' => 'required|min_length[3]|max_length[255]',
            'justificativa' => 'required'
        ];

        if ($this->validate($rules)) {
            try {
                $dadosAlterados = [
                    'nome' => $postData['nome'],
                    'id_acao' => $postData['id_acao']
                ];

                $data = [
                    'nivel' => 'meta',
                    'id_acao' => $postData['id_acao'],
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
