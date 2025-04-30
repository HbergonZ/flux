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
        'coordenacao',
        'responsavel',
        'tempo_estimado_dias',
        'data_inicio',
        'data_fim',
        'status',
        'id_acao',
        'id_meta'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $returnType = 'array';

    public function getEtapasByAcao($idAcao)
    {
        return $this->where('id_acao', $idAcao)->findAll();
    }

    public function getEtapasByMeta($idMeta)
    {
        return $this->where('id_meta', $idMeta)->findAll();
    }
}
