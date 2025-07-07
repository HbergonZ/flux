<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AcoesModel;

class AtualizarStatusAcoes extends BaseCommand
{
    protected $group = 'Tasks';
    protected $name = 'acoes:atualizar-status';
    protected $description = 'Atualiza o status de todas as ações do sistema';

    public function run(array $params)
    {
        $acoesModel = new AcoesModel();

        try {
            CLI::write('Iniciando atualização de status das ações...', 'yellow');

            // Obter todos os projetos que têm ações
            $db = db_connect();
            $projetosComAcoes = $db->table('projetos')
                ->join('acoes', 'acoes.id_projeto = projetos.id')
                ->groupBy('projetos.id')
                ->get()
                ->getResultArray();

            $totalAtualizadas = 0;

            foreach ($projetosComAcoes as $projeto) {
                $resultado = $acoesModel->atualizarStatusTodasAcoesProjeto($projeto['id']);
                $totalAtualizadas += $resultado['atualizadas'];

                CLI::write("Projeto ID {$projeto['id']}: {$resultado['atualizadas']}/{$resultado['total_acoes']} ações atualizadas", 'green');
            }

            CLI::write("Processo concluído. Total de ações atualizadas: $totalAtualizadas", 'green');
            return 0; // Código de sucesso
        } catch (\Exception $e) {
            CLI::error("Erro ao atualizar status: " . $e->getMessage());
            return 1; // Código de erro
        }
    }
}
