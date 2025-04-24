<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::home');
$routes->get('/projetos-cadastrados', 'ProjetosCadastrados::index');
$routes->get('/visao-projeto', 'VisaoProjeto::index');
