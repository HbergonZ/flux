<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitacoesModel extends Model
{
    protected $table = 'solicitacoes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nivel',
        'id_solicitante',
        'id_plano',
        'id_projeto',
        'id_etapa',
        'id_acao',
        'id_meta',
        'tipo',
        'dados_atuais',
        'dados_alterados',
        'justificativa_solicitante',
        'justificativa_avaliador',
        'status',
        'data_solicitacao',
        'data_avaliacao',
        'id_avaliador'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = false;
}
