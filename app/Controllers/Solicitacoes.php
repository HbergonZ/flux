<?php

namespace App\Controllers;

use App\Models\SolicitacoesModel;
use App\Models\EtapasModel;
use App\Models\AcoesModel;
use App\Models\MetasModel;
use App\Models\PlanosModel;

class Solicitacoes extends BaseController
{
    protected $solicitacoesModel;
    protected $etapasModel;
    protected $acoesModel;
    protected $metasModel;
    protected $planosModel;

    public function __construct()
    {
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->etapasModel = new EtapasModel();
        $this->acoesModel = new AcoesModel();
        $this->metasModel = new MetasModel();
        $this->planosModel = new PlanosModel();
    }

    public function index()
    {
        $solicitacoes = $this->solicitacoesModel->where('status', 'pendente')->findAll();

        foreach ($solicitacoes as &$solicitacao) {
            $dados = json_decode($solicitacao['dados_atuais'] ?? '{}', true);
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

        $dadosAtuais = json_decode($solicitacao['dados_atuais'], true);

        // Para exclusão, mostramos os dados atuais que serão removidos
        return $this->response->setJSON([
            'success' => true,
            'data' => $solicitacao,
            'dados_atuais' => $dadosAtuais,
            'dados_alterados' => [], // Não precisa de dados alterados para exclusão
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
            $resultado = false;
            switch ($solicitacao['nivel']) {
                case 'etapa':
                    $resultado = $this->processarEtapa($solicitacao);
                    break;
                case 'acao':
                    $resultado = $this->processarAcao($solicitacao);
                    break;
                case 'meta':
                    $resultado = $this->processarMeta($solicitacao);
                    break;
                case 'plano':
                    $resultado = $this->processarPlano($solicitacao);
                    break;
            }

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

    protected function processarEtapa($solicitacao)
    {
        $dados = json_decode($solicitacao['dados_alterados'], true);

        if ($solicitacao['tipo'] == 'edição') {
            $atualizacao = [];
            foreach ($dados as $campo => $valores) {
                $atualizacao[$campo] = $valores['para'];
            }
            return $this->etapasModel->update($solicitacao['id_etapa'], $atualizacao);
        }

        if ($solicitacao['tipo'] == 'inclusão') {
            return $this->etapasModel->insert($dados);
        }

        if ($solicitacao['tipo'] == 'exclusão') {
            return $this->etapasModel->delete($solicitacao['id_etapa']);
        }

        return false;
    }

    protected function processarAcao($solicitacao)
    {
        $dados = json_decode($solicitacao['dados_alterados'], true);

        if ($solicitacao['tipo'] == 'edição') {
            $atualizacao = [];
            foreach ($dados as $campo => $valores) {
                $atualizacao[$campo] = $valores['para'];
            }
            return $this->acoesModel->update($solicitacao['id_acao'], $atualizacao);
        }

        if ($solicitacao['tipo'] == 'inclusão') {
            return $this->acoesModel->insert($dados);
        }

        if ($solicitacao['tipo'] == 'exclusão') {
            return $this->acoesModel->delete($solicitacao['id_acao']);
        }

        return false;
    }

    protected function processarMeta($solicitacao)
    {
        $dados = json_decode($solicitacao['dados_alterados'], true);

        if ($solicitacao['tipo'] == 'edição') {
            $atualizacao = [];
            foreach ($dados as $campo => $valores) {
                $atualizacao[$campo] = $valores['para'];
            }
            return $this->metasModel->update($solicitacao['id_meta'], $atualizacao);
        }

        if ($solicitacao['tipo'] == 'inclusão') {
            return $this->metasModel->insert($dados);
        }

        if ($solicitacao['tipo'] == 'exclusão') {
            return $this->metasModel->delete($solicitacao['id_meta']);
        }

        return false;
    }

    protected function processarPlano($solicitacao)
    {
        $dados = json_decode($solicitacao['dados_alterados'], true);

        if ($solicitacao['tipo'] == 'edição') {
            $atualizacao = [];
            foreach ($dados as $campo => $valores) {
                $atualizacao[$campo] = $valores['para'];
            }
            return $this->planosModel->update($solicitacao['id_plano'], $atualizacao);
        }

        if ($solicitacao['tipo'] == 'inclusão') {
            return $this->planosModel->insert($dados);
        }

        if ($solicitacao['tipo'] == 'exclusão') {
            return $this->planosModel->delete($solicitacao['id_plano']);
        }

        return false;
    }
}
