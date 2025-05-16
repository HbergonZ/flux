<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjetosModel extends Model
{
    protected $table = 'projetos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'identificador',
        'nome',
        'descricao',
        'projeto_vinculado',
        'priorizacao_gab',
        'id_eixo',
        'id_plano',
        'responsaveis'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'data_criacao';
    protected $updatedField = 'data_atualizacao';
    protected $returnType = 'array';

    public function getProjetosByPlano($idPlano)
    {
        return $this->select('projetos.*, eixos.nome as nome_eixo')
            ->join('eixos', 'eixos.id = projetos.id_eixo', 'left')
            ->where('id_plano', $idPlano)
            ->orderBy('nome', 'ASC')
            ->findAll();
    }

    public function getProjetosFiltrados($idPlano, $filtros)
    {
        $builder = $this->select('projetos.*, eixos.nome as nome_eixo')
            ->join('eixos', 'eixos.id = projetos.id_eixo', 'left')
            ->where('id_plano', $idPlano);

        if (!empty($filtros['nome'])) {
            $builder->like('projetos.nome', $filtros['nome']);
        }

        if (!empty($filtros['projeto_vinculado'])) {
            $builder->like('projetos.projeto_vinculado', $filtros['projeto_vinculado']);
        }

        if (!empty($filtros['id_eixo'])) {
            $builder->where('projetos.id_eixo', $filtros['id_eixo']);
        }

        return $builder->orderBy('nome', 'ASC')->findAll();
    }

    public function getPlanoByProjeto($idProjeto)
    {
        return $this->db->table('projetos')
            ->select('planos.*')
            ->join('planos', 'planos.id = projetos.id_plano')
            ->where('projetos.id', $idProjeto)
            ->get()
            ->getRowArray();
    }
}
