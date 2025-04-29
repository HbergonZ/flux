<?php

namespace App\Controllers;

use App\Models\ProjetoModel;
use App\Models\EtapaModel;

class VisaoProjeto extends BaseController
{
    protected $projetoModel;
    protected $etapaModel;

    public function __construct()
    {
        $this->projetoModel = new ProjetoModel();
        $this->etapaModel = new EtapaModel();
    }

    public function index($id = null)
    {
        if (empty($id)) {
            log_message('error', 'Tentativa de acesso sem ID');
            return redirect()->to('/projetos-cadastrados');
        }

        log_message('info', 'Acessando projeto ID: ' . $id);
        $projeto = $this->projetoModel->find($id);

        if (!$projeto) {
            log_message('error', 'Projeto não encontrado: ' . $id);
            return redirect()->to('/projetos-cadastrados')->with('error', 'Projeto não encontrado');
        }

        $etapas = $this->etapaModel->where('id_projeto', $id)->findAll();
        log_message('debug', 'Número de etapas encontradas: ' . count($etapas));

        $data['projeto'] = $projeto;
        $data['etapas'] = $etapas;

        return view('layout', ['content' => view('sys/visao-projeto', $data)]);
    }

    public function dadosEtapa($idEtapa, $idAcao)
    {
        log_message('debug', 'Iniciando dadosEtapa - ID Etapa: ' . $idEtapa . ', ID Ação: ' . $idAcao);

        if (!$this->request->isAJAX()) {
            log_message('error', 'Tentativa de acesso não AJAX');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ])->setStatusCode(403);
        }

        try {
            $etapa = $this->etapaModel
                ->where('id_etapa', $idEtapa)
                ->where('id_acao', $idAcao)
                ->first();

            if (!$etapa) {
                log_message('error', 'Etapa não encontrada - ID Etapa: ' . $idEtapa . ', ID Ação: ' . $idAcao);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Etapa/Ação não encontrada'
                ])->setStatusCode(404);
            }

            log_message('debug', 'Dados encontrados: ' . print_r($etapa, true));
            return $this->response->setJSON([
                'success' => true,
                'data' => $etapa
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro em dadosEtapa: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro interno no servidor'
            ])->setStatusCode(500);
        }
    }

    public function solicitarEdicao()
    {
        log_message('debug', 'Iniciando solicitarEdicao');

        if (!$this->request->isAJAX()) {
            log_message('error', 'Tentativa de acesso não AJAX');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ])->setStatusCode(403);
        }

        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        // Validação dos dados
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_etapa' => 'required|numeric',
            'id_acao' => 'required',
            'id_projeto' => 'required|numeric',
            'dados_atuais' => 'required',
            'dados_alterados' => 'required',
            'justificativa' => 'required|min_length[10]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $validation->getErrors())
            ]);
        }

        // Insere a solicitação no banco de dados
        $db = \Config\Database::connect();
        $builder = $db->table('solicitacoes_edicao');

        try {
            $builder->insert([
                'id_etapa' => $this->request->getPost('id_etapa'),
                'id_acao' => $this->request->getPost('id_acao'),
                'id_projeto' => $this->request->getPost('id_projeto'),
                'dados_atuais' => $this->request->getPost('dados_atuais'),
                'dados_alterados' => $this->request->getPost('dados_alterados'),
                'justificativa' => $this->request->getPost('justificativa'),
                'status' => 'pendente'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Solicitação de edição registrada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao registrar solicitação: ' . $e->getMessage()
            ]);
        }
    }
}
