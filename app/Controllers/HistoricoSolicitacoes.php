<?php

namespace App\Controllers;

use App\Models\SolicitacoesModel;
use CodeIgniter\Shield\Models\UserModel;

class HistoricoSolicitacoes extends BaseController
{
    protected $solicitacoesModel;
    protected $userModel;

    public function __construct()
    {
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Busca solicitações que não estão pendentes
        $solicitacoes = $this->solicitacoesModel
            ->where('status !=', 'pendente')
            ->orderBy('data_avaliacao', 'DESC')
            ->findAll();

        // Processa os dados para a view
        foreach ($solicitacoes as &$solicitacao) {
            $dados = json_decode($solicitacao['dados_atuais'] ?? '{}', true);

            // Para solicitações de inclusão, pega o nome dos dados alterados
            if ($solicitacao['tipo'] == 'inclusão' && !empty($solicitacao['dados_alterados'])) {
                $dadosAlterados = json_decode($solicitacao['dados_alterados'], true);
                $solicitacao['nome'] = $dadosAlterados['etapa'] ?? $dadosAlterados['acao'] ?? $dadosAlterados['nome'] ?? 'Nova Solicitação';
            } else {
                $solicitacao['nome'] = $dados['etapa'] ?? $dados['acao'] ?? $dados['nome'] ?? 'Solicitação';
            }

            // Obtém username do avaliador
            if (!empty($solicitacao['id_avaliador'])) {
                $avaliador = $this->userModel->find($solicitacao['id_avaliador']);
                $solicitacao['avaliador_username'] = $avaliador->username ?? 'Desconhecido';
            } else {
                $solicitacao['avaliador_username'] = 'Sistema';
            }
        }

        $data = ['solicitacoes' => $solicitacoes];
        $this->content_data['content'] = view('sys/historico-solicitacoes', $data);
        return view('layout', $this->content_data);
    }

    public function detalhes($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $solicitacao = $this->solicitacoesModel->find($id);
            if (!$solicitacao) {
                throw new \Exception('Solicitação não encontrada');
            }

            // Busca o nome do avaliador
            $avaliadorNome = 'Sistema';
            if ($solicitacao['id_avaliador']) {
                $user = $this->userModel->find($solicitacao['id_avaliador']);
                $avaliadorNome = $user ? $user->username : 'Usuário removido';
            }

            // Processa os dados JSON
            $dadosAtuais = json_decode($solicitacao['dados_atuais'], true) ?: [];
            $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?: [];

            // Log para depuração (remova em produção)
            log_message('debug', 'Dados da solicitação: ' . print_r($solicitacao, true));

            return $this->response->setJSON([
                'success' => true,
                'data' => array_merge($solicitacao, [
                    'avaliador_nome' => $avaliadorNome,
                    'tipo' => $solicitacao['tipo'],
                    'nivel' => $solicitacao['nivel']
                ]),
                'dados_atuais' => $dadosAtuais,
                'dados_alterados' => $dadosAlterados
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao carregar solicitação: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
