<?php

namespace App\Models;

use CodeIgniter\Model;

class EtapaModel extends Model
{
    protected $table = 'etapas';
    protected $primaryKey = 'id_etapa';

    protected $allowedFields = [
        'etapa',
        'id_acao',
        'acao',
        'id_projeto',
        'coordenacao',
        'responsavel',
        'tempo_estimado_dias',
        'data_inicio',
        'data_fim',
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $returnType = 'array';
}
