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
    // Visão Geral
    $routes->get('visao-geral', 'VisaoGeral::index');
    $routes->post('visao-geral/filtrar', 'VisaoGeral::filtrar');

    // Planos
    $routes->get('planos', 'Planos::index');
    $routes->post('planos/filtrar', 'Planos::filtrar');

    // Projetos
    $routes->get('planos/(:num)/projetos', 'Projetos::index/$1');
    $routes->post('projetos/filtrar/(:num)', 'Projetos::filtrar/$1');

    // Etapas
    $routes->get('planos/(:num)/projetos/(:num)/etapas', 'Etapas::index/$1/$2');
    $routes->post('etapas/filtrar/(:num)', 'Etapas::filtrar/$1');

    // Ações
    $routes->get('planos/(:num)/projetos/(:num)/acoes', 'Acoes::index/$2/projeto');
    $routes->get('planos/(:num)/projetos/(:num)/etapas/(:num)/acoes', 'Acoes::index/$3/etapa');

    // Rotas alternativas para compatibilidade
    $routes->get('projetos/(:num)/etapas', 'Etapas::index/$1');
    $routes->get('projetos/(:num)/acoes', 'Acoes::index/$1/projeto');
    $routes->get('etapas/(:num)/acoes', 'Acoes::index/$1/etapa');

    // Solicitações
    $routes->get('minhas-solicitacoes', 'MinhasSolicitacoes::index');
    $routes->get('minhas-solicitacoes/detalhes/(:num)', 'MinhasSolicitacoes::detalhes/$1');

    // Dados para solicitações
    $routes->get('acoes/dados-acao/(:num)', 'Acoes::dadosAcao/$1');
    $routes->get('etapas/dados-etapa/(:num)', 'Etapas::dadosEtapa/$1');
    $routes->get('projetos/dados-projeto/(:num)', 'Projetos::dadosProjeto/$1');
    $routes->get('planos/dados-plano/(:num)', 'Planos::dadosPlano/$1');

    // Solicitações de edição
    $routes->post('etapas/solicitar-edicao', 'Etapas::solicitarEdicao');
    $routes->post('etapas/solicitar-exclusao', 'Etapas::solicitarExclusao');
    $routes->post('etapas/solicitar-inclusao', 'Etapas::solicitarInclusao');

    $routes->post('projetos/solicitar-edicao', 'Projetos::solicitarEdicao');
    $routes->post('projetos/solicitar-exclusao', 'Projetos::solicitarExclusao');
    $routes->post('projetos/solicitar-inclusao', 'Projetos::solicitarInclusao');
    $routes->get('projetos/listar-evidencias/(:num)', 'Projetos::listarEvidencias/$1');
    $routes->get('projetos/progresso/(:num)', 'Projetos::progresso/$1');
    $routes->get('projetos/responsaveis/(:num)', 'Projetos::getResponsaveis/$1');
    $routes->get('projetos/usuarios-disponiveis/(:num)', 'Projetos::getUsuariosDisponiveis/$1');
    $routes->get('projetos/listar-indicadores/(:num)', 'Projetos::listarIndicadores/$1');


    $routes->post('planos/solicitar-edicao', 'Planos::solicitarEdicao');
    $routes->post('planos/solicitar-exclusao', 'Planos::solicitarExclusao');
    $routes->post('planos/solicitar-inclusao', 'Planos::solicitarInclusao');

    $routes->post('acoes/solicitar-edicao', 'Acoes::solicitarEdicao');
    $routes->post('acoes/solicitar-exclusao', 'Acoes::solicitarExclusao');
    $routes->post('acoes/solicitar-inclusao', 'Acoes::solicitarInclusao');
    $routes->get('acoes/listar-evidencias/(:num)', 'Acoes::listarEvidencias/$1');
    $routes->post('acoes/filtrar/(:num)/(:segment)', 'Acoes::filtrar/$1/$2');
    $routes->get('acoes/get-acoes/(:num)/(:segment)', 'Acoes::getAcoes/$1/$2');
    $routes->get('acoes/acoes-atrasadas-usuario', 'Acoes::getAcoesAtrasadasUsuario');
    $routes->get('acoes/get-responsaveis/(:num)', 'Acoes::getResponsaveis/$1');
});

// Rotas administrativas
$routes->group('', ['filter' => 'group:admin,superadmin'], function ($routes) {
    // Planos
    $routes->post('planos/cadastrar', 'Planos::cadastrar');
    $routes->get('planos/editar/(:num)', 'Planos::editar/$1');
    $routes->post('planos/atualizar', 'Planos::atualizar');
    $routes->post('planos/excluir', 'Planos::excluir');
    $routes->get('planos/verificar-relacionamentos/(:num)', 'Planos::verificarRelacionamentos/$1');

    // Projetos
    $routes->post('projetos/cadastrar/(:num)', 'Projetos::cadastrar/$1');
    $routes->get('projetos/editar/(:num)', 'Projetos::editar/$1');
    $routes->post('projetos/atualizar/(:num)', 'Projetos::atualizar/$1');
    $routes->post('projetos/excluir/(:num)', 'Projetos::excluir/$1');
    $routes->post('projetos/cadastrar-acao-direta/(:num)', 'Projetos::cadastrarAcaoDireta/$1');
    $routes->post('projetos/editar/(:num)', 'Projetos::editar/$1');
    $routes->post('projetos/adicionar-responsavel/(:num)', 'Projetos::adicionarResponsavel/$1');
    $routes->post('projetos/remover-responsavel/(:num)', 'Projetos::removerResponsavel/$1');
    $routes->post('projetos/adicionar-indicador/(:num)', 'Projetos::adicionarIndicador/$1');
    $routes->post('projetos/remover-indicador/(:num)/(:num)', 'Projetos::removerIndicador/$1/$2');


    // Etapas
    $routes->post('etapas/cadastrar/(:num)', 'Etapas::cadastrar/$1');
    $routes->get('etapas/editar/(:num)', 'Etapas::editar/$1');
    $routes->post('etapas/atualizar/(:num)', 'Etapas::atualizar/$1');
    $routes->post('etapas/excluir/(:num)', 'Etapas::excluir/$1');
    $routes->get('etapas/proxima-ordem/(:num)', 'Etapas::proximaOrdem/$1');
    $routes->post('etapas/salvar-ordem/(:num)', 'Etapas::salvarOrdem/$1');
    $routes->get('etapas/verificar-relacionamentos/(:num)', 'Etapas::verificarRelacionamentos/$1');

    // Ações
    $routes->post('acoes/cadastrar/(:num)/(:segment)', 'Acoes::cadastrar/$1/$2');
    $routes->get('acoes/proxima-ordem/(:num)/(:segment)', 'Acoes::proximaOrdem/$1/$2');
    $routes->post('acoes/salvar-ordem/(:num)/(:segment)', 'Acoes::salvarOrdem/$1/$2');
    $routes->get('acoes/editar/(:num)', 'Acoes::editar/$1');
    $routes->post('acoes/atualizar/(:num)/(:segment)', 'Acoes::atualizar/$1/$2');
    $routes->post('acoes/excluir/(:num)/(:segment)', 'Acoes::excluir/$1/$2');
    $routes->post('acoes/adicionar-evidencia/(:num)', 'Acoes::adicionarEvidencia/$1');
    $routes->post('acoes/remover-evidencia/(:num)', 'Acoes::removerEvidencia/$1');
    $routes->get('acoes/gerenciar-evidencias/(:num)', 'Acoes::gerenciarEvidencias/$1');
    $routes->get('acoes/carregar-para-ordenacao/(:num)/(:segment)', 'Acoes::carregarAcoesParaOrdenacao/$1/$2');

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
