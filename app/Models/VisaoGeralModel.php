<?php

namespace App\Models;

use CodeIgniter\Model;

class VisaoGeralModel extends Model
{
    protected $table = 'visao_geral';
    protected $primaryKey = 'acao_id';
    protected $allowedFields = [];
    protected $returnType = 'array';
    protected $useTimestamps = false;

    /**
     * Busca valores distintos para os filtros
     */
    public function getFiltrosDistinct()
    {
        return [
            'planos' => $this->builder()->select('plano')->distinct(true)->orderBy('plano', 'ASC')->get()->getResultArray(),
            'acoes' => $this->builder()->select('acao')->distinct(true)->orderBy('acao', 'ASC')->get()->getResultArray(),
            'metas' => $this->builder()->select('meta')->distinct(true)->orderBy('meta', 'ASC')->get()->getResultArray(),
            'etapas' => $this->builder()->select('etapa')->distinct(true)->orderBy('etapa', 'ASC')->get()->getResultArray(),
            'responsavel' => $this->builder()->select('responsavel')->distinct(true)->orderBy('responsavel', 'ASC')->get()->getResultArray(),
            'status' => $this->builder()->select('status')->distinct(true)->orderBy('status', 'ASC')->get()->getResultArray()
        ];
    }

    /**
     * Filtra os dados da visão geral
     */
    public function filtrar($filtros)
    {
        $builder = $this->builder();

        // Filtro de priorização
        if (isset($filtros['priorizacao_gab']) && $filtros['priorizacao_gab'] !== '') {
            $builder->where('priorizacao_gab', $filtros['priorizacao_gab']);
        }

        if (!empty($filtros['plano'])) {
            $builder->where('plano', $filtros['plano']);
        }

        if (!empty($filtros['acao'])) {
            $builder->where('acao', $filtros['acao']);
        }

        if (!empty($filtros['meta'])) {
            $builder->where('meta', $filtros['meta']);
        }

        if (!empty($filtros['etapa'])) {
            $builder->where('etapa', $filtros['etapa']);
        }

        if (!empty($filtros['responsavel'])) {
            $builder->where('responsavel', $filtros['responsavel']);
        }

        if (!empty($filtros['equipe'])) {
            $builder->like('equipe', $filtros['equipe']);
        }

        if (!empty($filtros['status'])) {
            $builder->where('status', $filtros['status']);
        }

        if (!empty($filtros['data_inicio'])) {
            $builder->where('data_inicio >=', $filtros['data_inicio']);
        }

        if (!empty($filtros['data_fim'])) {
            $builder->where('data_fim <=', $filtros['data_fim']);
        }

        return $builder->get()->getResultArray();
    }
}
