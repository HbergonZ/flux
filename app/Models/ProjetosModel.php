<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjetosModel extends Model
{
    protected $table = 'projetos'; // Mantemos o nome da tabela por enquanto
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

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

    protected $beforeInsert = ['removerID'];

    protected function removerID(array $data)
    {
        if (isset($data['data']['id'])) {
            unset($data['data']['id']);
        }
        return $data;
    }

    public function getProjetosByPlano($idPlano)
    {
        return $this->where('id_plano', $idPlano)->findAll();
    }

    public function getAcoesDiretasByProjeto($idProjeto)
    {
        return $this->where('id_projeto', $idProjeto)
            ->where('id_etapa IS NULL')
            ->orderBy('ordem', 'ASC')
            ->findAll();
    }
    // Adicione este método ao seu ProjetosModel
    public function getPlanoByProjeto($idProjeto)
    {
        return $this->db->table('projetos')
            ->select('planos.*')
            ->join('planos', 'planos.id = projetos.id_plano') // Assumindo que a chave em planos também é 'id'
            ->where('projetos.id', $idProjeto) // Corrigido para usar 'id' em vez de 'id_projeto'
            ->get()
            ->getRowArray();
    }
}
