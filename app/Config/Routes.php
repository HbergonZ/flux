<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::home');
$routes->get('projetos-cadastrados', 'ProjetosCadastrados::index');
$routes->get('visao-projeto/(:num)', 'VisaoProjeto::index/$1');
$routes->post('projetos-cadastrados/cadastrar', 'ProjetosCadastrados::cadastrar');
