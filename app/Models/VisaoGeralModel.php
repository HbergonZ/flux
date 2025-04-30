<?php

namespace App\Models;

use CodeIgniter\Model;

class VisaoGeralModel extends Model
{
    protected $table = 'visao_geral';
    protected $primaryKey = 'acao_id'; // Ajuste conforme sua necessidade

    protected $allowedFields = []; // Views geralmente não permitem escrita

    protected $returnType = 'array';

    // Não use timestamps para views
    protected $useTimestamps = false;

    /**
     * Filtra os dados da visão geral
     */
    public function filtrar($filtros)
    {
        $builder = $this->builder();

        if (!empty($filtros['plano'])) {
            $builder->like('plano', $filtros['plano']);
        }

        if (!empty($filtros['acao'])) {
            $builder->like('acao', $filtros['acao']);
        }

        if (!empty($filtros['meta'])) {
            $builder->like('meta', $filtros['meta']);
        }

        if (!empty($filtros['etapa'])) {
            $builder->like('etapa', $filtros['etapa']);
        }

        if (!empty($filtros['coordenacao'])) {
            $builder->like('coordenacao', $filtros['coordenacao']);
        }

        if (!empty($filtros['responsavel'])) {
            $builder->like('responsavel_etapa', $filtros['responsavel']);
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
