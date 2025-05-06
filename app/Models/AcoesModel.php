<?php

namespace App\Models;

use CodeIgniter\Model;

class AcoesModel extends Model
{
    protected $table = 'acoes';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true; // Ativar auto-incremento

    protected $allowedFields = [
        'identificador',
        'acao',
        'descricao',
        'projeto_vinculado',
        'id_eixo',
        'id_plano',
        'responsaveis'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'data_criacao';
    protected $updatedField = 'data_atualizacao';
    protected $returnType = 'array';

    // Garantir que o ID não seja enviado em inserções
    protected $beforeInsert = ['removerID'];

    protected function removerID(array $data)
    {
        if (isset($data['data']['id'])) {
            unset($data['data']['id']);
        }
        return $data;
    }
    public function getAcoesByPlano($idPlano)
    {
        return $this->where('id_plano', $idPlano)->findAll();
    }
}
