<?php

namespace App\Models;

use CodeIgniter\Model;

class VisaoGeralModel extends Model
{
    protected $table = 'etapas'; // Tabela base
    protected $primaryKey = 'id_etapa';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = ['etapa', 'responsavel', 'equipe', 'tempo_estimado_dias', 'data_inicio', 'data_fim', 'status', 'id_acao', 'ordem', 'id_meta'];

    public function getVisaoGeral(array $filtros = [])
    {
        $builder = $this->builder();

        // Seleciona todos os campos necessários com joins
        $builder->select('etapas.*,
                        acoes.id as acao_id, acoes.identificador, acoes.acao, acoes.priorizacao_gab,
                        acoes.id_eixo, acoes.id_plano, acoes.responsaveis as responsaveis_acao,
                        metas.id as meta_id, metas.nome as meta_nome,
                        eixos.nome as nome_eixo,
                        planos.nome as plano_nome, planos.sigla as sigla_plano')
            ->join('acoes', 'acoes.id = etapas.id_acao')
            ->join('metas', 'metas.id = etapas.id_meta', 'left')
            ->join('eixos', 'eixos.id = acoes.id_eixo', 'left')
            ->join('planos', 'planos.id = acoes.id_plano', 'left')
            ->orderBy('acoes.acao, etapas.ordem', 'ASC');

        // Aplica filtros
        $this->aplicarFiltros($builder, $filtros);

        $result = $builder->get()->getResultArray();

        // Formata os dados
        return array_map(function ($item) {
            return [
                'plano' => $item['plano_nome'] ?? '',
                'sigla_plano' => $item['sigla_plano'] ?? '',
                'acao_id' => $item['acao_id'] ?? null,
                'acao' => $item['acao'] ?? '',
                'nome_eixo' => $item['nome_eixo'] ?? '',
                'responsaveis_acao' => $item['responsaveis_acao'] ?? '',
                'meta_id' => $item['meta_id'] ?? null,
                'meta' => $item['meta_nome'] ?? '',
                'id_etapa' => $item['id_etapa'],
                'etapa' => $item['etapa'],
                'ordem' => $item['ordem'],
                'responsavel' => $item['responsavel'],
                'equipe' => $item['equipe'],
                'tempo_estimado_dias' => $item['tempo_estimado_dias'],
                'data_inicio' => $item['data_inicio'],
                'data_fim' => $item['data_fim'],
                'status' => $item['status'],
                'priorizacao_gab' => $item['priorizacao_gab'] ?? 0,
                'data_inicio_formatada' => !empty($item['data_inicio']) ? date('d/m/Y', strtotime($item['data_inicio'])) : '',
                'data_fim_formatada' => !empty($item['data_fim']) ? date('d/m/Y', strtotime($item['data_fim'])) : ''
            ];
        }, $result);
    }

    protected function aplicarFiltros(&$builder, array $filtros)
    {
        // Priorização
        if (isset($filtros['priorizacao_gab']) && $filtros['priorizacao_gab'] !== '') {
            $builder->where('acoes.priorizacao_gab', $filtros['priorizacao_gab']);
        }

        // Plano
        if (!empty($filtros['plano'])) {
            $builder->where('planos.nome', $filtros['plano']);
        }

        // Ação
        if (!empty($filtros['acao'])) {
            $builder->where('acoes.acao', $filtros['acao']);
        }

        // Meta
        if (!empty($filtros['meta'])) {
            $builder->where('metas.nome', $filtros['meta']);
        }

        // Etapa
        if (!empty($filtros['etapa'])) {
            $builder->where('etapas.etapa', $filtros['etapa']);
        }

        // Responsável
        if (!empty($filtros['responsavel'])) {
            $builder->where('etapas.responsavel', $filtros['responsavel']);
        }

        // Equipe
        if (!empty($filtros['equipe'])) {
            $builder->like('etapas.equipe', $filtros['equipe']);
        }

        // Status
        if (!empty($filtros['status'])) {
            $builder->where('etapas.status', $filtros['status']);
        }

        // Data início
        if (!empty($filtros['data_inicio'])) {
            $builder->where('etapas.data_inicio >=', $filtros['data_inicio']);
        }

        // Data fim
        if (!empty($filtros['data_fim'])) {
            $builder->where('etapas.data_fim <=', $filtros['data_fim']);
        }
    }

    public function getFiltrosDistinct()
    {
        $builder = $this->builder();

        // Consulta para planos
        $planos = $this->builder()
            ->select('planos.nome as plano')
            ->join('acoes', 'acoes.id = etapas.id_acao')
            ->join('planos', 'planos.id = acoes.id_plano', 'left')
            ->groupBy('planos.nome')
            ->orderBy('planos.nome')
            ->get()
            ->getResultArray();

        // Consulta para ações
        $acoes = $this->builder()
            ->select('acoes.acao')
            ->join('acoes', 'acoes.id = etapas.id_acao')
            ->groupBy('acoes.acao')
            ->orderBy('acoes.acao')
            ->get()
            ->getResultArray();

        // Consulta para metas
        $metas = $this->builder()
            ->select('metas.nome as meta')
            ->join('metas', 'metas.id = etapas.id_meta', 'left')
            ->groupBy('metas.nome')
            ->orderBy('metas.nome')
            ->get()
            ->getResultArray();

        // Consulta para etapas
        $etapas = $this->builder()
            ->select('etapas.etapa')
            ->groupBy('etapas.etapa')
            ->orderBy('etapas.etapa')
            ->get()
            ->getResultArray();

        // Consulta para responsáveis
        $responsaveis = $this->builder()
            ->select('etapas.responsavel')
            ->groupBy('etapas.responsavel')
            ->orderBy('etapas.responsavel')
            ->get()
            ->getResultArray();

        // Consulta para status
        $status = $this->builder()
            ->select('etapas.status')
            ->groupBy('etapas.status')
            ->orderBy('etapas.status')
            ->get()
            ->getResultArray();

        // Processa os resultados para obter valores distintos
        $filtros = [
            'planos' => array_values(array_unique(array_column($planos, 'plano'))),
            'acoes' => array_values(array_unique(array_column($acoes, 'acao'))),
            'metas' => array_values(array_unique(array_column($metas, 'meta'))),
            'etapas' => array_values(array_unique(array_column($etapas, 'etapa'))),
            'responsavel' => array_values(array_unique(array_column($responsaveis, 'responsavel'))),
            'status' => array_values(array_unique(array_column($status, 'status')))
        ];

        // Converte para o formato esperado (array de arrays associativos)
        $filtros['planos'] = array_map(function ($item) {
            return ['plano' => $item];
        }, $filtros['planos']);

        $filtros['acoes'] = array_map(function ($item) {
            return ['acao' => $item];
        }, $filtros['acoes']);

        $filtros['metas'] = array_map(function ($item) {
            return ['meta' => $item];
        }, $filtros['metas']);

        $filtros['etapas'] = array_map(function ($item) {
            return ['etapa' => $item];
        }, $filtros['etapas']);

        $filtros['responsavel'] = array_map(function ($item) {
            return ['responsavel' => $item];
        }, $filtros['responsavel']);

        $filtros['status'] = array_map(function ($item) {
            return ['status' => $item];
        }, $filtros['status']);

        return $filtros;
    }
}
