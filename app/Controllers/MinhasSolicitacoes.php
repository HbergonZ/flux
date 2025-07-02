<?php

namespace App\Controllers;

use App\Models\SolicitacoesModel;
use App\Models\EtapasModel;
use App\Models\AcoesModel;
use App\Models\ProjetosModel;
use App\Models\PlanosModel;
use App\Models\EixosModel;
use CodeIgniter\Shield\Models\UserModel;

class MinhasSolicitacoes extends BaseController
{
    protected $solicitacoesModel;
    protected $etapasModel;
    protected $acoesModel;
    protected $projetosModel;
    protected $planosModel;
    protected $userModel;
    protected $eixosModel;

    public function __construct()
    {
        $this->solicitacoesModel = new SolicitacoesModel();
        $this->etapasModel = new EtapasModel();
        $this->acoesModel = new AcoesModel();
        $this->projetosModel = new ProjetosModel();
        $this->planosModel = new PlanosModel();
        $this->userModel = new UserModel();
        $this->eixosModel = new EixosModel();
    }

    public function index()
    {
        $userId = auth()->id();
        $solicitacoes = $this->solicitacoesModel->where('id_solicitante', $userId)->orderBy('data_solicitacao', 'DESC')->findAll();

        $eixos = [];
        foreach ($this->eixosModel->select('id, nome')->findAll() as $eixo) {
            $eixos[$eixo['id']] = $eixo['nome'];
        }

        foreach ($solicitacoes as &$solicitacao) {
            $dados = json_decode($solicitacao['dados_atuais'] ?? '{}', true);
            $dados_alterados = json_decode($solicitacao['dados_alterados'] ?? '{}', true);

            $solicitacao['total_evidencias'] = (!empty($dados['evidencias']) && is_array($dados['evidencias'])) ? count($dados['evidencias']) : 0;
            $solicitacao['total_indicadores'] = (!empty($dados['indicadores']) && is_array($dados['indicadores'])) ? count($dados['indicadores']) : 0;

            $solicitacao['total_evidencias_alteradas'] = (!empty($dados_alterados['evidencias']) && is_array($dados_alterados['evidencias'])) ? count($dados_alterados['evidencias']) : 0;
            $solicitacao['total_indicadores_alteradas'] = (!empty($dados_alterados['indicadores']) && is_array($dados_alterados['indicadores'])) ? count($dados_alterados['indicadores']) : 0;

            $solicitacao['nome'] = $this->getNomeSolicitacao($solicitacao, $dados);

            // Alterado para usar name em vez de username
            if (!empty($solicitacao['id_avaliador'])) {
                $avaliador = $this->userModel->find($solicitacao['id_avaliador']);
                $solicitacao['avaliador_username'] = $avaliador ? $avaliador->name : 'Sistema';
            } else {
                $solicitacao['avaliador_username'] = '-';
            }
        }
        unset($solicitacao);

        $data = [
            'title' => 'Minhas Solicitações',
            'solicitacoes' => $solicitacoes,
            'eixos' => $eixos
        ];

        return view('layout', ['content' => view('sys/minhas-solicitacoes', $data)]);
    }

    public function detalhes($id)
    {
        $userId = auth()->id();
        $solicitacao = $this->solicitacoesModel->where('id', $id)->where('id_solicitante', $userId)->first();

        if (!$solicitacao) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitação não encontrada ou não pertence ao usuário atual'
            ]);
        }

        $dadosAtuais = json_decode($solicitacao['dados_atuais'], true) ?? [];
        $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?? [];

        // Contagem de evidências e indicadores
        $dadosAtuais['total_evidencias'] = (!empty($dadosAtuais['evidencias']) && is_array($dadosAtuais['evidencias'])) ? count($dadosAtuais['evidencias']) : 0;
        $dadosAtuais['total_indicadores'] = (!empty($dadosAtuais['indicadores']) && is_array($dadosAtuais['indicadores'])) ? count($dadosAtuais['indicadores']) : 0;

        $dadosAlterados['total_evidencias'] = (!empty($dadosAlterados['evidencias']) && is_array($dadosAlterados['evidencias'])) ? count($dadosAlterados['evidencias']) : 0;
        $dadosAlterados['total_indicadores'] = (!empty($dadosAlterados['indicadores']) && is_array($dadosAlterados['indicadores'])) ? count($dadosAlterados['indicadores']) : 0;

        // Carregar nomes dos responsáveis
        if (!empty($dadosAtuais['responsaveis'])) {
            $responsaveisIds = $dadosAtuais['responsaveis'];
            if (!is_array($responsaveisIds)) $responsaveisIds = [$responsaveisIds];
            $dadosAtuais['responsaveis_nomes'] = array_column($this->getUserNamesByIds($responsaveisIds), 'name');
        } else if (!empty($dadosAtuais['responsavel'])) {
            $dadosAtuais['responsaveis_nomes'] = [$dadosAtuais['responsavel']];
        } else {
            $dadosAtuais['responsaveis_nomes'] = [];
        }

        // Carregar nomes dos responsáveis nas alterações
        if (!empty($dadosAlterados['responsaveis'])) {
            $addIds = isset($dadosAlterados['responsaveis']['adicionar']) ? $dadosAlterados['responsaveis']['adicionar'] : [];
            $remIds = isset($dadosAlterados['responsaveis']['remover']) ? $dadosAlterados['responsaveis']['remover'] : [];
            $addIds = is_array($addIds) ? $addIds : [];
            $remIds = is_array($remIds) ? $remIds : [];
            $dadosAlterados['responsaveis']['adicionar_nomes'] = array_column($this->getUserNamesByIds($addIds), 'name');
            $dadosAlterados['responsaveis']['remover_nomes'] = array_column($this->getUserNamesByIds($remIds), 'name');
        }

        // Processar evidências e indicadores
        if (!empty($dadosAlterados['evidencias'])) {
            $dadosAlterados['evidencias'] = $this->processarEvidenciasParaVisualizacao($dadosAlterados['evidencias'], $solicitacao['id_acao'] ?? null);
        }

        if (!empty($dadosAlterados['indicadores'])) {
            $dadosAlterados['indicadores'] = $this->processarEvidenciasParaVisualizacao($dadosAlterados['indicadores'], $solicitacao['id_acao'] ?? null);
        }

        // Buscar nome do avaliador
        $avaliador = null;
        if (!empty($solicitacao['id_avaliador'])) {
            $avaliador = $this->userModel->find($solicitacao['id_avaliador']);
        }

        // Buscar nome do projeto e do plano (se houver)
        $nomeProjeto = null;
        $nomePlano = null;
        $idProjeto = $solicitacao['id_projeto'] ?? $dadosAtuais['id_projeto'] ?? $dadosAlterados['id_projeto'] ?? null;
        $idPlano = $solicitacao['id_plano'] ?? $dadosAtuais['id_plano'] ?? $dadosAlterados['id_plano'] ?? null;

        if ($idProjeto) {
            $proj = $this->projetosModel->find($idProjeto);
            $nomeProjeto = $proj ? ($proj['nome'] ?? ($proj->nome ?? null)) : null;
        }

        if ($idPlano) {
            $plano = $this->planosModel->find($idPlano);
            $nomePlano = $plano ? ($plano['nome'] ?? ($plano->nome ?? null)) : null;
        }

        $data = [
            'id' => $solicitacao['id'],
            'nivel' => $solicitacao['nivel'],
            'tipo' => $solicitacao['tipo'],
            'status' => $solicitacao['status'],
            'solicitante' => $this->getSolicitanteName($solicitacao['id_solicitante']),
            'data_solicitacao' => $solicitacao['data_solicitacao'],
            'justificativa_solicitante' => $solicitacao['justificativa_solicitante'] ?? '',
            'dados_atuais' => $dadosAtuais,
            'dados_alterados' => $dadosAlterados,
            'nome_projeto' => $nomeProjeto,
            'nome_plano' => $nomePlano,
            'avaliador' => $avaliador ? $avaliador->name : null, // Alterado de username para name
            'data_avaliacao' => $solicitacao['data_avaliacao'] ?? null,
            'justificativa_avaliador' => $solicitacao['justificativa_avaliador'] ?? null
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    protected function getNomeSolicitacao($solicitacao, $dados)
    {
        switch ($solicitacao['nivel']) {
            case 'plano':
                return $dados['nome'] ?? 'Novo Plano';
            case 'projeto':
                return $dados['nome'] ?? $dados['identificador'] ?? 'Novo Projeto';
            case 'etapa':
                return $dados['nome'] ?? 'Nova Etapa';
            case 'acao':
                return $dados['nome'] ?? 'Nova Ação';
            default:
                return 'Nova Solicitação';
        }
    }

    protected function getSolicitanteName($id)
    {
        if (empty($id)) return 'Sistema';
        try {
            $user = $this->userModel->findById($id);
            return $user && !empty($user->name) ? $user->name : 'Usuário #' . $id;
        } catch (\Exception $e) {
            return 'Usuário #' . $id;
        }
    }

    protected function getUserNamesByIds($ids)
    {
        if (empty($ids) || !is_array($ids)) return [];
        $ids = array_unique(array_filter(array_map('intval', $ids)));
        if (empty($ids)) return [];
        $users = $this->userModel->whereIn('id', $ids)->findAll();
        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => is_object($user) ? $user->id : $user['id'],
                'name' => is_object($user) ? $user->name : $user['name'],
            ];
        }
        return $result;
    }

    protected function processarEvidenciasParaVisualizacao($evidenciasSolicitadas, $idRegistro)
    {
        $resultado = ['adicionar' => [], 'remover' => []];
        if (empty($evidenciasSolicitadas) || !is_array($evidenciasSolicitadas)) {
            return $resultado;
        }

        if (isset($evidenciasSolicitadas['adicionar'])) {
            $resultado['adicionar'] = $evidenciasSolicitadas['adicionar'];
        } elseif (isset($evidenciasSolicitadas['evidencias']['adicionar'])) {
            $resultado['adicionar'] = $evidenciasSolicitadas['evidencias']['adicionar'];
        }

        if (isset($evidenciasSolicitadas['remover'])) {
            $resultado['remover'] = $evidenciasSolicitadas['remover'];
        } elseif (isset($evidenciasSolicitadas['evidencias']['remover'])) {
            $resultado['remover'] = $evidenciasSolicitadas['evidencias']['remover'];
        }

        return $resultado;
    }
}
