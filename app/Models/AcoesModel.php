<?php

namespace App\Models;

use CodeIgniter\Model;

class AcoesModel extends Model
{
    protected $table = 'acoes';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'identificador',
        'acao',
        'descricao',
        'projeto_vinculado',
        'id_eixo',
        'id_projeto',
        'responsaveis'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'data_criacao';
    protected $updatedField = 'data_atualizacao';

    protected $returnType = 'array';

    public function getAcoesByPlano($idPlano)
    {
        return $this->where('id_projeto', $idPlano)->findAll();
    }
}
