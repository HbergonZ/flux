<?php

namespace App\Models;

use CodeIgniter\Model;

class EtapasModel extends Model
{
    protected $table = 'etapas';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'nome',
        'ordem',
        'id_projeto'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'data_criacao';
    protected $updatedField = 'data_atualizacao';
    protected $returnType = 'array';

    protected $beforeInsert = ['removerID'];

    protected function removerID(array $data)
    {
        if (isset($data['data']['id'])) {
            unset($data['data']['id']);
        }
        return $data;
    }

    public function getEtapasByProjeto($idProjeto)
    {
        return $this->where('id_projeto', $idProjeto)
            ->orderBy('ordem', 'ASC')
            ->findAll();
    }

    public function getProximaOrdem($idProjeto)
    {
        $builder = $this->where('id_projeto', $idProjeto)
            ->selectMax('ordem');
        $query = $builder->get();
        $result = $query->getRowArray();

        return ($result['ordem'] ?? 0) + 1;
    }
}
