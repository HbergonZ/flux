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
    protected $beforeUpdate = ['calcularStatusAntesDeAtualizar'];

    // Validações
    protected $validationRules = [
        'nome' => 'required|min_length[3]|max_length[255]',
        'id_projeto' => 'required|numeric',
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

        // Se tem data_fim, status é Finalizado (desde que tenha data_inicio)
        if (!empty($acao['data_fim'])) {
            if (empty($acao['data_inicio'])) {
                throw new \RuntimeException('Não é possível definir data de fim sem data de início');
            }
            return 'Finalizado';
        }

        // Se tem data_inicio, status é Em andamento
        if (!empty($acao['data_inicio'])) {
            return 'Em andamento';
        }

        // Caso contrário, Não iniciado
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
        return $this->db->table('acoes_equipe')
            ->join('users', 'users.id = acoes_equipe.usuario_id')
            ->select('users.username')
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

    public function processarAlteracoesEquipe($acaoId, $alteracoesEquipe)
    {
        $db = \Config\Database::connect();

        try {
            $db->transStart();

            // Remover membros
            if (!empty($alteracoesEquipe['remover'])) {
                $db->table('acoes_equipe')
                    ->where('acao_id', $acaoId)
                    ->whereIn('usuario_id', $alteracoesEquipe['remover'])
                    ->delete();
            }

            // Adicionar membros
            if (!empty($alteracoesEquipe['adicionar'])) {
                $dadosInserir = array_map(function ($usuarioId) use ($acaoId) {
                    return [
                        'acao_id' => $acaoId,
                        'usuario_id' => $usuarioId,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }, $alteracoesEquipe['adicionar']);

                $db->table('acoes_equipe')->insertBatch($dadosInserir);
            }

            $db->transComplete();

            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Erro ao processar alterações da equipe: ' . $e->getMessage());
            return false;
        }
    }

    public function getEquipeComUsernames($idAcao)
    {
        $result = $this->db->table('acoes_equipe')
            ->select('users.username')
            ->join('users', 'users.id = acoes_equipe.id_usuario')
            ->where('acoes_equipe.id_acao', $idAcao)
            ->get()
            ->getResultArray();

        return array_column($result, 'username');
    }

    public function processarSolicitacaoEdicao($dadosAprovados)
    {
        $db = \Config\Database::connect();

        try {
            $db->transStart();

            // Atualizar dados básicos da ação
            if (isset($dadosAprovados['dados_acao'])) {
                $this->update($dadosAprovados['id_acao'], $dadosAprovados['dados_acao']);
            }

            // Processar alterações na equipe
            if (isset($dadosAprovados['alteracoes_equipe'])) {
                $this->processarAlteracoesEquipe($dadosAprovados['id_acao'], $dadosAprovados['alteracoes_equipe']);
            }

            // Processar evidências solicitadas
            if (isset($dadosAprovados['evidencias_solicitadas'])) {
                $this->processarEvidenciasSolicitadas($dadosAprovados['id_acao'], $dadosAprovados['evidencias_solicitadas']);
            }

            $db->transComplete();

            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Erro ao processar solicitação de edição: ' . $e->getMessage());
            return false;
        }
    }

    protected function processarEvidenciasSolicitadas($acaoId, $evidencias)
    {
        if (empty($evidencias)) {
            return true;
        }

        // Processar evidências a serem adicionadas
        if (isset($evidencias['adicionar']) && is_array($evidencias['adicionar'])) {
            $dadosInserir = array_map(function ($evidencia) use ($acaoId) {
                return [
                    'tipo' => $evidencia['tipo'],
                    'evidencia' => $evidencia['conteudo'],
                    'descricao' => $evidencia['descricao'] ?? null,
                    'nivel' => 'acao',
                    'id_nivel' => $acaoId,
                    'created_by' => auth()->id(),
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }, $evidencias['adicionar']);

            if (!empty($dadosInserir)) {
                $this->db->table('evidencias')->insertBatch($dadosInserir);
            }
        }

        // Processar evidências a serem removidas
        if (isset($evidencias['remover']) && is_array($evidencias['remover'])) {
            $this->db->table('evidencias')
                ->whereIn('id', $evidencias['remover'])
                ->where('nivel', 'acao')
                ->where('id_nivel', $acaoId)
                ->delete();
        }

        return true;
    }

    protected function calcularStatusAntesDeAtualizar(array $data)
    {
        if (isset($data['data']['data_fim']) && !empty($data['data']['data_fim'])) {
            if (empty($data['data']['data_inicio'])) {
                throw new \RuntimeException('Não é possível definir data de fim sem data de início');
            }
            $data['data']['status'] = 'Finalizado';
        } elseif (isset($data['data']['data_inicio']) && !empty($data['data']['data_inicio'])) {
            $data['data']['status'] = 'Em andamento';
        } elseif (!isset($data['data']['status'])) {
            $data['data']['status'] = 'Não iniciado';
        }

        return $data;
    }
}
