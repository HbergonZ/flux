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
        // 1. Força Paralisado se o projeto estiver Paralisado
        if ($statusProjeto === 'Paralisado' && ($acao['status'] ?? null) !== 'Finalizado') {
            return 'Paralisado';
        }

        // 2. Se tem data_fim, status é Finalizado (desde que tenha data_inicio)
        if (!empty($acao['data_fim'])) {
            if (empty($acao['data_inicio'])) {
                throw new \RuntimeException('Não é possível definir data de fim sem data de início');
            }
            return 'Finalizado';
        }

        // 3. Verifica se está atrasado (data atual > entrega estimada)
        if (
            !empty($acao['entrega_estimada']) &&
            empty($acao['data_fim']) &&
            strtotime($acao['entrega_estimada']) < strtotime(date('Y-m-d'))
        ) {
            return 'Atrasado';
        }

        // 4. Se tem data_inicio, status é Em andamento
        if (!empty($acao['data_inicio'])) {
            return 'Em andamento';
        }

        // 5. Caso contrário, Não iniciado
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
    public function podeFinalizar($acaoId)
    {
        $evidencias = $this->db->table('evidencias')
            ->where('nivel', 'acao')
            ->where('id_nivel', $acaoId)
            ->countAllResults();

        return $evidencias > 0;
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

            // Processar evidências adicionadas
            if (!empty($dadosAprovados['evidencias_adicionadas'])) {
                $evidenciasAdicionadas = json_decode($dadosAprovados['evidencias_adicionadas'], true);
                if (is_array($evidenciasAdicionadas)) {
                    $dadosInserir = array_map(function ($evidencia) use ($dadosAprovados) {
                        return [
                            'tipo' => $evidencia['tipo'],
                            'evidencia' => $evidencia['conteudo'],
                            'descricao' => $evidencia['descricao'] ?? null,
                            'nivel' => 'acao',
                            'id_nivel' => $dadosAprovados['id_acao'],
                            'created_by' => auth()->id(),
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                    }, $evidenciasAdicionadas);

                    if (!empty($dadosInserir)) {
                        $db->table('evidencias')->insertBatch($dadosInserir);
                    }
                }
            }

            // Processar evidências removidas
            if (!empty($dadosAprovados['evidencias_removidas'])) {
                $evidenciasRemovidas = json_decode($dadosAprovados['evidencias_removidas'], true);
                if (is_array($evidenciasRemovidas)) {
                    $db->table('evidencias')
                        ->whereIn('id', $evidenciasRemovidas)
                        ->where('nivel', 'acao')
                        ->where('id_nivel', $dadosAprovados['id_acao'])
                        ->delete();
                }
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

    // Adicione este método para processar os responsáveis
    public function processarResponsaveis($acaoId, $responsaveisIds)
    {
        $responsaveisModel = new \App\Models\ResponsaveisModel();

        // Remove todos os responsáveis atuais
        $responsaveisModel->where('nivel', 'acao')
            ->where('nivel_id', $acaoId)
            ->delete();

        // Adiciona os novos responsáveis
        if (!empty($responsaveisIds)) {
            $data = [];
            foreach ($responsaveisIds as $usuarioId) {
                $data[] = [
                    'nivel' => 'acao',
                    'nivel_id' => $acaoId,
                    'usuario_id' => $usuarioId
                ];
            }
            $responsaveisModel->insertBatch($data);
        }

        return true;
    }

    public function getResponsaveis($acaoId)
    {
        return $this->getResponsaveisAcao($acaoId);
    }

    public function getUsuariosDisponiveis($acaoId)
    {
        return $this->db->table('users u')
            ->select('u.id, u.name, ai.secret as email')
            ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
            ->whereNotIn('u.id', function ($builder) use ($acaoId) {
                $builder->select('usuario_id')
                    ->from('responsaveis')
                    ->where('nivel', 'acao')
                    ->where('nivel_id', $acaoId);
            })
            ->orderBy('u.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function atualizarStatusAcoes(int $idOrigem, string $tipoOrigem = 'etapa')
    {
        $builder = $this->builder();

        if ($tipoOrigem === 'etapa') {
            $builder->where('id_etapa', $idOrigem);
        } else {
            $builder->where('id_projeto', $idOrigem)
                ->where('id_etapa IS NULL');
        }

        $acoes = $builder->get()->getResultArray();

        foreach ($acoes as $acao) {
            $novoStatus = $this->calcularStatus($acao);
            if ($novoStatus !== $acao['status']) {
                $this->update($acao['id'], ['status' => $novoStatus]);
            }
        }

        return count($acoes);
    }

    public function getResponsaveisAcao($acaoId)
    {
        return $this->db->table('responsaveis r')
            ->select('u.id, u.name, ai.secret as email')
            ->join('users u', 'u.id = r.usuario_id')
            ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
            ->where('r.nivel', 'acao')
            ->where('r.nivel_id', $acaoId)
            ->orderBy('u.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
