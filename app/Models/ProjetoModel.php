<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjetoModel extends Model
{
    protected $table = 'projetos';
    protected $primaryKey = 'id';

    protected $allowedFields = ['nome', 'objetivo', 'perspectiva_estrategica', 'interessados', 'status', 'data_publicacao'];

    protected $useTimestamps = true;
    protected $createdField = 'data_criacao';
    protected $updatedField = 'data_atualizacao';

    protected $returnType = 'array';
}
