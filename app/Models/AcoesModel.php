<?php

namespace App\Models;

use CodeIgniter\Model;

class AcoesModel extends Model
{
    protected $table = 'acoes';
    protected $primaryKey = 'id_acao';
    protected $allowedFields = [
        'acao',
        'projeto',
        'responsavel',
        'equipe',
        'tempo_estimado_dias',
        'inicio_estimado',
        'fim_estimado',
        'data_inicio',
        'data_fim',
        'status',
        'id_projeto',
        'ordem',
        'id_etapa'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAcoesByProjeto($idProjeto)
    {
        return $this->where('id_projeto', $idProjeto)
            ->orderBy('data_inicio', 'ASC')
            ->findAll();
    }

    public function getAcoesByEtapa($idEtapa)
    {
        return $this->where('id_etapa', $idEtapa)
            ->orderBy('data_inicio', 'ASC')
            ->findAll();
    }

    protected $beforeInsert = ['emptyStringToNull'];

    protected function emptyStringToNull(array $data)
    {
        if (array_key_exists('data', $data)) {
            foreach ($data['data'] as $key => $value) {
                if ($value === '') {
                    $data['data'][$key] = null;
                }
            }
        }
        return $data;
    }
}
