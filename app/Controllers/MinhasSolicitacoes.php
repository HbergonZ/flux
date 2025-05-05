<?php

namespace App\Controllers;

use App\Models\SolicitacoesModel;
use App\Models\EtapasModel;
use CodeIgniter\API\ResponseTrait;

class MinhasSolicitacoes extends BaseController
{
    use ResponseTrait;

    protected $solicitacaoModel;
    protected $etapaModel;

    public function __construct()
    {
        $this->solicitacaoModel = new SolicitacoesModel();
        $this->etapaModel = new EtapasModel();
    }

    public function index()
    {
        $this->content_data['content'] = view('sys/minhas-solicitacoes');
        return view('layout', $this->content_data);
    }
}
