<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'ProjetosCadastrados::index');
$routes->get('projetos-cadastrados', 'ProjetosCadastrados::index');
$routes->post('projetos-cadastrados/cadastrar', 'ProjetosCadastrados::cadastrar');
$routes->get('projetos-cadastrados/editar/(:num)', 'ProjetosCadastrados::editar/$1');
$routes->post('projetos-cadastrados/atualizar', 'ProjetosCadastrados::atualizar');
$routes->post('projetos-cadastrados/excluir', 'ProjetosCadastrados::excluir');
$routes->post('projetos-cadastrados/filtrar', 'ProjetosCadastrados::filtrar');
$routes->group('', function ($routes) {
    // Rotas para visualização de projeto
    $routes->get('visao-projeto/(:num)', 'VisaoProjeto::index/$1');
    $routes->post('visao-projeto/filtrar/(:num)', 'VisaoProjeto::filtrar/$1');

    // Novas rotas para solicitação de edição
    $routes->get('visao-projeto/dados-etapa/(:num)/(:any)', 'VisaoProjeto::dadosEtapa/$1/$2');
    $routes->post('visao-projeto/solicitar-edicao', 'VisaoProjeto::solicitarEdicao');

    // Rotas para gerenciamento de solicitações
    $routes->get('solicitacoes-edicao', 'SolicitacoesEdicao::index');
    $routes->get('solicitacoes-edicao/detalhes/(:num)', 'SolicitacoesEdicao::detalhes/$1', ['as' => 'detalhes_solicitacao']);
    $routes->post('solicitacoes-edicao/processar/(:num)', 'SolicitacoesEdicao::processar/$1');
});

$routes->group('historico-solicitacoes', function ($routes) {
    $routes->get('/', 'HistoricoSolicitacoes::index');
    $routes->get('detalhes/(:num)', 'HistoricoSolicitacoes::detalhes/$1');
});

$routes->get('minhas-solicitacoes', 'MinhasSolicitacoes::index');
$routes->get('minhas-solicitacoes/detalhes/(:num)', 'MinhasSolicitacoes::detalhes/$1');

$routes->get('meus-projetos', 'MeusProjetos::index');
$routes->post('meus-projetos/filtrar', 'MeusProjetos::filtrar');


$routes->get('planos', 'Planos::index');
$routes->post('planos/cadastrar', 'Planos::cadastrar');
$routes->get('planos/editar/(:num)', 'Planos::editar/$1');
$routes->post('planos/atualizar', 'Planos::atualizar');
$routes->post('planos/excluir', 'Planos::excluir');
$routes->post('planos/filtrar', 'Planos::filtrar');


$routes->get('acoes/(:num)', 'Acoes::index/$1');
$routes->post('acoes/cadastrar/(:num)', 'Acoes::cadastrar/$1');
$routes->get('acoes/editar/(:num)', 'Acoes::editar/$1');
$routes->post('acoes/atualizar/(:num)', 'Acoes::atualizar/$1');
$routes->post('acoes/excluir/(:num)', 'Acoes::excluir/$1');
$routes->post('acoes/filtrar/(:num)', 'Acoes::filtrar/$1');

$routes->get('metas/(:num)', 'Metas::index/$1');
$routes->post('metas/cadastrar/(:num)', 'Metas::cadastrar/$1');
$routes->get('metas/editar/(:num)', 'Metas::editar/$1');
$routes->post('metas/atualizar/(:num)', 'Metas::atualizar/$1');
$routes->post('metas/excluir/(:num)', 'Metas::excluir/$1');
$routes->post('metas/filtrar/(:num)', 'Metas::filtrar/$1');

// Para acessar via ações
$routes->get('etapas/(:num)', 'Etapas::index/$1');
$routes->post('etapas/cadastrar/acao/(:num)', 'Etapas::cadastrar/acao/$1');
$routes->get('etapas/editar/(:num)', 'Etapas::editar/$1');
$routes->post('etapas/atualizar/acao/(:num)', 'Etapas::atualizar/acao/$1');
$routes->post('etapas/excluir/acao/(:num)', 'Etapas::excluir/acao/$1');
$routes->post('etapas/filtrar/acao/(:num)', 'Etapas::filtrar/acao/$1');

// Para acessar via metas
$routes->get('etapas/meta/(:num)', 'Etapas::meta/$1');
$routes->post('etapas/cadastrar/meta/(:num)', 'Etapas::cadastrar/meta/$1');
$routes->post('etapas/atualizar/meta/(:num)', 'Etapas::atualizar/meta/$1');
$routes->post('etapas/excluir/meta/(:num)', 'Etapas::excluir/meta/$1');
$routes->post('etapas/filtrar/meta/(:num)', 'Etapas::filtrar/meta/$1');


$routes->get('visao-geral', 'VisaoGeral::index');
$routes->post('visao-geral/filtrar', 'VisaoGeral::filtrar');
