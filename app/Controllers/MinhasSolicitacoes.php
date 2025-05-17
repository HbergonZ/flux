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
        // Obtém o ID do usuário logado
        $userId = auth()->id();

        // Busca solicitações pelo id_solicitante do usuário logado
        $solicitacoes = $this->solicitacoesModel
            ->where('id_solicitante', $userId)
            ->orderBy('data_solicitacao', 'DESC')
            ->findAll();

        // Processa os dados para a view
        foreach ($solicitacoes as &$solicitacao) {
            $dados = json_decode($solicitacao['dados_atuais'] ?? '{}', true);

            if ($solicitacao['tipo'] == 'inclusão' && !empty($solicitacao['dados_alterados'])) {
                $dadosAlterados = json_decode($solicitacao['dados_alterados'], true);
                $solicitacao['nome'] = $dadosAlterados['etapa'] ?? $dadosAlterados['acao'] ?? $dadosAlterados['nome'] ?? 'Nova Solicitação';
            } else {
                $solicitacao['nome'] = $dados['etapa'] ?? $dados['acao'] ?? $dados['nome'] ?? 'Solicitação';
            }

            // Obtém username do solicitante
            $solicitante = $this->userModel->find($solicitacao['id_solicitante']);
            $solicitacao['solicitante_username'] = $solicitante->username ?? 'Usuário removido';

            // Obtém username do avaliador (se existir)
            if (!empty($solicitacao['id_avaliador'])) {
                $avaliador = $this->userModel->find($solicitacao['id_avaliador']);
                $solicitacao['avaliador_username'] = $avaliador->username ?? 'Desconhecido';
            } else {
                $solicitacao['avaliador_username'] = $solicitacao['status'] === 'pendente' ? 'Aguardando avaliação' : 'Sistema';
            }
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
            // Verifica se a solicitação pertence ao usuário logado (por id_solicitante)
            $userId = auth()->id();
            $solicitacao = $this->solicitacoesModel
                ->where('id', $id)
                ->where('id_solicitante', $userId)
                ->first();

            if (!$solicitacao) {
                throw new \Exception('Solicitação não encontrada ou não pertence ao usuário');
            }

            // Busca o nome do solicitante
            $solicitante = $this->userModel->find($solicitacao['id_solicitante']);
            $solicitanteNome = $solicitante ? $solicitante->username : 'Usuário removido';

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
                    'solicitante_nome' => $solicitanteNome,
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
