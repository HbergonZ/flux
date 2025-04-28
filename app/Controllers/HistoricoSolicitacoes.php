<?php

namespace App\Controllers;

use App\Models\SolicitacaoEdicaoModel;
use CodeIgniter\API\ResponseTrait;

class HistoricoSolicitacoes extends BaseController
{
    use ResponseTrait;

    protected $solicitacaoModel;

    public function __construct()
    {
        $this->solicitacaoModel = new SolicitacaoEdicaoModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('solicitacoes_edicao se');
        $builder->select('se.*, p.nome as nome_projeto, e.etapa, e.acao');
        $builder->join('projetos p', 'p.id = se.id_projeto');
        $builder->join('etapas e', 'e.id_etapa = se.id_etapa AND e.id_acao = se.id_acao');
        $builder->whereIn('se.status', ['aprovada', 'rejeitada']);
        $builder->orderBy('se.data_avaliacao', 'DESC');

        $solicitacoes = $builder->get()->getResultArray();

        foreach ($solicitacoes as &$solicitacao) {
            if (empty($solicitacao['solicitante'])) {
                $solicitacao['solicitante'] = 'Anônimo';
            }
            $solicitacao['processado_por_nome'] = 'Sistema';
            $solicitacao['data_formatada'] = date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao']));
            $solicitacao['data_processamento_formatada'] = isset($solicitacao['data_avaliacao'])
                ? date('d/m/Y H:i', strtotime($solicitacao['data_avaliacao']))
                : 'Não avaliada';
        }

        $data = [
            'solicitacoes' => $solicitacoes,
            'tituloPagina' => 'Histórico de Solicitações de Edição'
        ];

        $this->content_data['content'] = view('sys/historico-solicitacoes', $data);
        return view('layout', $this->content_data);
    }

    public function detalhes($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->failForbidden('Acesso permitido apenas via AJAX', 403);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('solicitacoes_edicao se');
            $builder->select('se.*, p.nome as nome_projeto, e.etapa as nome_etapa, e.acao as nome_acao');
            $builder->join('projetos p', 'p.id = se.id_projeto');
            $builder->join('etapas e', 'e.id_etapa = se.id_etapa AND e.id_acao = se.id_acao');
            $builder->where('se.id', $id);
            $solicitacao = $builder->get()->getRowArray();

            if (!$solicitacao) {
                return $this->failNotFound('Solicitação não encontrada');
            }

            // Dados originais (antes da solicitação)
            $dadosOriginais = json_decode($solicitacao['dados_atuais'], true) ?? [];

            // Dados para exibição (formatados)
            $dadosOriginaisParaExibicao = [
                'etapa' => $solicitacao['nome_etapa'] ?? 'N/A',
                'acao' => $solicitacao['nome_acao'] ?? 'N/A',
                'coordenacao' => $dadosOriginais['coordenacao'] ?? 'N/A',
                'responsavel' => $dadosOriginais['responsavel'] ?? 'N/A',
                'status' => $dadosOriginais['status'] ?? 'N/A',
                'tempo_estimado_dias' => $dadosOriginais['tempo_estimado_dias'] ?? 'N/A',
                'data_inicio' => isset($dadosOriginais['data_inicio']) && !empty($dadosOriginais['data_inicio'])
                    ? (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dadosOriginais['data_inicio'])
                        ? date('d/m/Y', strtotime($dadosOriginais['data_inicio']))
                        : $dadosOriginais['data_inicio'])
                    : 'N/A',
                'data_fim' => isset($dadosOriginais['data_fim']) && !empty($dadosOriginais['data_fim'])
                    ? (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dadosOriginais['data_fim'])
                        ? date('d/m/Y', strtotime($dadosOriginais['data_fim']))
                        : $dadosOriginais['data_fim'])
                    : 'N/A'
            ];

            // Filtra apenas campos alterados
            $dadosAlteradosOrig = json_decode($solicitacao['dados_alterados'], true) ?? [];
            $dadosAlteradosFiltrados = [];

            foreach ($dadosAlteradosOrig as $campo => $valorProposto) {
                $valorOriginal = $dadosOriginais[$campo] ?? null;

                if (in_array($campo, ['data_inicio', 'data_fim'])) {
                    $valorProposto = $this->normalizarDataParaComparacao($valorProposto);
                    $valorOriginal = $this->normalizarDataParaComparacao($valorOriginal);
                }

                if ($this->valoresDiferentes($valorOriginal, $valorProposto)) {
                    $dadosAlteradosFiltrados[$campo] = $dadosAlteradosOrig[$campo];
                }
            }

            // Formatar datas para exibição nos dados alterados
            foreach ($dadosAlteradosFiltrados as $campo => &$valor) {
                if (in_array($campo, ['data_inicio', 'data_fim']) && !empty($valor)) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                        $valor = date('d/m/Y', strtotime($valor));
                    }
                }
            }

            $html = $this->gerarHtmlDetalhes(
                $solicitacao,
                $dadosOriginaisParaExibicao,
                $dadosAlteradosFiltrados
            );

            return $this->response->setContentType('text/html')->setBody($html);
        } catch (\Exception $e) {
            log_message('error', 'Erro em HistoricoSolicitacoes::detalhes: ' . $e->getMessage());
            return $this->failServerError('Erro ao processar a solicitação');
        }
    }

    protected function normalizarDataParaComparacao($data)
    {
        if (empty($data)) return null;

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
            $date = \DateTime::createFromFormat('d/m/Y', $data);
            return $date ? $date->format('Y-m-d') : null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return $data;
        }

        return null;
    }

    protected function valoresDiferentes($valor1, $valor2)
    {
        if ($valor1 === null && $valor2 === null) return false;
        if ($valor1 === null || $valor2 === null) return true;
        return (string)$valor1 !== (string)$valor2;
    }

    protected function gerarHtmlDetalhes($solicitacao, $dadosOriginais, $dadosAlterados)
    {
        $html = '<div class="container-fluid">';

        // Cabeçalho
        $html .= '<div class="row mb-4">
        <div class="col-md-6">
            <h5><strong>Projeto:</strong> ' . htmlspecialchars($solicitacao['nome_projeto'] ?? 'N/A') . '</h5>
            <p><strong>Solicitante:</strong> ' . htmlspecialchars($solicitacao['solicitante'] ?? 'Anônimo') . '</p>
        </div>
        <div class="col-md-6 text-end">
            <p><strong>Data da Solicitação:</strong> ' . date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) . '</p>
            <p><strong>Data de Processamento:</strong> ' . (isset($solicitacao['data_avaliacao']) ? date('d/m/Y H:i', strtotime($solicitacao['data_avaliacao'])) : 'N/A') . '</p>
        </div>
    </div>';

        // Mapeamento dos nomes dos campos
        $nomesCampos = [
            'etapa' => 'Etapa',
            'acao' => 'Ação',
            'coordenacao' => 'Coordenação',
            'responsavel' => 'Responsável',
            'tempo_estimado_dias' => 'Tempo Estimado (dias)',
            'data_inicio' => 'Data Início',
            'data_fim' => 'Data Fim',
            'status' => 'Status'
        ];

        // Tabelas de comparação
        $html .= '<div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Dados Originais</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <tbody>';

        foreach ($dadosOriginais as $campo => $valor) {
            $nomeCampo = $nomesCampos[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
            $html .= '<tr>
            <th class="w-50">' . $nomeCampo . '</th>
            <td>' . htmlspecialchars($valor) . '</td>
        </tr>';
        }

        $html .= '</tbody></table></div></div></div>';

        $html .= '<div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header ' . ($solicitacao['status'] == 'aprovada' ? 'bg-success' : 'bg-danger') . ' text-white">
                    <h6 class="m-0 font-weight-bold">Alterações Propostas</h6>
                </div>
                <div class="card-body">';

        if (empty($dadosAlterados)) {
            $html .= '<div class="alert alert-info">Nenhuma alteração foi proposta</div>';
        } else {
            $html .= '<table class="table table-bordered table-sm">
            <tbody>';

            foreach ($dadosAlterados as $campo => $valor) {
                $valorOriginal = $dadosOriginais[$campo] ?? 'N/A';
                $nomeCampo = $nomesCampos[$campo] ?? ucfirst(str_replace('_', ' ', $campo));

                $html .= '<tr>
                <th class="w-50">' . $nomeCampo . '</th>
                <td>
                    <div class="text-danger"><del>' . htmlspecialchars($valorOriginal) . '</del></div>
                    <div class="text-success">' . htmlspecialchars($valor ?? 'N/A') . '</div>
                </td>
            </tr>';
            }

            $html .= '</tbody></table>';
        }

        $html .= '</div></div></div></div>';

        // Justificativa
        $html .= '<div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Justificativa da Alteração</h6>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded">' . nl2br(htmlspecialchars($solicitacao['justificativa'] ?? 'Nenhuma justificativa fornecida')) . '</div>
                </div>
            </div>
        </div>
    </div>';

        // Informações de processamento
        $html .= '<div class="row mt-4">
        <div class="col-md-12">
            <div class="alert alert-' . ($solicitacao['status'] == 'aprovada' ? 'success' : 'danger') . '">
                <h5 class="alert-heading">Status: ' . ucfirst($solicitacao['status']) . '</h5>
                <p><strong>Processado por:</strong> ' . htmlspecialchars($solicitacao['processado_por_nome'] ?? 'Sistema') . '</p>
                <p><strong>Data de processamento:</strong> ' . (isset($solicitacao['data_avaliacao']) ? date('d/m/Y H:i', strtotime($solicitacao['data_avaliacao'])) : 'N/A') . '</p>
            </div>
        </div>
    </div>';

        $html .= '</div>';
        return $html;
    }
}
