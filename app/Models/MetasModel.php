<?php

namespace App\Models;

use CodeIgniter\Model;

class MetasModel extends Model
{
    protected $table = 'metas';
    protected $primaryKey = 'id';

    protected $allowedFields = ['nome', 'id_acao'];

    protected $useTimestamps = true;
    protected $createdField = 'data_criacao';
    protected $updatedField = 'data_atualizacao';

    protected $returnType = 'array';

    public function getMetasByAcao($idAcao)
    {
        return $this->where('id_acao', $idAcao)->findAll();
    }
}
