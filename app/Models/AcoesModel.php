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

    public function calcularStatus(array $acao, ?string $statusProjeto = null): string
    {
        // Força Paralisado se o projeto estiver Paralisado
        if ($statusProjeto === 'Paralisado' && ($acao['status'] ?? null) !== 'Finalizado') {
            return 'Paralisado';
        }

        // Lógica original de status
        if (empty($acao['data_inicio']) && empty($acao['data_fim'])) {
            return 'Não iniciado';
        }
        if (!empty($acao['data_inicio']) && empty($acao['data_fim'])) {
            return 'Em andamento';
        }
        if (!empty($acao['data_inicio']) && !empty($acao['data_fim'])) {
            return 'Finalizado';
        }

        return 'Não iniciado';
    }

    public function atualizarStatusAcoesProjeto(int $idProjeto, string $statusProjeto, $db = null)
    {
        if ($db === null) {
            $db = \Config\Database::connect();
        }

        $builder = $db->table($this->table);
        $atualizadas = 0;

        // Busca todas as ações do projeto
        $acoes = $builder->where('id_projeto', $idProjeto)
            ->get()
            ->getResultArray();

        foreach ($acoes as $acao) {
            if ($statusProjeto === 'Paralisado' && $acao['status'] !== 'Finalizado') {
                $builder->where('id', $acao['id'])
                    ->update(['status' => 'Paralisado']);
                $atualizadas++;
            } elseif ($statusProjeto === 'Ativo' && $acao['status'] === 'Paralisado') {
                $novoStatus = $this->calcularStatus($acao, 'Ativo');
                $builder->where('id', $acao['id'])
                    ->update(['status' => $novoStatus]);
                $atualizadas++;
            }
        }

        return ['total_acoes' => count($acoes), 'atualizadas' => $atualizadas];
    }
    public function getEquipeAcao($acaoId)
    {
        $builder = $this->db->table('acoes_equipe');
        return $builder->select('users.id, users.username, auth_identities.secret as email')
            ->join('users', 'users.id = acoes_equipe.usuario_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"')
            ->where('acao_id', $acaoId)
            ->get()
            ->getResultArray();
    }

    public function adicionarMembroEquipe($acaoId, $usuarioId)
    {
        $builder = $this->db->table('acoes_equipe');
        return $builder->insert([
            'acao_id' => $acaoId,
            'usuario_id' => $usuarioId
        ]);
    }

    public function removerMembroEquipe($acaoId, $usuarioId)
    {
        $builder = $this->db->table('acoes_equipe');
        return $builder->where([
            'acao_id' => $acaoId,
            'usuario_id' => $usuarioId
        ])->delete();
    }

    public function getUsernamesEquipe($acaoId)
    {
        $builder = $this->db->table('acoes_equipe');
        return $builder->select('users.username')
            ->join('users', 'users.id = acoes_equipe.usuario_id')
            ->where('acao_id', $acaoId)
            ->get()
            ->getResultArray();
    }
    public function podeFinalizar($acaoId)
    {
        $evidencias = $this->db->table('evidencias')
            ->where('nivel', 'acao')
            ->where('id_nivel', $acaoId)
            ->countAllResults();

        return $evidencias > 0;
    }
}
