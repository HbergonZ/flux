<?php

namespace App\Models;

use CodeIgniter\Model;

class EixosModel extends Model
{
    protected $table = 'eixos';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nome',
        'objetivo',
        'perspectiva_estrategica',
        'responsaveis',
        'status',
        'data_publicacao',
        'data_criacao',
        'data_atualizacao'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'data_criacao';
    protected $updatedField = 'data_atualizacao';

    protected $returnType = 'array';
}
