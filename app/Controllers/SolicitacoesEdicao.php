<?php

namespace App\Controllers;

use App\Models\SolicitacaoEdicaoModel;
use App\Models\EtapaModel;
use CodeIgniter\API\ResponseTrait;

class SolicitacoesEdicao extends BaseController
{
    use ResponseTrait;

    protected $solicitacaoModel;
    protected $etapaModel;

    public function __construct()
    {
        $this->solicitacaoModel = new SolicitacaoEdicaoModel();
        $this->etapaModel = new EtapaModel();
    }

    public function index()
    {

        $db = \Config\Database::connect();
        $builder = $db->table('solicitacoes_edicao se');
        $builder->select('se.*, p.nome as nome_projeto, e.etapa, e.acao');
        $builder->join('projetos p', 'p.id = se.id_projeto');
        $builder->join('etapas e', 'e.id_etapa = se.id_etapa AND e.id_acao = se.id_acao');
        $builder->where('se.status', 'pendente');
        $builder->orderBy('se.data_solicitacao', 'DESC');

        $solicitacoes = $builder->get()->getResultArray();

        foreach ($solicitacoes as &$solicitacao) {
            if (empty($solicitacao['solicitante'])) {
                $solicitacao['solicitante'] = 'Anônimo';
            }
            $solicitacao['data_formatada'] = date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao']));
        }

        $data = [
            'solicitacoes' => $solicitacoes,
            'tituloPagina' => 'Solicitações de Edição Pendentes'
        ];

        $this->content_data['content'] = view('sys/solicitacoes-edicao', $data);
        return view('layout', $this->content_data);
    }

    public function detalhes($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->failForbidden('Acesso permitido apenas via AJAX');
        }

        try {
            // Busca a solicitação com JOIN para pegar o nome do projeto
            $db = \Config\Database::connect();
            $builder = $db->table('solicitacoes_edicao se');
            $builder->select('se.*, p.nome as nome_projeto');
            $builder->join('projetos p', 'p.id = se.id_projeto');
            $builder->where('se.id', $id);
            $solicitacao = $builder->get()->getRowArray();

            if (!$solicitacao) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Solicitação não encontrada'
                ]);
            }

            // Decodifica os dados atuais diretamente da solicitação
            $dadosAtuaisParaComparacao = json_decode($solicitacao['dados_atuais'], true) ?? [];

            // Dados para exibição (formatados)
            $dadosAtuaisParaExibicao = [
                'etapa' => $dadosAtuaisParaComparacao['etapa'] ?? '',
                'acao' => $dadosAtuaisParaComparacao['acao'] ?? '',
                'coordenacao' => $dadosAtuaisParaComparacao['coordenacao'] ?? '',
                'responsavel' => $dadosAtuaisParaComparacao['responsavel'] ?? '',
                'status' => $dadosAtuaisParaComparacao['status'] ?? '',
                'tempo_estimado_dias' => $dadosAtuaisParaComparacao['tempo_estimado_dias'] ?? '',
                'data_inicio' => isset($dadosAtuaisParaComparacao['data_inicio']) && !empty($dadosAtuaisParaComparacao['data_inicio'])
                    ? date('d/m/Y', strtotime($dadosAtuaisParaComparacao['data_inicio']))
                    : '',
                'data_fim' => isset($dadosAtuaisParaComparacao['data_fim']) && !empty($dadosAtuaisParaComparacao['data_fim'])
                    ? date('d/m/Y', strtotime($dadosAtuaisParaComparacao['data_fim']))
                    : ''
            ];

            // Decodifica os dados alterados
            $dadosAlteradosOrig = json_decode($solicitacao['dados_alterados'], true) ?? [];
            $dadosAlteradosFiltrados = [];

            foreach ($dadosAlteradosOrig as $campo => $valorProposto) {
                $valorAtual = $dadosAtuaisParaComparacao[$campo] ?? null;

                if (in_array($campo, ['data_inicio', 'data_fim'])) {
                    $valorProposto = $this->normalizarDataParaComparacao($valorProposto);
                    $valorAtual = $this->normalizarDataParaComparacao($valorAtual);
                }

                if ($this->valoresDiferentes($valorAtual, $valorProposto)) {
                    $dadosAlteradosFiltrados[$campo] = $dadosAlteradosOrig[$campo];
                }
            }

            $html = $this->gerarHtmlDetalhes(
                $solicitacao,
                $dadosAtuaisParaExibicao,
                $dadosAlteradosFiltrados
            );

            return $this->respond([
                'status' => 'success',
                'html' => $html
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro em SolicitacoesEdicao::detalhes: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Erro ao processar a solicitação'
            ]);
        }
    }

    public function processar($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->failForbidden('Acesso permitido apenas via AJAX');
        }

        try {
            $acao = $this->request->getPost('acao');

            if (!in_array($acao, ['aprovada', 'rejeitada'])) {
                return $this->fail('Ação inválida', 400);
            }

            $solicitacao = $this->solicitacaoModel->find($id);
            if (!$solicitacao) {
                return $this->failNotFound('Solicitação não encontrada');
            }

            if ($acao === 'aprovada') {
                $dadosAlterados = json_decode($solicitacao['dados_alterados'], true) ?? [];

                // Verifica se existem dados para atualizar
                if (empty($dadosAlterados)) {
                    return $this->fail('Nenhum dado para atualizar', 400);
                }

                // Debug: Log dos dados que serão atualizados
                log_message('debug', 'Dados a serem atualizados: ' . print_r($dadosAlterados, true));

                // Atualiza a etapa
                $updateResult = $this->etapaModel
                    ->where('id_etapa', $solicitacao['id_etapa'])
                    ->where('id_acao', $solicitacao['id_acao'])  // Observe o tipo de dado aqui
                    ->set($dadosAlterados)
                    ->update();

                // Verifica se a atualização foi bem-sucedida
                if (!$updateResult) {
                    $error = $this->etapaModel->errors();
                    log_message('error', 'Erro ao atualizar etapa: ' . print_r($error, true));
                    return $this->fail('Falha ao atualizar a etapa', 500);
                }
            }

            // Atualiza o status da solicitação
            $this->solicitacaoModel->protect(false)->update($id, [
                'status' => $acao,
                'data_avaliacao' => date('Y-m-d H:i:s')
            ]);

            return $this->respond([
                'success' => true,
                'message' => 'Solicitação processada com sucesso',
                'data' => [
                    'id_etapa' => $solicitacao['id_etapa'],
                    'id_acao' => $solicitacao['id_acao'],
                    'dados_alterados' => $dadosAlterados ?? null
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro em SolicitacoesEdicao::processar: ' . $e->getMessage());
            return $this->failServerError('Erro ao processar a solicitação: ' . $e->getMessage());
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

    protected function gerarHtmlDetalhes($solicitacao, $dadosAtuais, $dadosAlterados)
    {
        $html = '<div class="container-fluid">';
        log_message('debug', 'HTML gerado: ' . substr($html, 0, 200) . '...');

        // Cabeçalho
        $html .= '<div class="row mb-4">
        <div class="col-md-6">
            <h5><strong>Projeto:</strong> ' . htmlspecialchars($solicitacao['nome_projeto'] ?? '') . '</h5>
            <p><strong>Solicitante:</strong> ' . htmlspecialchars($solicitacao['solicitante'] ?? 'Anônimo') . '</p>
        </div>
        <div class="col-md-6 text-end">
            <p><strong>Data da Solicitação:</strong> ' . date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) . '</p>
        </div>
    </div>';

        // Mapeamento dos nomes dos campos para exibição
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
                    <h6 class="m-0 font-weight-bold">Dados Atuais</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <tbody>';

        foreach ($dadosAtuais as $campo => $valor) {
            $nomeCampo = $nomesCampos[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
            $html .= '<tr>
            <th class="w-50">' . $nomeCampo . '</th>
            <td>' . htmlspecialchars($valor) . '</td>
        </tr>';
        }

        $html .= '</tbody></table></div></div></div>';

        $html .= '<div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header ' . ($solicitacao['status'] == 'pendente' ? 'bg-warning' : 'bg-success') . ' text-white">
                    <h6 class="m-0 font-weight-bold">Alterações Propostas</h6>
                </div>
                <div class="card-body">';

        if (empty($dadosAlterados)) {
            $html .= '<div class="alert alert-info">Nenhuma alteração foi proposta</div>';
        } else {
            $html .= '<table class="table table-bordered table-sm">
                    <tbody>';

            foreach ($dadosAlterados as $campo => $valor) {
                $valorAtual = $dadosAtuais[$campo] ?? '';
                $valorExibicao = $valor;
                $nomeCampo = $nomesCampos[$campo] ?? ucfirst(str_replace('_', ' ', $campo));

                if (in_array($campo, ['data_inicio', 'data_fim']) && !empty($valor)) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                        $valorExibicao = date('d/m/Y', strtotime($valor));
                    }
                }

                $html .= '<tr>
                <th class="w-50">' . $nomeCampo . '</th>
                <td>
                    <div class="text-danger"><del>' . htmlspecialchars($valorAtual) . '</del></div>
                    <div class="text-success">' . htmlspecialchars($valorExibicao ?? '') . '</div>
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

        $html .= '</div>';
        return $html;
    }
}
