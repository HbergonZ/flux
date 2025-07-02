<?php

namespace App\Models;

use CodeIgniter\Model;

class IndicadoresModel extends Model
{
    protected $table = 'indicadores';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'conteudo',
        'descricao',
        'nivel',
        'id_nivel',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $returnType = 'array';

    // Validações
    protected $validationRules = [
        'conteudo' => 'required|min_length[3]',
        'descricao' => 'permit_empty',
        'nivel' => 'required|in_list[acao,etapa,projeto,plano]',
        'id_nivel' => 'required|numeric'
    ];

    public function getIndicadoresByNivel($nivel, $idNivel)
    {
        return $this->select('id, descricao, conteudo, created_at')
            ->where('nivel', $nivel)
            ->where('id_nivel', $idNivel)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function addIndicador($data)
    {
        $data['created_by'] = auth()->id();
        return $this->insert($data);
    }

    public function removeIndicador($id)
    {
        return $this->delete($id);
    }
}
