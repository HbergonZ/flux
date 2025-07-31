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

    /**
     * Obtém todos os planos com cálculo de progresso
     */
    public function getPlanosComProgresso()
    {
        // Query para ações diretamente vinculadas a projetos
        $builderAcoesDiretas = $this->db->table('acoes a')
            ->select('pr.id_plano, COUNT(a.id) as total_diretas')
            ->select('SUM(CASE WHEN a.status IN (\'Finalizado\', \'Finalizado com Atraso\') THEN 1 ELSE 0 END) as finalizadas_diretas')
            ->join('projetos pr', 'pr.id = a.id_projeto', 'left')
            ->where('a.id_projeto IS NOT NULL')
            ->groupBy('pr.id_plano');

        // Query para ações vinculadas a etapas
        $builderAcoesEtapas = $this->db->table('acoes a')
            ->select('pr.id_plano, COUNT(a.id) as total_etapas')
            ->select('SUM(CASE WHEN a.status IN (\'Finalizado\', \'Finalizado com Atraso\') THEN 1 ELSE 0 END) as finalizadas_etapas')
            ->join('etapas e', 'e.id = a.id_etapa', 'left')
            ->join('projetos pr', 'pr.id = e.id_projeto', 'left')
            ->where('a.id_etapa IS NOT NULL')
            ->groupBy('pr.id_plano');

        // Query principal combinando os resultados
        return $this->db->table('planos p')
            ->select('p.*')
            ->select('COALESCE(ad.total_diretas, 0) + COALESCE(ae.total_etapas, 0) as total_acoes')
            ->select('COALESCE(ad.finalizadas_diretas, 0) + COALESCE(ae.finalizadas_etapas, 0) as acoes_finalizadas')
            ->join("({$builderAcoesDiretas->getCompiledSelect()}) as ad", 'ad.id_plano = p.id', 'left')
            ->join("({$builderAcoesEtapas->getCompiledSelect()}) as ae", 'ae.id_plano = p.id', 'left')
            ->orderBy('p.nome', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtém plano por ID com cálculo de progresso
     */
    public function getPlanoComProgresso($id)
    {
        // Recriamos a query específica para um plano
        $builderAcoesDiretas = $this->db->table('acoes a')
            ->select('pr.id_plano, COUNT(a.id) as total_diretas')
            ->select('SUM(CASE WHEN a.status IN (\'Finalizado\', \'Finalizado com Atraso\') THEN 1 ELSE 0 END) as finalizadas_diretas')
            ->join('projetos pr', 'pr.id = a.id_projeto', 'left')
            ->where('a.id_projeto IS NOT NULL')
            ->where('pr.id_plano', $id)
            ->groupBy('pr.id_plano');

        $builderAcoesEtapas = $this->db->table('acoes a')
            ->select('pr.id_plano, COUNT(a.id) as total_etapas')
            ->select('SUM(CASE WHEN a.status IN (\'Finalizado\', \'Finalizado com Atraso\') THEN 1 ELSE 0 END) as finalizadas_etapas')
            ->join('etapas e', 'e.id = a.id_etapa', 'left')
            ->join('projetos pr', 'pr.id = e.id_projeto', 'left')
            ->where('a.id_etapa IS NOT NULL')
            ->where('pr.id_plano', $id)
            ->groupBy('pr.id_plano');

        return $this->db->table('planos p')
            ->select('p.*')
            ->select('COALESCE(ad.total_diretas, 0) + COALESCE(ae.total_etapas, 0) as total_acoes')
            ->select('COALESCE(ad.finalizadas_diretas, 0) + COALESCE(ae.finalizadas_etapas, 0) as acoes_finalizadas')
            ->join("({$builderAcoesDiretas->getCompiledSelect()}) as ad", 'ad.id_plano = p.id', 'left')
            ->join("({$builderAcoesEtapas->getCompiledSelect()}) as ae", 'ae.id_plano = p.id', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Obtém planos filtrados por nome e/ou sigla
     */
    public function getPlanosFiltrados($nome = null, $sigla = null)
    {
        $builder = $this->builder();

        if (!empty($nome)) {
            $builder->like('nome', $nome);
        }

        if (!empty($sigla)) {
            $builder->like('sigla', $sigla);
        }

        return $builder->orderBy('nome', 'ASC')->get()->getResultArray();
    }

    /**
     * Calcula percentual de conclusão de um plano específico
     */
    public function getPercentualConclusao($planoId)
    {
        $plano = $this->getPlanoComProgresso($planoId);

        if (empty($plano) || !isset($plano['total_acoes'])) {
            return 0;
        }

        $totalAcoes = (int)$plano['total_acoes'];
        $acoesFinalizadas = (int)$plano['acoes_finalizadas'];

        return $totalAcoes > 0 ? round(($acoesFinalizadas / $totalAcoes) * 100, 2) : 0;
    }
}
