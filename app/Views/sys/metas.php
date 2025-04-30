<!-- Importação de Modais -->
<?php echo view('components/metas/modal-editar-meta.php'); ?>
<?php echo view('components/metas/modal-confirmar-exclusao.php'); ?>
<?php echo view('components/metas/modal-adicionar-meta.php'); ?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Metas da Ação: <?= $acao['acao'] ?></h1>
        <a href="<?= site_url("acoes/{$acao['id_plano']}") ?>" class="btn btn-secondary btn-icon-split btn-sm">
            <span class="icon text-white-50">
                <i class="fas fa-arrow-left"></i>
            </span>
            <span class="text">Voltar para Ações</span>
        </a>
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Metas</h6>
            <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addMetaModal">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Incluir Meta</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($metas) && !empty($metas)) : ?>
                            <?php foreach ($metas as $meta) :
                                $id = $meta['id'] . '-' . str_replace(' ', '-', strtolower($meta['nome'])); ?>
                                <tr>
                                    <td class="text-wrap"><?= $meta['nome'] ?></td>
                                    <!-- Na coluna de ações, substitua o conteúdo atual por: -->
                                    <td class="text-center">
                                        <div class="d-inline-flex">
                                            <!-- Botão Visualizar Etapas -->
                                            <a href="<?= site_url('etapas/meta/' . $meta['id']) ?>" class="btn btn-secondary btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar Etapas">
                                                <i class="fas fa-tasks"></i>
                                            </a>

                                            <!-- Botão Editar -->
                                            <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- Botão Excluir -->
                                            <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="2" class="text-center">Nenhuma meta encontrada</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts da página -->
<?php echo view('scripts/metas.php'); ?>