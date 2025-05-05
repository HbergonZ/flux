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

            // Define o nome para exibição
            $solicitacao['nome'] = $dados['etapa'] ?? $dados['acao'] ?? $dados['nome'] ?? 'Solicitação';

            // Obtém username do avaliador usando o UserModel do Shield
            if (!empty($solicitacao['id_avaliador'])) {
                $avaliador = $this->userModel->find($solicitacao['id_avaliador']);
                // O Shield usa 'username' como padrão para o nome de usuário
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

        // Carrega o model de usuários
        $userModel = new UserModel();

        $solicitacao = $this->solicitacoesModel->find($id);
        if (!$solicitacao) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitação não encontrada'
            ]);
        }

        // Busca o nome do avaliador
        $avaliadorNome = 'Sistema';
        if ($solicitacao['id_avaliador']) {
            $user = $userModel->find($solicitacao['id_avaliador']);
            $avaliadorNome = $user ? $user->username : 'Usuário removido';
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => array_merge($solicitacao, ['avaliador_nome' => $avaliadorNome]),
            'dados_atuais' => json_decode($solicitacao['dados_atuais'], true),
            'dados_alterados' => json_decode($solicitacao['dados_alterados'], true)
        ]);
    }
}
