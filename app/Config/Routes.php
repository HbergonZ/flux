<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\LoginController;

/**
 * @var RouteCollection $routes
 */

service('auth')->routes($routes);
$routes->get('login', '\App\Controllers\Auth\LoginController::loginView');
$routes->get('register', '\App\Controllers\Auth\RegisterController::registerView');
$routes->get('logout', '\App\Controllers\Auth\LoginController::logoutAction');

$routes->get('/', 'VisaoGeral::index');
$routes->group('', function ($routes) {
    // Rotas para visualização de projeto
    $routes->get('visao-projeto/(:num)', 'VisaoProjeto::index/$1');
    $routes->post('visao-projeto/filtrar/(:num)', 'VisaoProjeto::filtrar/$1');

    // Novas rotas para solicitação de edição
    $routes->get('visao-projeto/dados-etapa/(:num)/(:any)', 'VisaoProjeto::dadosEtapa/$1/$2');
    $routes->post('visao-projeto/solicitar-edicao', 'VisaoProjeto::solicitarEdicao');
});


$routes->get('planos', 'Planos::index');
$routes->post('planos/filtrar', 'Planos::filtrar');


$routes->get('acoes/(:num)', 'Acoes::index/$1');
$routes->post('acoes/filtrar/(:num)', 'Acoes::filtrar/$1');

$routes->get('metas/(:num)', 'Metas::index/$1');
$routes->post('metas/filtrar/(:num)', 'Metas::filtrar/$1');

// Para acessar via ações
$routes->get('etapas/(:num)', 'Etapas::index/$1');
$routes->post('etapas/filtrar/acao/(:num)', 'Etapas::filtrar/acao/$1');

// Para acessar via metas
$routes->get('etapas/meta/(:num)', 'Etapas::meta/$1');
$routes->post('etapas/filtrar/meta/(:num)', 'Etapas::filtrar/meta/$1');

$routes->get('visao-geral', 'VisaoGeral::index');
$routes->post('visao-geral/filtrar', 'VisaoGeral::filtrar');

$routes->post('etapas/solicitar-edicao', 'Etapas::solicitarEdicao');
$routes->post('etapas/solicitar-exclusao', 'Etapas::solicitarExclusao');
$routes->post('etapas/solicitar-inclusao', 'Etapas::solicitarInclusao');
$routes->get('etapas/dados-etapa/(:num)', 'Etapas::dadosEtapa/$1');

$routes->get('minhas-solicitacoes', 'MinhasSolicitacoes::index');
$routes->get('minhas-solicitacoes/detalhes/(:num)', 'MinhasSolicitacoes::detalhes/$1');

$routes->post('metas/solicitar-edicao', 'Metas::solicitarEdicao');
$routes->post('metas/solicitar-exclusao', 'Metas::solicitarExclusao');
$routes->post('metas/solicitar-inclusao', 'Metas::solicitarInclusao');
$routes->get('metas/dados-meta/(:num)', 'Metas::dadosMeta/$1');

$routes->post('acoes/solicitar-edicao', 'Acoes::solicitarEdicao');
$routes->post('acoes/solicitar-exclusao', 'Acoes::solicitarExclusao');
$routes->post('acoes/solicitar-inclusao', 'Acoes::solicitarInclusao');
$routes->get('acoes/dados-acao/(:num)', 'Acoes::dadosAcao/$1');

$routes->post('planos/solicitar-edicao', 'Planos::solicitarEdicao');
$routes->post('planos/solicitar-exclusao', 'Planos::solicitarExclusao');
$routes->post('planos/solicitar-inclusao', 'Planos::solicitarInclusao');
$routes->get('planos/dados-plano/(:num)', 'Planos::dadosPlano/$1');

$routes->group('', ['filter' => 'group:admin,superadmin'], function ($routes) {

    $routes->post('planos/cadastrar', 'Planos::cadastrar');
    $routes->get('planos/editar/(:num)', 'Planos::editar/$1');
    $routes->post('planos/atualizar', 'Planos::atualizar');
    $routes->post('planos/excluir', 'Planos::excluir');

    $routes->post('acoes/cadastrar/(:num)', 'Acoes::cadastrar/$1');
    $routes->get('acoes/editar/(:num)', 'Acoes::editar/$1');
    $routes->post('acoes/atualizar/(:num)', 'Acoes::atualizar/$1');
    $routes->post('acoes/excluir/(:num)', 'Acoes::excluir/$1');

    $routes->post('metas/cadastrar/(:num)', 'Metas::cadastrar/$1');
    $routes->get('metas/editar/(:num)', 'Metas::editar/$1');
    $routes->post('metas/atualizar/(:num)', 'Metas::atualizar/$1');
    $routes->post('metas/excluir/(:num)', 'Metas::excluir/$1');

    $routes->post('etapas/cadastrar/acao/(:num)', 'Etapas::cadastrar/acao/$1');
    $routes->get('etapas/editar/(:num)', 'Etapas::editar/$1');
    $routes->post('etapas/atualizar/acao/(:num)', 'Etapas::atualizar/acao/$1');
    $routes->post('etapas/excluir/acao/(:num)', 'Etapas::excluir/acao/$1');

    $routes->post('etapas/cadastrar/meta/(:num)', 'Etapas::cadastrar/meta/$1');
    $routes->post('etapas/atualizar/meta/(:num)', 'Etapas::atualizar/meta/$1');
    $routes->post('etapas/excluir/meta/(:num)', 'Etapas::excluir/meta/$1');

    $routes->get('solicitacoes', 'Solicitacoes::index');
    $routes->get('solicitacoes/avaliar/(:num)', 'Solicitacoes::avaliar/$1');
    $routes->post('solicitacoes/processar', 'Solicitacoes::processar');

    $routes->group('historico-solicitacoes', function ($routes) {
        $routes->get('/', 'HistoricoSolicitacoes::index');
        $routes->get('detalhes/(:num)', 'HistoricoSolicitacoes::detalhes/$1');
    });

    $routes->get('atribuir-grupos', 'AtribuirGrupos::index');
    $routes->post('atribuir-grupos/atribuir', 'AtribuirGrupos::atribuir'); // Exemplo extra

    $routes->get('gerenciar-usuarios', 'Usuarios::index');
    $routes->post('gerenciar-usuarios/filtrar', 'Usuarios::filtrar');
    $routes->get('gerenciar-usuarios/editar/(:num)', 'Usuarios::editar/$1');
    $routes->post('gerenciar-usuarios/atualizar', 'Usuarios::atualizar');
    $routes->post('gerenciar-usuarios/alterar-grupo', 'Usuarios::alterarGrupo');
    $routes->post('gerenciar-usuarios/excluir', 'Usuarios::excluir');
});
