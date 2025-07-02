<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AcoesModel;

class AtualizarStatusAcoes extends BaseCommand
{
    protected $group = 'Tasks';
    protected $name = 'tasks:atualizarstatus';
    protected $description = 'Atualiza o status das ações atrasadas';

    public function run(array $params)
    {
        $model = new AcoesModel();
        $db = \Config\Database::connect();

        try {
            // Atualiza ações que estão atrasadas
            $db->table('acoes')
                ->where('entrega_estimada <', date('Y-m-d'))
                ->where('data_fim IS NULL')
                ->where('status !=', 'Finalizado')
                ->where('status !=', 'Paralisado')
                ->update(['status' => 'Atrasado']);

            CLI::write('Status das ações atualizados com sucesso.', 'green');
        } catch (\Exception $e) {
            CLI::error('Erro ao atualizar status: ' . $e->getMessage());
        }
    }
}
