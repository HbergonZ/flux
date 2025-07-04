<?php echo view('components/projetos/modal-editar-projeto.php'); ?>
<?php echo view('components/projetos/modal-confirmar-exclusao.php'); ?>
<?php echo view('components/projetos/modal-adicionar-projeto.php'); ?>
<?php echo view('components/projetos/modal-solicitar-edicao.php'); ?>
<?php echo view('components/projetos/modal-solicitar-exclusao.php'); ?>
<?php echo view('components/projetos/modal-solicitar-inclusao.php'); ?>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('/planos') ?>">Início</a></li>
            <li class="breadcrumb-item"><a href="<?= site_url('planos') ?>"><?= $plano['nome'] ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Projetos</li>
        </ol>
    </nav>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Projetos do <?= $plano['nome'] ?></h1>
        <a href="<?= site_url('planos') ?>" class="btn btn-secondary btn-icon-split btn-sm">
            <span class="icon text-white-50">
                <i class="fas fa-arrow-left"></i>
            </span>
            <span class="text">Voltar para Planos</span>
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterProjeto">Projeto</label>
                            <select class="form-control" id="filterProjeto" name="nome">
                                <option value="">Todos os projetos</option>
                                <?php foreach ($projetos as $projeto): ?>
                                    <option value="<?= $projeto['nome'] ?>"><?= $projeto['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterProjetoVinculado">Projeto Vinculado</label>
                            <select class="form-control" id="filterProjetoVinculado" name="projeto_vinculado">
                                <option value="">Todos os projetos vinculados</option>
                                <?php foreach ($projetos as $projeto): ?>
                                    <?php if (!empty($projeto['projeto_vinculado'])): ?>
                                        <option value="<?= $projeto['projeto_vinculado'] ?>"><?= $projeto['projeto_vinculado'] ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterEixo">Eixo</label>
                            <select class="form-control" id="filterEixo" name="id_eixo">
                                <option value="">Todos os eixos</option>
                                <?php foreach ($eixos as $eixo): ?>
                                    <option value="<?= $eixo['id'] ?>"><?= $eixo['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Projetos</h6>
            <?php if (auth()->user()->inGroup('admin')): ?>
                <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addProjetoModal">
                    <span class="icon text-white-50">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span class="text">Incluir Projeto</span>
                </a>
            <?php else: ?>
                <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#solicitarInclusaoModal">
                    <span class="icon text-white-50">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span class="text">Solicitar Inclusão</span>
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Identificador</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Metas</th>
                            <th>Eixo</th>
                            <th>Responsáveis</th>
                            <th>Data Fim</th>
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
<?php echo view('scripts/projetos.php'); ?>