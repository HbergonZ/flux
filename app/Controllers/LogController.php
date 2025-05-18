<?php

namespace App\Controllers;

use App\Models\LogAdminModel;

class LogController extends BaseController
{
    protected $logModel;

    public function __construct()
    {
        $this->logModel = new LogAdminModel();
    }

    /**
     * Método principal para registrar logs
     */
    public function registrar(string $tipoOperacao, string $entidade, ?array $dadosAntigos = null, ?array $dadosNovos = null, ?string $justificativa = null): bool
    {
        try {
            $dados = $dadosNovos ?? $dadosAntigos;
            $idEntidade = $this->obterIdEntidade($dados, $entidade);
            $nomeEntidade = $this->obterNomeEntidade($dados, $entidade);

            $logData = [
                'id_usuario' => auth()->id(),
                'tipo_operacao' => $tipoOperacao,
                'entidade' => $entidade,
                'id_entidade' => ($tipoOperacao !== 'exclusao') ? $idEntidade : null,
                'id_excluido' => ($tipoOperacao === 'exclusao') ? $idEntidade : null,
                'nome_entidade' => $nomeEntidade,
                'dados_antigos' => $dadosAntigos ? json_encode($dadosAntigos, JSON_UNESCAPED_UNICODE) : null,
                'dados_novos' => $dadosNovos ? json_encode($dadosNovos, JSON_UNESCAPED_UNICODE) : null,
                'justificativa' => $justificativa
            ];

            // Preenche também os campos específicos para compatibilidade
            if ($idEntidade && in_array($entidade, ['plano', 'projeto', 'etapa', 'acao', 'solicitacao'])) {
                $logData["id_{$entidade}"] = ($tipoOperacao !== 'exclusao') ? $idEntidade : null;
            }

            return $this->logModel->insert($logData) !== false;
        } catch (\Exception $e) {
            log_message('error', 'Erro ao registrar log: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Métodos específicos para cada tipo de operação
     */
    public function registrarCriacao(string $entidade, array $dadosNovos, ?string $justificativa = null): bool
    {
        return $this->registrar('criacao', $entidade, null, $dadosNovos, $justificativa);
    }

    public function registrarEdicao(string $entidade, array $dadosAntigos, array $dadosNovos, ?string $justificativa = null): bool
    {
        return $this->registrar('edicao', $entidade, $dadosAntigos, $dadosNovos, $justificativa);
    }

    public function registrarExclusao(string $entidade, array $dadosAntigos, ?string $justificativa = null): bool
    {
        return $this->registrar('exclusao', $entidade, $dadosAntigos, null, $justificativa);
    }

    public function registrarParalisacao(string $entidade, array $dadosAtuais, ?string $justificativa = null): bool
    {
        return $this->registrar('paralisacao', $entidade, null, $dadosAtuais, $justificativa);
    }

    public function registrarRetomada(string $entidade, array $dadosAtuais, ?string $justificativa = null): bool
    {
        return $this->registrar('retomada', $entidade, null, $dadosAtuais, $justificativa);
    }

    public function registrarAprovacao(string $entidade, array $dadosAtuais, ?string $justificativa = null): bool
    {
        return $this->registrar('aprovacao', $entidade, null, $dadosAtuais, $justificativa);
    }

    public function registrarRejeicao(string $entidade, array $dadosAtuais, ?string $justificativa = null): bool
    {
        return $this->registrar('rejeicao', $entidade, null, $dadosAtuais, $justificativa);
    }

    public function registrarAlteracao(string $entidade, array $dadosAntigos, array $dadosNovos, ?string $justificativa = null): bool
    {
        return $this->registrar('alteracao', $entidade, $dadosAntigos, $dadosNovos, $justificativa);
    }

    /**
     * Métodos auxiliares protegidos
     */
    protected function obterIdEntidade(?array $dados, string $entidade): ?int
    {
        if (!$dados) return null;

        // Tenta primeiro o padrão "id_entidade"
        $id = $dados["id_{$entidade}"] ?? null;

        // Se não encontrar, procura pelo ID genérico
        if ($id === null && isset($dados['id'])) {
            $id = $dados['id'];
        }

        return $id ? (int)$id : null;
    }

    protected function obterNomeEntidade(?array $dados, string $entidade): ?string
    {
        if (!$dados) return null;

        // Tenta primeiro o padrão "nome_entidade"
        $nome = $dados["nome_{$entidade}"] ?? null;

        // Se não encontrar, procura pelo nome genérico
        if ($nome === null && isset($dados['nome'])) {
            $nome = $dados['nome'];
        }

        // Se ainda não encontrou, tenta título ou descrição
        if ($nome === null) {
            $nome = $dados['titulo'] ?? $dados['descricao'] ?? null;
        }

        return $nome ? (string)$nome : 'Sem nome';
    }
}
