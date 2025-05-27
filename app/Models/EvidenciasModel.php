<?php

namespace App\Models;

use CodeIgniter\Model;

class EvidenciasModel extends Model
{
    protected $table = 'evidencias';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'descricao',
        'link',
        'tipo',
        'nivel',
        'id_nivel',
        'created_by',
        'evidencia'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $returnType = 'array';

    // Validações
    protected $validationRules = [
        'evidencia' => 'required|min_length[3]',
        'descricao' => 'permit_empty',
        'tipo' => 'required|in_list[texto,link]',
        'nivel' => 'required|in_list[acao,etapa,projeto,plano]',
        'id_nivel' => 'required|numeric'
    ];

    protected $validationMessages = [
        'descricao' => [
            'required' => 'A descrição da evidência é obrigatória',
            'min_length' => 'A descrição deve ter pelo menos 3 caracteres'
        ]
    ];

    public function getEvidenciasByNivel($nivel, $idNivel)
    {
        return $this->where('nivel', $nivel)
            ->where('id_nivel', $idNivel)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function addEvidencia($data)
    {
        $data['created_by'] = auth()->id();

        if ($data['tipo'] === 'link') {
            // Validação básica de URL
            if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
                throw new \RuntimeException('O link fornecido não é válido');
            }
        }

        return $this->insert($data);
    }
}
