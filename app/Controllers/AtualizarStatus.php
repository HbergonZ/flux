<?php

namespace App\Controllers;

use App\Models\AcoesModel;

class AtualizarStatus extends BaseController
{
    public function index()
    {
        // Carregar o modelo de ações
        $acoesModel = new AcoesModel();

        // Obter todos os projetos que têm ações
        $db = db_connect();
        $projetos = $db->table('projetos')->select('id')->get()->getResultArray();

        foreach ($projetos as $projeto) {
            // Atualizar status de todas as ações do projeto
            $acoesModel->atualizarStatusTodasAcoesProjeto($projeto['id']);
        }

        // Registrar no log
        log_message('info', 'Status das ações atualizado automaticamente via cron job');

        return "Status das ações atualizado com sucesso em " . date('Y-m-d H:i:s');
    }
}
