<!-- Importação de Modais -->
<?php echo view('components/etapas/modal-editar-etapa.php'); ?>
<?php echo view('components/etapas/modal-confirmar-exclusao.php'); ?>
<?php echo view('components/etapas/modal-adicionar-etapa.php'); ?>
<?php echo view('components/etapas/modal-solicitar-edicao.php'); ?>
<?php echo view('components/etapas/modal-solicitar-exclusao.php'); ?>
<?php echo view('components/etapas/modal-solicitar-inclusao.php'); ?>
<?php echo view('components/etapas/modal-ordenar-etapas.php'); ?>

<div class="container-fluid">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('/planos') ?>">Visão Detalhada</a></li>
            <li class="breadcrumb-item"><a href="<?= site_url("planos/{$plano['id']}") ?>"><?= $plano['nome'] ?></a></li>
            <li class="breadcrumb-item"><a href="<?= site_url("planos/{$plano['id']}/projetos/") ?>"><?= $projeto['nome'] ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Etapas</li>
        </ol>
    </nav>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Etapas do Projeto: <?= $projeto['nome'] ?></h1>
        <div>
            <a href="<?= site_url("planos/{$plano['id']}/projetos/") ?>" class="btn btn-secondary btn-icon-split btn-sm">
                <span class="icon text-white-50">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span class="text">Voltar para Projeto</span>
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="filterNome">Nome</label>
                            <input type="text" class="form-control" id="filterNome" name="nome" placeholder="Filtrar por nome">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12 text-right">
                        <button type="button" id="btnLimparFiltros" class="btn btn-secondary btn-icon-split btn-sm">
                            <span class="icon text-white-50">
                                <i class="fas fa-broom"></i>
                            </span>
                            <span class="text">Limpar</span>
                        </button>
                        <button type="submit" class="btn btn-primary btn-icon-split btn-sm mr-2">
                            <span class="icon text-white-50">
                                <i class="fas fa-filter"></i>
                            </span>
                            <span class="text">Filtrar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Etapas</h6>
            <div class="d-flex">
                <?php if (auth()->user()->inGroup('admin')): ?>
                    <div class="d-flex">
                        <a href="#" class="btn btn-info btn-icon-split btn-sm mr-2" data-toggle="modal" data-target="#ordenarEtapasModal">
                            <span class="icon text-white-50">
                                <i class="fas fa-sort"></i>
                            </span>
                            <span class="text">Alterar Ordem</span>
                        </a>
                        <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addEtapaModal">
                            <span class="icon text-white-50">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span class="text">Incluir Etapa</span>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#solicitarInclusaoModal">
                        <span class="icon text-white-50">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text">Solicitar Inclusão</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Ordem</th>
                            <th>Nome</th>
                            <th>Data Criação</th>
                            <th>Data Atualização</th>
                            <th>Progresso</th>
                            <th>Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- O conteúdo será carregado via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts da página -->
<?php echo view('scripts/etapas.php'); ?>