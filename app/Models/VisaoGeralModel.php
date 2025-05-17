<?php

namespace App\Models;

use CodeIgniter\Model;

class VisaoGeralModel extends Model
{
    protected $table = 'acoes';
    protected $primaryKey = 'id_acao';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'nome',
        'projeto',
        'responsavel',
        'equipe',
        'tempo_estimado_dias',
        'entrega_estimada',
        'data_inicio',
        'data_fim',
        'status',
        'id_projeto',
        'ordem',
        'id_etapa'
    ];

    public function getVisaoGeral(array $filtros = [])
    {
        $builder = $this->db->table('acoes');

        // Seleciona todos os campos necessários com joins
        $builder->select('acoes.*,
                    projetos.nome as nome_projeto, projetos.identificador, projetos.priorizacao_gab,
                    etapas.nome as nome_etapa,
                    planos.nome as nome_plano, planos.sigla as sigla_plano')
            ->join('projetos', 'projetos.id = acoes.id_projeto', 'left')
            ->join('etapas', 'etapas.id = acoes.id_etapa', 'left')
            ->join('planos', 'planos.id = projetos.id_plano', 'left')
            ->orderBy('planos.nome, projetos.nome, etapas.nome, acoes.ordem', 'ASC');

        // Aplica filtros
        $this->aplicarFiltros($builder, $filtros);

        $result = $builder->get()->getResultArray();

        // Formata os dados
        return array_map(function ($item) {
            return [
                'id_acao' => $item['id_acao'],
                'nome' => $item['nome'],
                'projeto' => $item['projeto'] ?? '',
                'responsavel' => $item['responsavel'],
                'equipe' => $item['equipe'],
                'tempo_estimado_dias' => $item['tempo_estimado_dias'],
                'entrega_estimada' => $item['entrega_estimada'],
                'data_inicio' => $item['data_inicio'],
                'data_fim' => $item['data_fim'],
                'status' => $item['status'],
                'id_projeto' => $item['id_projeto'],
                'ordem' => $item['ordem'],
                'id_etapa' => $item['id_etapa'],

                // Campos para exibição
                'plano' => $item['nome_plano'] ?? '',
                'sigla_plano' => $item['sigla_plano'] ?? '',
                'acao' => $item['nome'] ?? '',
                'nome_projeto' => $item['nome_projeto'] ?? '',
                'identificador_projeto' => $item['identificador'] ?? '',
                'etapa' => $item['nome_etapa'] ?? '',
                'priorizacao_gab' => $item['priorizacao_gab'] ?? 0,

                // Datas formatadas
                'entrega_estimada_formatada' => !empty($item['entrega_estimada']) ? date('d/m/Y', strtotime($item['entrega_estimada'])) : '',
                'data_inicio_formatada' => !empty($item['data_inicio']) ? date('d/m/Y', strtotime($item['data_inicio'])) : '',
                'data_fim_formatada' => !empty($item['data_fim']) ? date('d/m/Y', strtotime($item['data_fim'])) : ''
            ];
        }, $result);
    }
    protected function aplicarFiltros(&$builder, array $filtros)
    {
        // Priorização
        if (isset($filtros['priorizacao_gab']) && $filtros['priorizacao_gab'] !== '') {
            $builder->where('projetos.priorizacao_gab', $filtros['priorizacao_gab']);
        }

        // Plano
        if (!empty($filtros['plano'])) {
            $builder->where('planos.nome', $filtros['plano']);
        }

        // Ação
        if (!empty($filtros['acao'])) {
            $builder->where('acoes.nome', $filtros['acao']);
        }

        // Etapa
        if (!empty($filtros['etapa'])) {
            $builder->where('etapas.nome', $filtros['etapa']);
        }

        // Responsável
        if (!empty($filtros['responsavel'])) {
            $builder->where('acoes.responsavel', $filtros['responsavel']);
        }

        // Equipe
        if (!empty($filtros['equipe'])) {
            $builder->like('acoes.equipe', $filtros['equipe']);
        }

        // Status
        if (!empty($filtros['status'])) {
            $builder->where('acoes.status', $filtros['status']);
        }

        // Data início
        if (!empty($filtros['data_inicio'])) {
            $builder->where('acoes.data_inicio >=', $filtros['data_inicio']);
        }

        // Data fim
        if (!empty($filtros['data_fim'])) {
            $builder->where('acoes.data_fim <=', $filtros['data_fim']);
        }
    }

    public function getFiltrosDistinct()
    {
        $db = $this->db;

        // Consulta para planos
        $planos = $db->table('planos')
            ->select('planos.nome as plano')
            ->join('projetos', 'projetos.id_plano = planos.id', 'left')
            ->join('acoes', 'acoes.id_projeto = projetos.id', 'left')
            ->groupBy('planos.nome')
            ->orderBy('planos.nome')
            ->get()
            ->getResultArray();

        // Consulta para ações
        $acoes = $db->table('acoes')
            ->select('acoes.nome as acao')
            ->groupBy('acoes.nome')
            ->orderBy('acoes.nome')
            ->get()
            ->getResultArray();

        // Consulta para etapas
        $etapas = $db->table('etapas')
            ->select('etapas.nome as etapa')
            ->groupBy('etapas.nome')
            ->orderBy('etapas.nome')
            ->get()
            ->getResultArray();

        // Consulta para responsáveis
        $responsaveis = $db->table('acoes')
            ->select('acoes.responsavel')
            ->groupBy('acoes.responsavel')
            ->orderBy('acoes.responsavel')
            ->get()
            ->getResultArray();

        // Consulta para equipes
        $equipes = $db->table('acoes')
            ->select('acoes.equipe')
            ->groupBy('acoes.equipe')
            ->orderBy('acoes.equipe')
            ->get()
            ->getResultArray();

        // Consulta para status
        $status = $db->table('acoes')
            ->select('acoes.status')
            ->groupBy('acoes.status')
            ->orderBy('acoes.status')
            ->get()
            ->getResultArray();

        // Processa os resultados para obter valores distintos
        return [
            'planos' => array_map(function ($item) {
                return ['plano' => $item['plano']];
            }, $planos),
            'acoes' => array_map(function ($item) {
                return ['acao' => $item['acao']];
            }, $acoes),
            'etapas' => array_map(function ($item) {
                return ['etapa' => $item['etapa']];
            }, $etapas),
            'responsaveis' => array_map(function ($item) {
                return ['responsavel' => $item['responsavel']];
            }, $responsaveis),
            'equipes' => array_map(function ($item) {
                return ['equipe' => $item['equipe']];
            }, $equipes),
            'status' => array_map(function ($item) {
                return ['status' => $item['status']];
            }, $status)
        ];
    }
}
