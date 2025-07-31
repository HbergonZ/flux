<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rotas de autenticação personalizadas (substituem as do Shield)
service('auth')->routes($routes);


$routes->group('', function ($routes) {
    $routes->GET('login', 'LoginController::loginView');
    $routes->POST('login', 'LoginController::loginAction');
    $routes->GET('register', '\App\Controllers\Auth\RegisterController::registerView');
    $routes->POST('register', '\App\Controllers\Auth\RegisterController::registerAction');
    $routes->GET('logout', 'LoginController::logoutAction');
});

// Rotas adicionais
$routes->GET('testeldap', 'TesteLDAPController::index');
$routes->POST('testeldap/testar', 'TesteLDAPController::testar');
$routes->GET('/', 'VisaoGeralController::index');

// Rotas principais hierárquicas
$routes->group('', function ($routes) {
    // Visão Geral
    $routes->GET('visao-geral', 'VisaoGeralController::index');
    $routes->POST('visao-geral/filtrar', 'VisaoGeralController::filtrar');
    $routes->GET('teste', 'AtualizarStatusController::index');

    // Planos
    $routes->GET('planos', 'PlanosController::index');
    $routes->POST('planos/carregar-dados', 'PlanosController::carregarDados');
    $routes->POST('planos/filtrar', 'PlanosController::filtrar');

    // Projetos
    $routes->GET('planos/(:num)/projetos', 'ProjetosController::index/$1');
    $routes->POST('projetos/filtrar/(:num)', 'ProjetosController::filtrar/$1');

    // Etapas
    $routes->GET('planos/(:num)/projetos/(:num)/etapas', 'EtapasController::index/$1/$2');
    $routes->match(['GET', 'POST'], 'etapas/filtrar/(:num)', 'EtapasController::filtrar/$1');

    // Ações
    $routes->GET('planos/(:num)/projetos/(:num)/acoes', 'AcoesController::index/$2/projeto');
    $routes->GET('planos/(:num)/projetos/(:num)/etapas/(:num)/acoes', 'AcoesController::index/$3/etapa');

    // Rotas alternativas para compatibilidade
    $routes->GET('projetos/(:num)/etapas', 'EtapasController::index/$1');
    $routes->GET('projetos/(:num)/acoes', 'AcoesController::index/$1/projeto');
    $routes->GET('etapas/(:num)/acoes', 'AcoesController::index/$1/etapa');

    // Solicitações
    $routes->GET('minhas-solicitacoes', 'MinhasSolicitacoesController::index');
    $routes->GET('minhas-solicitacoes/detalhes/(:num)', 'MinhasSolicitacoesController::detalhes/$1');

    // Dados para solicitações
    $routes->GET('acoes/dados-acao/(:num)', 'AcoesController::dadosAcao/$1');
    $routes->GET('etapas/dados-etapa/(:num)', 'EtapasController::dadosEtapa/$1');
    $routes->GET('projetos/dados-projeto/(:num)', 'ProjetosController::dadosProjeto/$1');
    $routes->GET('planos/dados-plano/(:num)', 'PlanosController::dadosPlano/$1');

    // Solicitações de edição
    $routes->POST('etapas/solicitar-edicao', 'EtapasController::solicitarEdicao');
    $routes->POST('etapas/solicitar-exclusao', 'EtapasController::solicitarExclusao');
    $routes->POST('etapas/solicitar-inclusao', 'EtapasController::solicitarInclusao');
    $routes->GET('etapas/carregar-etapas/(:num)', 'EtapasController::carregarEtapas/$1');
    $routes->GET('etapas/progresso/(:num)', 'EtapasController::progresso/$1');

    $routes->POST('projetos/solicitar-edicao', 'ProjetosController::solicitarEdicao');
    $routes->POST('projetos/solicitar-exclusao', 'ProjetosController::solicitarExclusao');
    $routes->POST('projetos/solicitar-inclusao', 'ProjetosController::solicitarInclusao');
    $routes->GET('projetos/listar-evidencias/(:num)', 'ProjetosController::listarEvidencias/$1');
    $routes->GET('projetos/progresso/(:num)', 'ProjetosController::progresso/$1');
    $routes->GET('projetos/responsaveis/(:num)', 'ProjetosController::GETResponsaveis/$1');
    $routes->GET('projetos/usuarios-disponiveis/(:num)', 'ProjetosController::GETUsuariosDisponiveis/$1');
    $routes->GET('projetos/listar-indicadores/(:num)', 'ProjetosController::listarIndicadores/$1');


    $routes->POST('planos/solicitar-edicao', 'PlanosController::solicitarEdicao');
    $routes->POST('planos/solicitar-exclusao', 'PlanosController::solicitarExclusao');
    $routes->POST('planos/solicitar-inclusao', 'PlanosController::solicitarInclusao');

    $routes->POST('acoes/solicitar-edicao', 'AcoesController::solicitarEdicao');
    $routes->POST('acoes/solicitar-exclusao', 'AcoesController::solicitarExclusao');
    $routes->POST('acoes/solicitar-inclusao', 'AcoesController::solicitarInclusao');
    $routes->GET('acoes/listar-evidencias/(:num)', 'AcoesController::listarEvidencias/$1');
    $routes->POST('acoes/filtrar/(:num)/(:segment)', 'AcoesController::filtrar/$1/$2');
    $routes->GET('acoes/GET-acoes/(:num)/(:segment)', 'AcoesController::GETAcoes/$1/$2');
    $routes->GET('acoes/acoes-atrasadas-usuario', 'AcoesController::GETAcoesAtrasadasUsuario');
    $routes->GET('acoes/GET-responsaveis/(:num)', 'AcoesController::GETResponsaveis/$1');
    $routes->GET('acoes/responsaveis/(:num)', 'AcoesController::GETResponsaveis/$1');
    $routes->GET('acoes/usuarios-disponiveis/(:num)', 'AcoesController::buscarUsuariosDisponiveis/$1');
    $routes->GET('acoes/usuarios-disponiveis/(:num)', 'AcoesController::GETUsuariosDisponiveis/$1');
    $routes->GET('acoes/buscar-usuarios', 'AcoesController::buscarUsuariosParaResponsaveis');
    $routes->GET('acoes/GET-usuarios-disponiveis/(:num)', 'AcoesController::GETUsuariosDisponiveis/$1');

    $routes->GET('atualizacoes', 'AtualizacoesController::index');
});

// Rotas administrativas
$routes->group('', ['filter' => 'group:admin,superadmin'], function ($routes) {
    // Planos
    $routes->POST('planos/cadastrar', 'PlanosController::cadastrar');
    $routes->GET('planos/editar/(:num)', 'PlanosController::editar/$1');
    $routes->POST('planos/atualizar', 'PlanosController::atualizar');
    $routes->POST('planos/excluir', 'PlanosController::excluir');
    $routes->GET('planos/verificar-relacionamentos/(:num)', 'PlanosController::verificarRelacionamentos/$1');

    // Projetos
    $routes->POST('projetos/cadastrar/(:num)', 'ProjetosController::cadastrar/$1');
    $routes->GET('projetos/editar/(:num)', 'ProjetosController::editar/$1');
    $routes->POST('projetos/atualizar/(:num)', 'ProjetosController::atualizar/$1');
    $routes->POST('projetos/excluir/(:num)', 'ProjetosController::excluir/$1');
    $routes->POST('projetos/cadastrar-acao-direta/(:num)', 'ProjetosController::cadastrarAcaoDireta/$1');
    $routes->POST('projetos/editar/(:num)', 'ProjetosController::editar/$1');
    $routes->POST('projetos/adicionar-responsavel/(:num)', 'ProjetosController::adicionarResponsavel/$1');
    $routes->POST('projetos/remover-responsavel/(:num)', 'ProjetosController::removerResponsavel/$1');
    $routes->POST('projetos/adicionar-indicador/(:num)', 'ProjetosController::adicionarIndicador/$1');
    $routes->POST('projetos/remover-indicador/(:num)/(:num)', 'ProjetosController::removerIndicador/$1/$2');


    // Etapas
    $routes->POST('etapas/cadastrar/(:num)', 'EtapasController::cadastrar/$1');
    $routes->GET('etapas/editar/(:num)', 'EtapasController::editar/$1');
    $routes->POST('etapas/atualizar/(:num)', 'EtapasController::atualizar/$1');
    $routes->POST('etapas/excluir/(:num)', 'EtapasController::excluir/$1');
    $routes->GET('etapas/proxima-ordem/(:num)', 'EtapasController::proximaOrdem/$1');
    $routes->POST('etapas/salvar-ordem/(:num)', 'EtapasController::salvarOrdem/$1');
    $routes->GET('etapas/verificar-relacionamentos/(:num)', 'EtapasController::verificarRelacionamentos/$1');

    // Ações
    $routes->POST('acoes/cadastrar/(:num)/(:segment)', 'AcoesController::cadastrar/$1/$2');
    $routes->GET('acoes/proxima-ordem/(:num)/(:segment)', 'AcoesController::proximaOrdem/$1/$2');
    $routes->POST('acoes/salvar-ordem/(:num)/(:segment)', 'AcoesController::salvarOrdem/$1/$2');
    $routes->GET('acoes/editar/(:num)', 'AcoesController::editar/$1');
    $routes->POST('acoes/atualizar/(:num)/(:segment)', 'AcoesController::atualizar/$1/$2');
    $routes->POST('acoes/excluir/(:num)/(:segment)', 'AcoesController::excluir/$1/$2');
    $routes->POST('acoes/adicionar-evidencia/(:num)', 'AcoesController::adicionarEvidencia/$1');
    $routes->POST('acoes/remover-evidencia/(:num)', 'AcoesController::removerEvidencia/$1');
    $routes->GET('acoes/gerenciar-evidencias/(:num)', 'AcoesController::gerenciarEvidencias/$1');
    $routes->GET('acoes/carregar-para-ordenacao/(:num)/(:segment)', 'AcoesController::carregarAcoesParaOrdenacao/$1/$2');
    $routes->POST('acoes/adicionar-responsavel', 'AcoesController::adicionarResponsavel');
    $routes->POST('acoes/remover-responsavel', 'AcoesController::removerResponsavel');




    // Solicitações
    $routes->GET('solicitacoes', 'SolicitacoesController::index');
    $routes->GET('solicitacoes/avaliar/(:num)', 'SolicitacoesController::avaliar/$1');
    $routes->POST('solicitacoes/processar', 'SolicitacoesController::processar');

    // Histórico
    $routes->group('historico-solicitacoes', function ($routes) {
        $routes->GET('/', 'HistoricoSolicitacoesController::index');
        $routes->GET('detalhes/(:num)', 'HistoricoSolicitacoesController::detalhes/$1');
    });

    // Usuários e Grupos
    $routes->GET('atribuir-grupos', 'AtribuirGrupos::index');
    $routes->POST('atribuir-grupos/atribuir', 'AtribuirGrupos::atribuir');
    $routes->GET('gerenciar-usuarios', 'UsuariosController::index');
    $routes->POST('gerenciar-usuarios/filtrar', 'UsuariosController::filtrar');
    $routes->GET('gerenciar-usuarios/editar/(:num)', 'UsuariosController::editar/$1');
    $routes->POST('gerenciar-usuarios/atualizar', 'UsuariosController::atualizar');
    $routes->POST('gerenciar-usuarios/alterar-grupo', 'UsuariosController::alterarGrupo');
    $routes->POST('gerenciar-usuarios/excluir', 'UsuariosController::excluir');

    $routes->POST('gerenciar-usuarios/toggle-registro', 'UsuariosController::toggleRegistro');
    $routes->GET('gerenciar-usuarios/status-registro', 'UsuariosController::statusRegistro');
});
