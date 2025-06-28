<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SolicitacoesModel;
use Config\Services;

class ShareDataFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verifica se é AJAX
        $isAjax = $request->hasHeader('X-Requested-With') &&
            strtolower($request->header('X-Requested-With')->getValue()) === 'xmlhttprequest';

        if (!$isAjax) {
            // Forma correta de contar registros
            $totalPendentes = (new SolicitacoesModel())
                ->where('status', 'pendente')
                ->countAllResults();  // Método correto para contar

            // Compartilha com todas as views
            Services::renderer()->setVar('total_solicitacoes_pendentes', $totalPendentes);
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
