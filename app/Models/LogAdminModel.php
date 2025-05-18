<?php

namespace App\Models;

use CodeIgniter\Model;

class LogAdminModel extends Model
{
    protected $table = 'log_administrativo';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_usuario',
        'tipo_operacao',
        'entidade',
        'id_entidade',
        'id_excluido',
        'nome_entidade',
        'id_plano',
        'id_projeto',
        'id_etapa',
        'id_acao',
        'id_solicitacao',
        'dados_antigos',
        'dados_novos',
        'justificativa'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'data_registro';
    protected $updatedField = '';
    protected $skipValidation = true;

    // Mapeamento de atividades antigas para novos tipos
    protected $mapeamentoAtividades = [
        'inclusao_projeto' => ['tipo_operacao' => 'criacao', 'entidade' => 'projeto'],
        'edicao_projeto' => ['tipo_operacao' => 'edicao', 'entidade' => 'projeto'],
        'exclusao_projeto' => ['tipo_operacao' => 'exclusao', 'entidade' => 'projeto'],
    ];
}
