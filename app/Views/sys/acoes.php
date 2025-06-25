<!-- Importação de Modais -->
<?php echo view('components/acoes/modal-editar-acao.php'); ?>
<?php echo view('components/acoes/modal-confirmar-exclusao.php'); ?>
<?php echo view('components/acoes/modal-adicionar-acao.php'); ?>
<?php echo view('components/acoes/modal-solicitar-edicao.php'); ?>
<?php echo view('components/acoes/modal-solicitar-exclusao.php'); ?>
<?php echo view('components/acoes/modal-solicitar-inclusao.php'); ?>
<?php echo view('components/acoes/modal-ordenar-acoes.php'); ?>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('/planos') ?>">Início</a></li>
            <li class="breadcrumb-item"><a href="<?= site_url('planos') ?>"><?= $plano['nome'] ?></a></li>
            <li class="breadcrumb-item"><a href="<?= site_url("planos/{$projeto['id_plano']}/projetos") ?>"><?= $projeto['nome'] ?></a></li>

            <?php if (!isset($acessoDireto) || !$acessoDireto): ?>
                <li class="breadcrumb-item"><a href="<?= site_url("projetos/{$projeto['id']}/etapas") ?>"><?= $etapa['nome'] ?></a></li>
            <?php endif; ?>

            <li class="breadcrumb-item active" aria-current="page">Ações</li>
        </ol>
    </nav>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <?= isset($acessoDireto) && $acessoDireto ?
                "Ações do Projeto: {$projeto['nome']}" :
                "Ações da Etapa: {$etapa['nome']}"
            ?>
        </h1>
        <div>
            <a href="<?= isset($acessoDireto) && $acessoDireto ?
                            site_url("planos/{$projeto['id_plano']}/projetos") :
                            site_url("projetos/{$projeto['id']}/etapas")
                        ?>" class="btn btn-secondary btn-icon-split btn-sm">
                <span class="icon text-white-50">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span class="text">
                    <?= isset($acessoDireto) && $acessoDireto ?
                        'Voltar para Projetos' :
                        'Voltar para Etapas'
                    ?>
                </span>
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterNome">Nome</label>
                            <input type="text" class="form-control" id="filterNome" name="nome" placeholder="Filtrar por nome">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterResponsavel">Responsável</label>
                            <input type="text" class="form-control" id="filterResponsavel" name="responsavel" placeholder="Filtrar por responsável">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterStatus">Status</label>
                            <select class="form-control" id="filterStatus" name="status">
                                <option value="">Todos</option>
                                <option value="Não iniciado">Não iniciado</option>
                                <option value="Em andamento">Em andamento</option>
                                <option value="Paralisado">Paralisado</option>
                                <option value="Finalizado">Finalizado</option>
                                <option value="Atrasado">Atrasado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterData">Data</label>
                            <input type="date" class="form-control" id="filterData" name="data_filtro">
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Ações</h6>
            <div class="d-flex">
                <?php if (auth()->user()->inGroup('admin')): ?>
                    <div class="d-flex">
                        <a href="#" class="btn btn-info btn-icon-split btn-sm mr-2" data-toggle="modal" data-target="#ordenarAcoesModal">
                            <span class="icon text-white-50">
                                <i class="fas fa-sort"></i>
                            </span>
                            <span class="text">Alterar Ordem</span>
                        </a>
                        <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addAcaoModal">
                            <span class="icon text-white-50">
                                <i class="fas fa-plus"></i>
                            </span>
                            <span class="text">Incluir Ação</span>
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
                            <?php if (!isset($acessoDireto) || !$acessoDireto): ?>
                                <th>Etapa</th>
                            <?php endif; ?>
                            <th>Responsável</th>
                            <th>Entrega Estimada</th>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Status</th>
                            <th>Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Vazio - será preenchido via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts da página -->
<?php echo view('scripts/acoes.php'); ?>