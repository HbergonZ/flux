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
        // Busca todas as solicitações avaliadas (não pendentes)
        $solicitacoes = $this->solicitacoesModel
            ->where('status !=', 'pendente')
            ->orderBy('data_avaliacao', 'DESC')
            ->findAll();

        // Processa os dados para a view
        foreach ($solicitacoes as &$solicitacao) {
            $dadosAtuais = json_decode($solicitacao['dados_atuais'] ?? '{}', true);
            $dadosAlterados = json_decode($solicitacao['dados_alterados'] ?? '{}', true);

            // Define o nome baseado no nível e tipo da solicitação
            if ($solicitacao['tipo'] == 'inclusão' && !empty($dadosAlterados)) {
                // Para inclusões, pega o nome dos dados alterados
                $solicitacao['nome'] = $dadosAlterados['nome'] ??
                    $dadosAlterados['etapa'] ??
                    $dadosAlterados['acao'] ??
                    'Nova Solicitação';
            } else {
                // Para edições/exclusões, pega o nome dos dados atuais
                $solicitacao['nome'] = $dadosAtuais['nome'] ??
                    $dadosAtuais['etapa'] ??
                    $dadosAtuais['acao'] ??
                    'Solicitação';
            }

            // Obtém nome do solicitante
            $solicitacao['solicitante'] = $this->getUserName($solicitacao['id_solicitante']);

            // Obtém nome do avaliador
            $solicitacao['avaliador_username'] = $this->getUserName($solicitacao['id_avaliador']);
        }

        $data = ['solicitacoes' => $solicitacoes];
        $this->content_data['content'] = view('sys/historico-solicitacoes', $data);
        return view('layout', $this->content_data);
    }

    protected function getUserName($userId)
    {
        if (empty($userId)) {
            return 'Sistema';
        }

        try {
            $user = $this->userModel->find($userId);
            return $user ? $user->username : 'Usuário #' . $userId;
        } catch (\Exception $e) {
            return 'Usuário #' . $userId;
        }
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
            $avaliadorNome = $this->getUserName($solicitacao['id_avaliador']);

            // Busca o nome do solicitante
            $solicitanteNome = $this->getUserName($solicitacao['id_solicitante']);

            // Processa os dados JSON
            $dadosAtuais = json_decode($solicitacao['dados_atuais'], true) ?: [];
            $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?: [];

            return $this->response->setJSON([
                'success' => true,
                'data' => array_merge($solicitacao, [
                    'avaliador_nome' => $avaliadorNome,
                    'solicitante' => $solicitanteNome,
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
