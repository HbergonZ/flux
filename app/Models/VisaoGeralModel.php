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
    protected $allowedFields = ['etapa', 'responsavel', 'equipe', 'tempo_estimado_dias', 'data_inicio', 'data_fim', 'status', 'id_acao', 'id_meta'];

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
            ->join('planos', 'planos.id = acoes.id_plano', 'left');

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
        $builder->select('planos.nome as plano,
                        acoes.acao,
                        metas.nome as meta,
                        etapas.etapa,
                        etapas.responsavel,
                        etapas.status')
            ->join('acoes', 'acoes.id = etapas.id_acao')
            ->join('metas', 'metas.id = etapas.id_meta', 'left')
            ->join('planos', 'planos.id = acoes.id_plano', 'left')
            ->groupBy('planos.nome, acoes.acao, metas.nome, etapas.etapa, etapas.responsavel, etapas.status')
            ->orderBy('planos.nome, acoes.acao, metas.nome, etapas.etapa');

        $result = $builder->get()->getResultArray();

        // Processa os resultados para obter valores distintos
        $filtros = [
            'planos' => [],
            'acoes' => [],
            'metas' => [],
            'etapas' => [],
            'responsavel' => [],
            'status' => []
        ];

        foreach ($result as $row) {
            if (!empty($row['plano']) && !in_array(['plano' => $row['plano']], $filtros['planos'])) {
                $filtros['planos'][] = ['plano' => $row['plano']];
            }

            if (!empty($row['acao']) && !in_array(['acao' => $row['acao']], $filtros['acoes'])) {
                $filtros['acoes'][] = ['acao' => $row['acao']];
            }

            if (!empty($row['meta']) && !in_array(['meta' => $row['meta']], $filtros['metas'])) {
                $filtros['metas'][] = ['meta' => $row['meta']];
            }

            if (!empty($row['etapa']) && !in_array(['etapa' => $row['etapa']], $filtros['etapas'])) {
                $filtros['etapas'][] = ['etapa' => $row['etapa']];
            }

            if (!empty($row['responsavel']) && !in_array(['responsavel' => $row['responsavel']], $filtros['responsavel'])) {
                $filtros['responsavel'][] = ['responsavel' => $row['responsavel']];
            }

            if (!empty($row['status']) && !in_array(['status' => $row['status']], $filtros['status'])) {
                $filtros['status'][] = ['status' => $row['status']];
            }
        }

        return $filtros;
    }
}
