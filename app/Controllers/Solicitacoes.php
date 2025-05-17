<?php

namespace App\Controllers;

use App\Models\SolicitacoesModel;
use App\Models\EtapasModel;
use App\Models\AcoesModel;
use App\Models\ProjetosModel;
use App\Models\PlanosModel;
use CodeIgniter\Shield\Models\UserModel;

class Solicitacoes extends BaseController
{
    protected $solicitacoesModel;
    protected $etapasModel;
    protected $acoesModel;
    protected $projetosModel;
    protected $planosModel;
    protected $userModel;

    public function __construct()
    {
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->etapasModel = new EtapasModel();
        $this->acoesModel = new AcoesModel();
        $this->projetosModel = new ProjetosModel();
        $this->planosModel = new PlanosModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $solicitacoes = $this->solicitacoesModel->where('status', 'pendente')->findAll();

        foreach ($solicitacoes as &$solicitacao) {
            $dados = json_decode($solicitacao['dados_atuais'] ?? '{}', true);

            // Define o nome baseado no nível da solicitação
            switch ($solicitacao['nivel']) {
                case 'plano':
                    $solicitacao['nome'] = $dados['nome'] ?? 'Novo Plano';
                    break;
                case 'projeto':
                    $solicitacao['nome'] = $dados['nome'] ?? $dados['identificador'] ?? 'Novo Projeto';
                    break;
                case 'etapa':
                    $solicitacao['nome'] = $dados['nome'] ?? 'Nova Etapa';
                    break;
                case 'acao':
                    $solicitacao['nome'] = $dados['nome'] ?? 'Nova Ação';
                    break;
                default:
                    $solicitacao['nome'] = 'Nova Solicitação';
            }

            $solicitacao['solicitante'] = $this->getSolicitanteName($solicitacao['id_solicitante']);
        }

        $data = [
            'title' => 'Solicitações Pendentes',
            'solicitacoes' => $solicitacoes
        ];

        $this->content_data['content'] = view('sys/solicitacoes', $data);
        return view('layout', $this->content_data);
    }

    protected function getSolicitanteName($id)
    {
        if (empty($id)) {
            return 'Sistema';
        }

        try {
            $user = $this->userModel->findById($id);
            return $user ? $user->username : 'Usuário #' . $id;
        } catch (\Exception $e) {
            return 'Usuário #' . $id;
        }
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

        $dadosAtuais = json_decode($solicitacao['dados_atuais'], true) ?? [];
        $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?? [];

        return $this->response->setJSON([
            'success' => true,
            'data' => $solicitacao,
            'dados_atuais' => $dadosAtuais,
            'dados_alterados' => $dadosAlterados,
            'tipo' => $solicitacao['tipo'],
            'nivel' => $solicitacao['nivel']
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

        $status = ($acao === 'aceitar') ? 'aprovada' : 'rejeitada';

        $dadosAtualizacao = [
            'status' => $status,
            'data_avaliacao' => date('Y-m-d H:i:s'),
            'id_avaliador' => auth()->user()->id,
            'justificativa_avaliador' => $justificativaAvaliador
        ];

        if (!$this->solicitacoesModel->update($id, $dadosAtualizacao)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar solicitação',
                'errors' => $this->solicitacoesModel->errors()
            ]);
        }

        if ($acao === 'aceitar') {
            $resultado = $this->processarSolicitacao($solicitacao);

            if (!$resultado) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Erro ao processar alterações no banco de dados'
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Solicitação ' . ($acao === 'aceitar' ? 'aprovada' : 'recusada') . ' com sucesso',
            'status' => $status
        ]);
    }

    protected function processarSolicitacao($solicitacao)
    {
        $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?? [];

        switch ($solicitacao['nivel']) {
            case 'plano':
                return $this->processarRegistro($solicitacao, $this->planosModel, 'id_plano');
            case 'projeto':
                return $this->processarRegistro($solicitacao, $this->projetosModel, 'id_projeto');
            case 'etapa':
                return $this->processarRegistro($solicitacao, $this->etapasModel, 'id_etapa');
            case 'acao':
                return $this->processarRegistro($solicitacao, $this->acoesModel, 'id_acao');
            default:
                return false;
        }
    }

    protected function processarRegistro($solicitacao, $model, $idField)
    {
        $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?? [];
        $idRegistro = $solicitacao[$idField];

        switch ($solicitacao['tipo']) {
            case 'edição':
                $atualizacao = [];
                foreach ($dadosAlterados as $campo => $valores) {
                    $atualizacao[$campo] = is_array($valores) ? $valores['para'] : $valores;
                }
                return $model->update($idRegistro, $atualizacao);

            case 'inclusão':
                return $model->insert($dadosAlterados);

            case 'exclusão':
                return $model->delete($idRegistro);

            default:
                return false;
        }
    }
}
