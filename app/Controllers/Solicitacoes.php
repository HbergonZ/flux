<?php

namespace App\Controllers;

use App\Models\SolicitacoesModel;
use App\Models\EtapasModel;

class Solicitacoes extends BaseController
{
    protected $solicitacoesModel;
    protected $etapasModel;

    public function __construct()
    {
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->etapasModel = new EtapasModel();
    }

    public function index()
    {
        $solicitacoes = $this->solicitacoesModel->where('status', 'pendente')->findAll();

        // Processa os dados para a view
        foreach ($solicitacoes as &$solicitacao) {
            $dados = json_decode($solicitacao['dados_atuais'] ?? '{}', true);

            // Define o nome para exibição
            $solicitacao['nome'] = $dados['etapa'] ?? $dados['acao'] ?? $dados['nome'] ?? 'Nova Solicitação';
        }

        $data = ['solicitacoes' => $solicitacoes];
        $this->content_data['content'] = view('sys/solicitacoes', $data);
        return view('layout', $this->content_data);
    }

    public function avaliar($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $solicitacao = $this->solicitacoesModel->find($id);
        if (!$solicitacao) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solicitação não encontrada']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $solicitacao,
            'dados_atuais' => json_decode($solicitacao['dados_atuais']),
            'dados_alterados' => json_decode($solicitacao['dados_alterados'])
        ]);
    }

    public function processar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $id = $this->request->getPost('id');
        $acao = $this->request->getPost('acao');
        $justificativaAvaliador = $this->request->getPost('justificativa');

        // Validação básica
        if (empty($id) || empty($acao)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parâmetros inválidos'
            ]);
        }

        $solicitacao = $this->solicitacoesModel->find($id);
        if (!$solicitacao) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitação não encontrada'
            ]);
        }

        // Determina o status
        $status = ($acao === 'aceitar') ? 'aprovada' : 'rejeitada';

        // Dados para atualização
        $dadosAtualizacao = [
            'status' => $status,
            'data_avaliacao' => date('Y-m-d H:i:s'),
            'id_avaliador' => auth()->user()->id,
            'justificativa_avaliador' => $justificativaAvaliador
        ];

        // Atualiza a solicitação
        if (!$this->solicitacoesModel->update($id, $dadosAtualizacao)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar solicitação',
                'errors' => $this->solicitacoesModel->errors()
            ]);
        }

        // Se aceito, aplica as alterações
        if ($acao === 'aceitar') {
            switch ($solicitacao['nivel']) {
                case 'etapa':
                    $this->processarEtapa($solicitacao);
                    break;
                    // Adicione outros casos conforme necessário
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Solicitação ' . ($acao === 'aceitar' ? 'aprovada' : 'recusada') . ' com sucesso',
            'status' => $status
        ]);
    }

    protected function processarEtapa($solicitacao)
    {
        if ($solicitacao['tipo'] == 'edicao') {
            $dados = json_decode($solicitacao['dados_alterados'], true);
            $atualizacao = [];
            foreach ($dados as $campo => $valores) {
                $atualizacao[$campo] = $valores['para'];
            }
            $this->etapasModel->update($solicitacao['id_etapa'], $atualizacao);
        } elseif ($solicitacao['tipo'] == 'inclusao') {
            $dados = json_decode($solicitacao['dados_alterados'], true);
            $this->etapasModel->insert($dados);
        } elseif ($solicitacao['tipo'] == 'exclusao') {
            $this->etapasModel->delete($solicitacao['id_etapa']);
        }
    }
}
