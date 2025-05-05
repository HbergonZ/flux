<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitacoesModel extends Model
{
    protected $table = 'solicitacoes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nivel',
        'id_etapa',
        'id_acao',
        'id_plano',
        'id_meta',
        'tipo',
        'dados_atuais',
        'dados_alterados',
        'justificativa',
        'solicitante',
        'status',
        'data_solicitacao',
        'data_avaliacao',
        'id_avaliador'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = false;
}
