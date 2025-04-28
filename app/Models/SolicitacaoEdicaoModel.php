<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitacaoEdicaoModel extends Model
{
    protected $table = 'solicitacoes_edicao';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_etapa',
        'id_acao',
        'id_projeto',
        'dados_atuais',
        'dados_alterados',
        'justificativa',
        'status',
        'data_avaliacao',  // Adicione esta linha
        'id_avaliador'     // Adicione se for usar este campo
    ];
    protected $returnType = 'array';
    protected $useTimestamps = false;
}
