<?php

namespace App\Models;

use CodeIgniter\Model;

class AcoesModel extends Model
{
    protected $table = 'acoes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'nome',
        'descricao',
        'responsavel',
        'equipe',
        'tempo_estimado_dias',
        'entrega_estimada',
        'data_inicio',
        'data_fim',
        'status',
        'ordem',
        'id_projeto',
        'id_etapa',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $returnType = 'array';

    // Validações
    protected $validationRules = [
        'nome' => 'required|min_length[3]|max_length[255]',
        'id_projeto' => 'required|numeric',
        'status' => 'permit_empty|in_list[Não iniciado,Em andamento,Concluído,Cancelado]',
        'ordem' => 'permit_empty|numeric'
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'O nome da ação é obrigatório',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres'
        ],
        'id_projeto' => [
            'required' => 'O projeto vinculado é obrigatório'
        ]
    ];

    protected function handleEquipeField(array $data)
    {
        if (isset($data['data']['equipe']) && is_array($data['data']['equipe'])) {
            $data['data']['equipe'] = json_encode($data['data']['equipe']);
        }
        return $data;
    }

    public function getAcoesByEtapa($idEtapa)
    {
        return $this->where('id_etapa', $idEtapa)
            ->orderBy('ordem', 'ASC')
            ->findAll();
    }

    public function getProximaOrdem($idEtapa)
    {
        $builder = $this->builder();
        $builder->selectMax('ordem');
        $builder->where('id_etapa', $idEtapa);
        $query = $builder->get();

        $result = $query->getRow();
        return ($result->ordem ?? 0) + 1;
    }
}
