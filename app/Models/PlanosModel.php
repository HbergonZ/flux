<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanosModel extends Model
{
    protected $table = 'planos';
    protected $primaryKey = 'id';

    protected $allowedFields = ['nome', 'sigla', 'descricao'];

    protected $useTimestamps = true;
    protected $createdField = 'data_criacao';
    protected $updatedField = 'data_atualizacao';

    protected $returnType = 'array';
}
