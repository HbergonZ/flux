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
        'metas',
        'projeto_vinculado',
        'priorizacao_gab',
        'id_eixo',
        'id_plano',
        'responsaveis',
        'status'
    ];

    protected $validationRules = [
        'status' => 'permit_empty|in_list[Ativo,Paralisado,Concluído]'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'data_criacao';
    protected $updatedField = 'data_atualizacao';
    protected $returnType = 'array';

    // Adicione esta propriedade
    protected $responsaveisModel;

    // Adicione o construtor
    public function __construct()
    {
        parent::__construct();
        $this->responsaveisModel = new \App\Models\ResponsaveisModel();
    }

    public function getProjetosByPlano($idPlano)
    {
        $subquery = $this->db->table('acoes')
            ->select('id_projeto, COUNT(*) as total_acoes, SUM(CASE WHEN status IN ("Finalizado", "Finalizado com atraso") THEN 1 ELSE 0 END) as acoes_finalizadas')
            ->where('id_projeto IS NOT NULL')
            ->groupBy('id_projeto')
            ->getCompiledSelect();

        return $this->db->table('projetos')
            ->select('projetos.*, eixos.nome as nome_eixo,
                 COALESCE(progresso.total_acoes, 0) as total_acoes,
                 COALESCE(progresso.acoes_finalizadas, 0) as acoes_finalizadas')
            ->join('eixos', 'eixos.id = projetos.id_eixo', 'left')
            ->join("($subquery) as progresso", 'progresso.id_projeto = projetos.id', 'left')
            ->where('id_plano', $idPlano)
            ->orderBy('nome', 'ASC')
            ->get()
            ->getResultArray();
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

    public function atualizarStatus(int $idProjeto, string $novoStatus)
    {
        // Inicia transação para garantir consistência
        $this->db->transStart();

        try {
            // Atualiza o status do projeto
            $this->update($idProjeto, ['status' => $novoStatus]);

            // Dispara atualização em cascata para as ações
            $acoesModel = new \App\Models\AcoesModel();
            $acoesModel->atualizarStatusAcoesProjeto($idProjeto, $novoStatus);

            $this->db->transComplete();

            return true;
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Erro ao atualizar status do projeto: ' . $e->getMessage());
            return false;
        }
    }

    public function getTotalProjetos($idPlano, $filtros = [])
    {
        $builder = $this->db->table($this->table)
            ->where('id_plano', $idPlano);

        if (!empty($filtros['nome'])) {
            $builder->like('nome', $filtros['nome']);
        }

        if (!empty($filtros['projeto_vinculado'])) {
            $builder->like('projeto_vinculado', $filtros['projeto_vinculado']);
        }

        if (!empty($filtros['id_eixo'])) {
            $builder->where('id_eixo', $filtros['id_eixo']);
        }

        // Busca global
        if (!empty($filtros['search']['value'])) {
            $searchValue = $filtros['search']['value'];
            $builder->groupStart()
                ->like('identificador', $searchValue)
                ->orLike('nome', $searchValue)
                ->orLike('descricao', $searchValue)
                ->orLike('projeto_vinculado', $searchValue)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getProjetosFiltrados($idPlano, $filtros = [])
    {
        // Subquery para cálculo do progresso e data fim
        $subqueryAcoes = $this->db->table('acoes')
            ->select('id_projeto,
             COUNT(*) as total_acoes,
             SUM(CASE WHEN status IN ("Finalizado", "Finalizado com atraso") THEN 1 ELSE 0 END) as acoes_finalizadas,
             CASE
                WHEN COUNT(*) = 0 THEN 0
                ELSE (SUM(CASE WHEN status = "Finalizado" THEN 1 ELSE 0 END) / COUNT(*)) * 100
             END as percentual_progresso,
             CASE
                WHEN SUM(CASE WHEN data_fim IS NULL THEN 1 ELSE 0 END) > 0 THEN NULL
                ELSE MAX(data_fim)
             END as data_fim_projeto')
            ->where('id_projeto IS NOT NULL')
            ->groupBy('id_projeto')
            ->getCompiledSelect();

        // Subquery para obter responsáveis formatados como JSON
        $subqueryResponsaveis = $this->db->table('responsaveis')
            ->select('nivel_id,
             JSON_ARRAYAGG(
                JSON_OBJECT(
                    "usuario_id", usuario_id,
                    "username", users.username,
                    "name", users.name,
                    "email", auth_identities.secret
                )
             ) as responsaveis_json')
            ->join('users', 'users.id = responsaveis.usuario_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->where('nivel', 'projeto')
            ->groupBy('nivel_id')
            ->getCompiledSelect();

        $builder = $this->db->table('projetos')
            ->select('projetos.*,
             eixos.nome as nome_eixo,
             COALESCE(progresso.total_acoes, 0) as total_acoes,
             COALESCE(progresso.acoes_finalizadas, 0) as acoes_finalizadas,
             COALESCE(progresso.percentual_progresso, 0) as percentual_progresso,
             progresso.data_fim_projeto,
             COALESCE(responsaveis.responsaveis_json, "[]") as responsaveis')
            ->join('eixos', 'eixos.id = projetos.id_eixo', 'left')
            ->join("($subqueryAcoes) as progresso", 'progresso.id_projeto = projetos.id', 'left')
            ->join("($subqueryResponsaveis) as responsaveis", 'responsaveis.nivel_id = projetos.id', 'left')
            ->where('projetos.id_plano', $idPlano);

        // Aplicar filtros
        if (!empty($filtros['nome'])) {
            $builder->like('projetos.nome', $filtros['nome']);
        }

        if (!empty($filtros['projeto_vinculado'])) {
            $builder->like('projetos.projeto_vinculado', $filtros['projeto_vinculado']);
        }

        if (!empty($filtros['id_eixo'])) {
            $builder->where('projetos.id_eixo', $filtros['id_eixo']);
        }

        // Busca global
        if (!empty($filtros['search']['value'])) {
            $searchValue = $filtros['search']['value'];
            $builder->groupStart()
                ->like('projetos.identificador', $searchValue)
                ->orLike('projetos.nome', $searchValue)
                ->orLike('projetos.descricao', $searchValue)
                ->orLike('projetos.projeto_vinculado', $searchValue)
                ->groupEnd();
        }

        // Ordenação
        if (!empty($filtros['order'])) {
            $columnIndex = $filtros['order'][0]['column'];
            $direction = $filtros['order'][0]['dir'];

            $columns = [
                0 => 'projetos.identificador',
                1 => 'projetos.nome',
                2 => 'projetos.descricao',
                3 => 'projetos.projeto_vinculado',
                4 => 'responsaveis.responsaveis_json',
                5 => 'progresso.data_fim_projeto', // Nova coluna para ordenação
                6 => 'progresso.percentual_progresso'
            ];

            if (isset($columns[$columnIndex])) {
                $builder->orderBy($columns[$columnIndex], $direction);
            }
        }

        // Paginação
        if (isset($filtros['start']) && $filtros['length'] != -1) {
            $builder->limit($filtros['length'], $filtros['start']);
        }

        $result = $builder->get()->getResultArray();

        // Decodificar JSON de responsáveis
        return array_map(function ($projeto) {
            $projeto['responsaveis'] = json_decode($projeto['responsaveis'], true) ?: [];
            return $projeto;
        }, $result);
    }

    public function getResponsaveis($projetoId)
    {
        return $this->responsaveisModel->getResponsaveis('projeto', $projetoId);
    }

    public function getUsuariosDisponiveis($projetoId)
    {
        return $this->responsaveisModel->getUsuariosDisponiveis('projeto', $projetoId);
    }

    public function adicionarResponsavel($projetoId, $usuarioId)
    {
        return $this->responsaveisModel->adicionarResponsavel('projeto', $projetoId, $usuarioId);
    }

    public function removerResponsavel($projetoId, $usuarioId)
    {
        return $this->responsaveisModel->removerResponsavel('projeto', $projetoId, $usuarioId);
    }
}
