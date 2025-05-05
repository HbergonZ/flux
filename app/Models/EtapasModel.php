<?php

namespace App\Models;

use CodeIgniter\Model;

class EtapasModel extends Model
{
    protected $table = 'etapas';
    protected $primaryKey = 'id_etapa';
    protected $allowedFields = [
        'etapa',
        'acao',
        'responsavel',
        'equipe',
        'tempo_estimado_dias',
        'data_inicio',
        'data_fim',
        'status',
        'id_acao',
        'id_meta'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getEtapasByAcao($idAcao)
    {
        return $this->where('id_acao', $idAcao)
            ->orderBy('data_inicio', 'ASC')
            ->findAll();
    }

    public function getEtapasByMeta($idMeta)
    {
        return $this->where('id_meta', $idMeta)
            ->orderBy('data_inicio', 'ASC')
            ->findAll();
    }
}
