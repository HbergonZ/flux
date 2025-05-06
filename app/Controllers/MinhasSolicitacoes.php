<?php

namespace App\Controllers;

use App\Models\SolicitacoesModel;
use CodeIgniter\Shield\Models\UserModel;

class MinhasSolicitacoes extends BaseController
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
        // Obtém o USERNAME do usuário logado (busca atual por username)
        $userName = auth()->user()->username;

        // Busca solicitações pelo username do usuário logado
        $solicitacoes = $this->solicitacoesModel
            ->where('solicitante', $userName) // Filtro por username
            ->orderBy('data_solicitacao', 'DESC')
            ->findAll();

        /*
        // CÓDIGO ALTERNATIVO (para quando migrar para salvar user_id):
        // $userId = auth()->id();
        // $solicitacoes = $this->solicitacoesModel
        //     ->where('solicitante', $userId) // Filtro por user_id
        //     ->orderBy('data_solicitacao', 'DESC')
        //     ->findAll();
        */

        // Processa os dados para a view (mantém o username na exibição)
        foreach ($solicitacoes as &$solicitacao) {
            $dados = json_decode($solicitacao['dados_atuais'] ?? '{}', true);

            if ($solicitacao['tipo'] == 'inclusão' && !empty($solicitacao['dados_alterados'])) {
                $dadosAlterados = json_decode($solicitacao['dados_alterados'], true);
                $solicitacao['nome'] = $dadosAlterados['etapa'] ?? $dadosAlterados['acao'] ?? $dadosAlterados['nome'] ?? 'Nova Solicitação';
            } else {
                $solicitacao['nome'] = $dados['etapa'] ?? $dados['acao'] ?? $dados['nome'] ?? 'Solicitação';
            }

            // Obtém username do avaliador (se existir)
            if (!empty($solicitacao['id_avaliador'])) {
                $avaliador = $this->userModel->find($solicitacao['id_avaliador']);
                $solicitacao['avaliador_username'] = $avaliador->username ?? 'Desconhecido';
            } else {
                $solicitacao['avaliador_username'] = $solicitacao['status'] === 'pendente' ? 'Aguardando avaliação' : 'Sistema';
            }

            // Já está salvando o username, então não precisa buscar
            $solicitacao['solicitante'] = $userName; // Exibe o username diretamente
        }

        $data = ['solicitacoes' => $solicitacoes];
        $this->content_data['content'] = view('sys/minhas-solicitacoes', $data);
        return view('layout', $this->content_data);
    }

    public function detalhes($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            // Verifica se a solicitação pertence ao usuário logado (por username)
            $userName = auth()->user()->username;
            $solicitacao = $this->solicitacoesModel
                ->where('id', $id)
                ->where('solicitante', $userName) // Filtro por username
                ->first();

            /*
            // CÓDIGO ALTERNATIVO (para quando migrar para user_id):
            // $userId = auth()->id();
            // $solicitacao = $this->solicitacoesModel
            //     ->where('id', $id)
            //     ->where('solicitante', $userId) // Filtro por user_id
            //     ->first();
            */

            if (!$solicitacao) {
                throw new \Exception('Solicitação não encontrada ou não pertence ao usuário');
            }

            // Busca o nome do avaliador (se existir)
            $avaliadorNome = 'Sistema';
            if ($solicitacao['id_avaliador']) {
                $user = $this->userModel->find($solicitacao['id_avaliador']);
                $avaliadorNome = $user ? $user->username : 'Usuário removido';
            }

            // Processa os dados JSON
            $dadosAtuais = json_decode($solicitacao['dados_atuais'], true) ?: [];
            $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?: [];

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
