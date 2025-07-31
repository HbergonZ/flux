<!-- Importação de Modais -->
<?php echo view('components/planos/modal-editar-plano.php'); ?>
<?php echo view('components/planos/modal-confirmar-exclusao.php'); ?>
<?php echo view('components/planos/modal-adicionar-plano.php'); ?>

<div class="container-fluid">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('/planos') ?>">Visão Detalhada</a></li>
            <li class="breadcrumb-item active" aria-current="page">Planos</li>
        </ol>
    </nav>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Planos</h1>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filterName">Plano</label>
                            <input type="text" class="form-control" id="filterName" name="nome" placeholder="Filtrar por plano">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filterSigla">Sigla</label>
                            <input type="text" class="form-control" id="filterSigla" name="sigla" placeholder="Filtrar por sigla">
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Planos</h6>
            <?php if (auth()->user()->inGroup('admin')): ?>
                <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addPlanoModal">
                    <span class="icon text-white-50">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span class="text">Incluir Plano</span>
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Plano</th>
                            <th>Sigla</th>
                            <th>Descrição</th>
                            <th>Progresso</th>
                            <th>Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Os dados serão carregados via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts da página -->
<?php echo view('scripts/planos.php'); ?>