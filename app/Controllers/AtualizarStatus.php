<?php

namespace App\Controllers;

use App\Models\AcoesModel;

class AtualizarStatus extends BaseController
{
    // Constante com o tempo mínimo entre execuções (5 minutos em segundos)
    const MIN_INTERVAL = 2; // 5 * 60

    // Caminho para o arquivo que armazenará o timestamp da última execução
    const TOUCH_FILE = WRITEPATH . 'last_status_update.txt';

    public function index()
    {
        // Verificar se já passou o intervalo mínimo desde a última execução
        if (!$this->shouldExecute()) {
            return "A atualização de status foi executada recentemente. Próxima execução disponível após 5 minutos da última.";
        }

        // Carregar o modelo de ações
        $acoesModel = new AcoesModel();

        // Obter todos os projetos que têm ações
        $db = db_connect();
        $projetos = $db->table('projetos')->select('id')->get()->getResultArray();

        foreach ($projetos as $projeto) {
            // Atualizar status de todas as ações do projeto
            log_message('debug', 'Atualizando ações do projeto ID: ' . $projeto['id']);
            $acoesModel->atualizarStatusTodasAcoesProjeto($projeto['id']);
        }

        // Registrar no log
        log_message('info', 'Status das ações atualizado automaticamente via cron job');

        // Atualizar o touch file com o timestamp atual
        $this->updateLastExecutionTime();

        return "Status das ações atualizado com sucesso em " . date('Y-m-d H:i:s');
    }

    /**
     * Verifica se já passou o tempo mínimo desde a última execução
     */
    protected function shouldExecute(): bool
    {
        // Se o arquivo não existe, precisa executar
        if (!file_exists(self::TOUCH_FILE)) {
            return true;
        }

        // Ler o timestamp da última execução
        $lastTime = file_get_contents(self::TOUCH_FILE);
        if (!$lastTime) {
            return true;
        }

        // Verificar se já passou o intervalo mínimo
        return (time() - (int)$lastTime) >= self::MIN_INTERVAL;
    }

    /**
     * Atualiza o arquivo com o timestamp da última execução
     */
    protected function updateLastExecutionTime(): void
    {
        file_put_contents(self::TOUCH_FILE, time());
    }
}
