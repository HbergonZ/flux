<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rotas de autenticação personalizadas (substituem as do Shield)
$routes->group('', function ($routes) {
    $routes->get('login', 'LoginController::loginView');
    $routes->post('login', 'LoginController::loginAction');
    $routes->get('register', '\App\Controllers\Auth\RegisterController::registerView');
    $routes->post('register', '\App\Controllers\Auth\RegisterController::registerAction');
    $routes->get('logout', 'LoginController::logoutAction');
});

// Rotas adicionais
$routes->get('testeldap', 'TesteLDAPController::index');
$routes->post('testeldap/testar', 'TesteLDAPController::testar');
$routes->get('/', 'VisaoGeral::index');

// Rotas principais hierárquicas
$routes->group('', function ($routes) {
    // Visão Geral
    $routes->get('visao-geral', 'VisaoGeral::index');
    $routes->post('visao-geral/filtrar', 'VisaoGeral::filtrar');
    $routes->get('teste', 'AtualizarStatusController::index');

    // Planos
    $routes->get('planos', 'PlanosController::index');
    $routes->post('planos/filtrar', 'PlanosController::filtrar');

    // Projetos
    $routes->get('planos/(:num)/projetos', 'ProjetosController::index/$1');
    $routes->post('projetos/filtrar/(:num)', 'ProjetosController::filtrar/$1');

    // Etapas
    $routes->get('planos/(:num)/projetos/(:num)/etapas', 'EtapasController::index/$1/$2');
    $routes->post('etapas/filtrar/(:num)', 'EtapasController::filtrar/$1');

    // Ações
    $routes->get('planos/(:num)/projetos/(:num)/acoes', 'AcoesController::index/$2/projeto');
    $routes->get('planos/(:num)/projetos/(:num)/etapas/(:num)/acoes', 'AcoesController::index/$3/etapa');

    // Rotas alternativas para compatibilidade
    $routes->get('projetos/(:num)/etapas', 'EtapasController::index/$1');
    $routes->get('projetos/(:num)/acoes', 'AcoesController::index/$1/projeto');
    $routes->get('etapas/(:num)/acoes', 'AcoesController::index/$1/etapa');

    // Solicitações
    $routes->get('minhas-solicitacoes', 'MinhasSolicitacoesController::index');
    $routes->get('minhas-solicitacoes/detalhes/(:num)', 'MinhasSolicitacoesController::detalhes/$1');

    // Dados para solicitações
    $routes->get('acoes/dados-acao/(:num)', 'AcoesController::dadosAcao/$1');
    $routes->get('etapas/dados-etapa/(:num)', 'EtapasController::dadosEtapa/$1');
    $routes->get('projetos/dados-projeto/(:num)', 'ProjetosController::dadosProjeto/$1');
    $routes->get('planos/dados-plano/(:num)', 'PlanosController::dadosPlano/$1');

    // Solicitações de edição
    $routes->post('etapas/solicitar-edicao', 'EtapasController::solicitarEdicao');
    $routes->post('etapas/solicitar-exclusao', 'EtapasController::solicitarExclusao');
    $routes->post('etapas/solicitar-inclusao', 'EtapasController::solicitarInclusao');
    $routes->get('etapas/carregar-etapas/(:num)', 'EtapasController::carregarEtapas/$1');
    $routes->get('etapas/progresso/(:num)', 'EtapasController::progresso/$1');

    $routes->post('projetos/solicitar-edicao', 'ProjetosController::solicitarEdicao');
    $routes->post('projetos/solicitar-exclusao', 'ProjetosController::solicitarExclusao');
    $routes->post('projetos/solicitar-inclusao', 'ProjetosController::solicitarInclusao');
    $routes->get('projetos/listar-evidencias/(:num)', 'ProjetosController::listarEvidencias/$1');
    $routes->get('projetos/progresso/(:num)', 'ProjetosController::progresso/$1');
    $routes->get('projetos/responsaveis/(:num)', 'ProjetosController::getResponsaveis/$1');
    $routes->get('projetos/usuarios-disponiveis/(:num)', 'ProjetosController::getUsuariosDisponiveis/$1');
    $routes->get('projetos/listar-indicadores/(:num)', 'ProjetosController::listarIndicadores/$1');


    $routes->post('planos/solicitar-edicao', 'PlanosController::solicitarEdicao');
    $routes->post('planos/solicitar-exclusao', 'PlanosController::solicitarExclusao');
    $routes->post('planos/solicitar-inclusao', 'PlanosController::solicitarInclusao');

    $routes->post('acoes/solicitar-edicao', 'AcoesController::solicitarEdicao');
    $routes->post('acoes/solicitar-exclusao', 'AcoesController::solicitarExclusao');
    $routes->post('acoes/solicitar-inclusao', 'AcoesController::solicitarInclusao');
    $routes->get('acoes/listar-evidencias/(:num)', 'AcoesController::listarEvidencias/$1');
    $routes->post('acoes/filtrar/(:num)/(:segment)', 'AcoesController::filtrar/$1/$2');
    $routes->get('acoes/get-acoes/(:num)/(:segment)', 'AcoesController::getAcoes/$1/$2');
    $routes->get('acoes/acoes-atrasadas-usuario', 'AcoesController::getAcoesAtrasadasUsuario');
    $routes->get('acoes/get-responsaveis/(:num)', 'AcoesController::getResponsaveis/$1');
    $routes->get('acoes/responsaveis/(:num)', 'AcoesController::getResponsaveis/$1');
    $routes->get('acoes/usuarios-disponiveis/(:num)', 'AcoesController::buscarUsuariosDisponiveis/$1');
    $routes->get('acoes/usuarios-disponiveis/(:num)', 'AcoesController::getUsuariosDisponiveis/$1');
    $routes->get('acoes/buscar-usuarios', 'AcoesController::buscarUsuariosParaResponsaveis');
    $routes->get('acoes/get-usuarios-disponiveis/(:num)', 'AcoesController::getUsuariosDisponiveis/$1');
});

// Rotas administrativas
$routes->group('', ['filter' => 'group:admin,superadmin'], function ($routes) {
    // Planos
    $routes->post('planos/cadastrar', 'PlanosController::cadastrar');
    $routes->get('planos/editar/(:num)', 'PlanosController::editar/$1');
    $routes->post('planos/atualizar', 'PlanosController::atualizar');
    $routes->post('planos/excluir', 'PlanosController::excluir');
    $routes->get('planos/verificar-relacionamentos/(:num)', 'PlanosController::verificarRelacionamentos/$1');

    // Projetos
    $routes->post('projetos/cadastrar/(:num)', 'ProjetosController::cadastrar/$1');
    $routes->get('projetos/editar/(:num)', 'ProjetosController::editar/$1');
    $routes->post('projetos/atualizar/(:num)', 'ProjetosController::atualizar/$1');
    $routes->post('projetos/excluir/(:num)', 'ProjetosController::excluir/$1');
    $routes->post('projetos/cadastrar-acao-direta/(:num)', 'ProjetosController::cadastrarAcaoDireta/$1');
    $routes->post('projetos/editar/(:num)', 'ProjetosController::editar/$1');
    $routes->post('projetos/adicionar-responsavel/(:num)', 'ProjetosController::adicionarResponsavel/$1');
    $routes->post('projetos/remover-responsavel/(:num)', 'ProjetosController::removerResponsavel/$1');
    $routes->post('projetos/adicionar-indicador/(:num)', 'ProjetosController::adicionarIndicador/$1');
    $routes->post('projetos/remover-indicador/(:num)/(:num)', 'ProjetosController::removerIndicador/$1/$2');


    // Etapas
    $routes->post('etapas/cadastrar/(:num)', 'EtapasController::cadastrar/$1');
    $routes->get('etapas/editar/(:num)', 'EtapasController::editar/$1');
    $routes->post('etapas/atualizar/(:num)', 'EtapasController::atualizar/$1');
    $routes->post('etapas/excluir/(:num)', 'EtapasController::excluir/$1');
    $routes->get('etapas/proxima-ordem/(:num)', 'EtapasController::proximaOrdem/$1');
    $routes->post('etapas/salvar-ordem/(:num)', 'EtapasController::salvarOrdem/$1');
    $routes->get('etapas/verificar-relacionamentos/(:num)', 'EtapasController::verificarRelacionamentos/$1');

    // Ações
    $routes->post('acoes/cadastrar/(:num)/(:segment)', 'AcoesController::cadastrar/$1/$2');
    $routes->get('acoes/proxima-ordem/(:num)/(:segment)', 'AcoesController::proximaOrdem/$1/$2');
    $routes->post('acoes/salvar-ordem/(:num)/(:segment)', 'AcoesController::salvarOrdem/$1/$2');
    $routes->get('acoes/editar/(:num)', 'AcoesController::editar/$1');
    $routes->post('acoes/atualizar/(:num)/(:segment)', 'AcoesController::atualizar/$1/$2');
    $routes->post('acoes/excluir/(:num)/(:segment)', 'AcoesController::excluir/$1/$2');
    $routes->post('acoes/adicionar-evidencia/(:num)', 'AcoesController::adicionarEvidencia/$1');
    $routes->post('acoes/remover-evidencia/(:num)', 'AcoesController::removerEvidencia/$1');
    $routes->get('acoes/gerenciar-evidencias/(:num)', 'AcoesController::gerenciarEvidencias/$1');
    $routes->get('acoes/carregar-para-ordenacao/(:num)/(:segment)', 'AcoesController::carregarAcoesParaOrdenacao/$1/$2');
    $routes->post('acoes/adicionar-responsavel', 'AcoesController::adicionarResponsavel');
    $routes->post('acoes/remover-responsavel', 'AcoesController::removerResponsavel');




    // Solicitações
    $routes->get('solicitacoes', 'SolicitacoesController::index');
    $routes->get('solicitacoes/avaliar/(:num)', 'SolicitacoesController::avaliar/$1');
    $routes->post('solicitacoes/processar', 'SolicitacoesController::processar');

    // Histórico
    $routes->group('historico-solicitacoes', function ($routes) {
        $routes->get('/', 'HistoricoSolicitacoesController::index');
        $routes->get('detalhes/(:num)', 'HistoricoSolicitacoesController::detalhes/$1');
    });

    // Usuários e Grupos
    $routes->get('atribuir-grupos', 'AtribuirGrupos::index');
    $routes->post('atribuir-grupos/atribuir', 'AtribuirGrupos::atribuir');
    $routes->get('gerenciar-usuarios', 'UsuariosController::index');
    $routes->post('gerenciar-usuarios/filtrar', 'UsuariosController::filtrar');
    $routes->get('gerenciar-usuarios/editar/(:num)', 'UsuariosController::editar/$1');
    $routes->post('gerenciar-usuarios/atualizar', 'UsuariosController::atualizar');
    $routes->post('gerenciar-usuarios/alterar-grupo', 'UsuariosController::alterarGrupo');
    $routes->post('gerenciar-usuarios/excluir', 'UsuariosController::excluir');

    $routes->post('gerenciar-usuarios/toggle-registro', 'UsuariosController::toggleRegistro');
    $routes->get('gerenciar-usuarios/status-registro', 'UsuariosController::statusRegistro');
});
