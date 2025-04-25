<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'ProjetosCadastrados::index');
$routes->get('projetos-cadastrados', 'ProjetosCadastrados::index');
$routes->get('visao-projeto/(:num)', 'VisaoProjeto::index/$1');
$routes->post('projetos-cadastrados/cadastrar', 'ProjetosCadastrados::cadastrar');
$routes->get('projetos-cadastrados/editar/(:num)', 'ProjetosCadastrados::editar/$1');
$routes->post('projetos-cadastrados/atualizar', 'ProjetosCadastrados::atualizar');
$routes->post('projetos-cadastrados/excluir', 'ProjetosCadastrados::excluir');
$routes->post('projetos-cadastrados/filtrar', 'ProjetosCadastrados::filtrar');
$routes->post('visao-projeto/filtrar/(:num)', 'VisaoProjeto::filtrar/$1');
