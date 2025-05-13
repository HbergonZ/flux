<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

service('auth')->routes($routes);
$routes->get('login', '\App\Controllers\Auth\LoginController::loginView');
$routes->get('register', '\App\Controllers\Auth\RegisterController::registerView');
$routes->get('logout', '\App\Controllers\Auth\LoginController::logoutAction');

$routes->get('/', 'VisaoGeral::index');

// Rotas principais hierárquicas
$routes->group('', function ($routes) {
    // Hierarquia completa
    $routes->get('planos/(:num)/projetos', 'Projetos::index/$1');
    $routes->get('projetos/(:num)/etapas', 'Etapas::index/$1');
    $routes->get('etapas/(:num)/acoes', 'Acoes::index/$1');

    // Rotas alternativas para compatibilidade
    $routes->get('projetos/(:num)', 'Projetos::index/$1');
    $routes->get('etapas/(:num)', 'Etapas::index/$1');

    // Visão Geral
    $routes->get('visao-geral', 'VisaoGeral::index');
    $routes->post('visao-geral/filtrar', 'VisaoGeral::filtrar');

    // Visão Projeto
    $routes->get('visao-projeto/(:num)', 'VisaoProjeto::index/$1');
    $routes->post('visao-projeto/filtrar/(:num)', 'VisaoProjeto::filtrar/$1');
    $routes->get('visao-projeto/dados-etapa/(:num)/(:any)', 'VisaoProjeto::dadosEtapa/$1/$2');
    $routes->post('visao-projeto/solicitar-edicao', 'VisaoProjeto::solicitarEdicao');

    // Planos
    $routes->get('planos', 'Planos::index');
    $routes->post('planos/filtrar', 'Planos::filtrar');

    // Projetos
    $routes->post('projetos/filtrar/(:num)', 'Projetos::filtrar/$1');

    // Etapas
    $routes->post('etapas/filtrar/(:num)', 'Etapas::filtrar/$1');

    // Ações
    $routes->post('acoes/cadastrar/(:num)', 'Acoes::cadastrar/$1');
    $routes->get('acoes/editar/(:num)', 'Acoes::editar/$1');
    $routes->post('acoes/atualizar/(:num)', 'Acoes::atualizar/$1');
    $routes->post('acoes/excluir/(:num)', 'Acoes::excluir/$1');
    $routes->post('acoes/filtrar/(:num)', 'Acoes::filtrar/$1');

    // Solicitações
    $routes->get('minhas-solicitacoes', 'MinhasSolicitacoes::index');
    $routes->get('minhas-solicitacoes/detalhes/(:num)', 'MinhasSolicitacoes::detalhes/$1');

    // Solicitações de edição
    $routes->post('etapas/solicitar-edicao', 'Etapas::solicitarEdicao');
    $routes->post('etapas/solicitar-exclusao', 'Etapas::solicitarExclusao');
    $routes->post('etapas/solicitar-inclusao', 'Etapas::solicitarInclusao');
    $routes->get('etapas/dados-etapa/(:num)', 'Etapas::dadosEtapa/$1');

    $routes->post('projetos/solicitar-edicao', 'Projetos::solicitarEdicao');
    $routes->post('projetos/solicitar-exclusao', 'Projetos::solicitarExclusao');
    $routes->post('projetos/solicitar-inclusao', 'Projetos::solicitarInclusao');
    $routes->get('projetos/dados-projeto/(:num)', 'Projetos::dadosProjeto/$1');

    $routes->post('planos/solicitar-edicao', 'Planos::solicitarEdicao');
    $routes->post('planos/solicitar-exclusao', 'Planos::solicitarExclusao');
    $routes->post('planos/solicitar-inclusao', 'Planos::solicitarInclusao');
    $routes->get('planos/dados-plano/(:num)', 'Planos::dadosPlano/$1');

    $routes->get('projetos/(:num)/acoes', 'Projetos::acoes/$1');
});

// Rotas administrativas
$routes->group('', ['filter' => 'group:admin,superadmin'], function ($routes) {
    // Planos
    $routes->post('planos/cadastrar', 'Planos::cadastrar');
    $routes->get('planos/editar/(:num)', 'Planos::editar/$1');
    $routes->post('planos/atualizar', 'Planos::atualizar');
    $routes->post('planos/excluir', 'Planos::excluir');

    // Projetos
    $routes->post('projetos/cadastrar/(:num)', 'Projetos::cadastrar/$1');
    $routes->get('projetos/editar/(:num)', 'Projetos::editar/$1');
    $routes->post('projetos/atualizar/(:num)', 'Projetos::atualizar/$1');
    $routes->post('projetos/excluir/(:num)', 'Projetos::excluir/$1');

    // Etapas
    $routes->post('etapas/cadastrar/(:num)', 'Etapas::cadastrar/$1');
    $routes->get('etapas/editar/(:num)', 'Etapas::editar/$1');
    $routes->post('etapas/atualizar/(:num)', 'Etapas::atualizar/$1');
    $routes->post('etapas/excluir/(:num)', 'Etapas::excluir/$1');

    // Ações


    // No grupo administrativo
    $routes->post('acoes/cadastrar/(:num)', 'Acoes::cadastrar/$1');
    $routes->get('acoes/editar/(:num)', 'Acoes::editar/$1');
    $routes->post('acoes/atualizar/(:num)', 'Acoes::atualizar/$1');
    $routes->post('acoes/excluir/(:num)', 'Acoes::excluir/$1');

    // Solicitações
    $routes->get('solicitacoes', 'Solicitacoes::index');
    $routes->get('solicitacoes/avaliar/(:num)', 'Solicitacoes::avaliar/$1');
    $routes->post('solicitacoes/processar', 'Solicitacoes::processar');

    // Histórico
    $routes->group('historico-solicitacoes', function ($routes) {
        $routes->get('/', 'HistoricoSolicitacoes::index');
        $routes->get('detalhes/(:num)', 'HistoricoSolicitacoes::detalhes/$1');
    });

    // Usuários e Grupos
    $routes->get('atribuir-grupos', 'AtribuirGrupos::index');
    $routes->post('atribuir-grupos/atribuir', 'AtribuirGrupos::atribuir');
    $routes->get('gerenciar-usuarios', 'Usuarios::index');
    $routes->post('gerenciar-usuarios/filtrar', 'Usuarios::filtrar');
    $routes->get('gerenciar-usuarios/editar/(:num)', 'Usuarios::editar/$1');
    $routes->post('gerenciar-usuarios/atualizar', 'Usuarios::atualizar');
    $routes->post('gerenciar-usuarios/alterar-grupo', 'Usuarios::alterarGrupo');
    $routes->post('gerenciar-usuarios/excluir', 'Usuarios::excluir');
});
