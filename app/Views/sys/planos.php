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
                            <label for="filterName">Nome</label>
                            <input type="text" class="form-control" id="filterName" name="nome" placeholder="Filtrar por nome">
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
                            <th>Nome</th>
                            <th>Sigla</th>
                            <th>Descrição</th>
                            <th>Progresso</th>
                            <th>Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($planos) && !empty($planos)) : ?>
                            <?php foreach ($planos as $plano) :
                                $id = $plano['id'] . '-' . str_replace(' ', '-', strtolower($plano['nome'])); ?>
                                <tr>
                                    <td class="text-wrap align-middle"><?= $plano['nome'] ?></td>
                                    <td class="text-center align-middle"><?= $plano['sigla'] ?></td>
                                    <td class="text-wrap align-middle"><?= $plano['descricao'] ?></td>
                                    <td class="text-center align-middle">
                                        <div class="progress-container" title="<?= $plano['progresso']['texto'] ?>">
                                            <div class="progress progress-sm">
                                                <div class="progress-bar progress-bar-striped <?= $plano['progresso']['class'] ?>"
                                                    role="progressbar" style="width: <?= $plano['progresso']['percentual'] ?>%"
                                                    aria-valuenow="<?= $plano['progresso']['percentual'] ?>"
                                                    aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="progress-text"><?= $plano['progresso']['percentual'] ?>%</small>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-inline-flex">
                                            <!-- Botão Visualizar Projetos -->
                                            <a href="<?= site_url('planos/' . $plano['id'] . '/projetos') ?>" class="btn btn-info btn-sm mx-1 d-flex justify-content-center align-items-center" style="width: 32px; height: 32px;" title="Visualizar Projetos">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <?php if (auth()->user()->inGroup('admin')): ?>
                                                <!-- Botão Editar -->
                                                <button type="button" class="btn btn-primary btn-sm mx-1 d-flex justify-content-center align-items-center" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <!-- Botão Excluir -->
                                                <button type="button" class="btn btn-danger btn-sm mx-1 d-flex justify-content-center align-items-center" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Excluir">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php else: ?>

                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="text-center">Nenhum plano encontrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts da página -->
<?php echo view('scripts/planos.php'); ?>