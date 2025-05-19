<?php

namespace App\Models;

use CodeIgniter\Model;

class AcoesModel extends Model
{
    protected $table = 'acoes';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'id',
        'nome',
        'responsavel',
        'equipe',
        'tempo_estimado_dias',
        'entrega_estimada',
        'data_inicio',
        'data_fim',
        'status',
        'ordem',
        'id_projeto',
        'id_etapa'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $returnType = 'array';

    protected $beforeInsert = ['removerID'];

    protected function removerID(array $data)
    {
        if (isset($data['data']['id'])) {
            unset($data['data']['id']);
        }
        return $data;
    }

    public function getAcoesByEtapa($idEtapa)
    {
        return $this->where('id_etapa', $idEtapa)
            ->orderBy('ordem', 'ASC')
            ->findAll();
    }
}
