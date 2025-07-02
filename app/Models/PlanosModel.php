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

    public function getPlanosComProgresso()
    {
        // Subquery para contar ações por projeto
        $subqueryAcoes = $this->db->table('acoes')
            ->select('id_projeto, COUNT(*) as total_acoes, SUM(CASE WHEN status = "Finalizado" THEN 1 ELSE 0 END) as acoes_finalizadas')
            ->where('id_projeto IS NOT NULL')
            ->groupBy('id_projeto')
            ->getCompiledSelect();

        // Subquery para contar ações por etapa (quando não vinculadas diretamente a projeto)
        $subqueryAcoesEtapas = $this->db->table('acoes')
            ->select('id_etapa, COUNT(*) as total_acoes, SUM(CASE WHEN status = "Finalizado" THEN 1 ELSE 0 END) as acoes_finalizadas')
            ->where('id_etapa IS NOT NULL')
            ->groupBy('id_etapa')
            ->getCompiledSelect();

        // Subquery para projetos com progresso
        $subqueryProjetos = $this->db->table('projetos')
            ->select('id_plano,
            SUM(COALESCE(p.total_acoes, 0) + COALESCE(pe.total_acoes, 0)) as total_acoes,
            SUM(COALESCE(p.acoes_finalizadas, 0) + COALESCE(pe.acoes_finalizadas, 0)) as acoes_finalizadas')
            ->join("($subqueryAcoes) as p", 'p.id_projeto = projetos.id', 'left')
            ->join('etapas', 'etapas.id_projeto = projetos.id', 'left')
            ->join("($subqueryAcoesEtapas) as pe", 'pe.id_etapa = etapas.id', 'left')
            ->groupBy('id_plano')
            ->getCompiledSelect();

        return $this->db->table('planos')
            ->select('planos.*,
            COALESCE(progresso.total_acoes, 0) as total_acoes,
            COALESCE(progresso.acoes_finalizadas, 0) as acoes_finalizadas')
            ->join("($subqueryProjetos) as progresso", 'progresso.id_plano = planos.id', 'left')
            ->orderBy('planos.nome', 'ASC')
            ->get()
            ->getResultArray();
    }
}
