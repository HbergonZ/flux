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
    $routes->get('visao-projeto/dados-etapa/(:num)/(:num)', 'VisaoProjeto::dadosEtapa/$1/$2');
    $routes->post('visao-projeto/solicitar-edicao', 'VisaoProjeto::solicitarEdicao');

    // Rotas para gerenciamento de solicitações
    $routes->get('solicitacoes-edicao', 'SolicitacoesEdicao::index');
    $routes->get('solicitacoes-edicao/detalhes/(:num)', 'SolicitacoesEdicao::detalhes/$1');
    $routes->post('solicitacoes-edicao/processar/(:num)', 'SolicitacoesEdicao::processar/$1');
});
