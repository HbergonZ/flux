<!-- Importação de Modais -->
<?php echo view('components/projetos/modal-editar-projeto.php'); ?>
<?php echo view('components/projetos/modal-confirmar-exclusao.php'); ?>
<?php echo view('components/projetos/modal-adicionar-projeto.php'); ?>


<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Projetos Cadastrados</h1>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterName">Nome</label>
                            <input type="text" class="form-control" id="filterName" name="nome" placeholder="Filtrar por nome">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterStatus">Status</label>
                            <select class="form-control" id="filterStatus" name="status">
                                <option value="">Todos</option>
                                <option value="Em andamento">Em andamento</option>
                                <option value="Finalizado">Finalizado</option>
                                <option value="Paralisado">Paralisado</option>
                                <option value="Não iniciado">Não iniciado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterStartDate">Data Inicial</label>
                            <input type="date" class="form-control" id="filterStartDate" name="data_inicio">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterEndDate">Data Final</label>
                            <input type="date" class="form-control" id="filterEndDate" name="data_fim">
                        </div>
                    </div>
                </div>

                <!-- Linha dos botões alinhados à direita -->
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
            <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addProjectModal">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Incluir Projeto</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Nome</th>
                            <th>Objetivo</th>
                            <th>Perspectiva Estratégica</th>
                            <th>Interessados</th>
                            <th>Status</th>
                            <th>Data de Início</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($projetos) && !empty($projetos)) : ?>
                            <?php foreach ($projetos as $projeto) :
                                $id = $projeto['id'] . '-' . str_replace(' ', '-', strtolower($projeto['nome'])); ?>
                                <tr>
                                    <td class="text-wrap"><?= $projeto['nome'] ?></td>
                                    <td class="text-wrap"><?= $projeto['objetivo'] ?></td>
                                    <td class="text-wrap"><?= $projeto['perspectiva_estrategica'] ?></td>
                                    <td class="text-wrap"><?= $projeto['interessados'] ?></td>
                                    <td class="text-center">
                                        <?php
                                        $badge_class = '';
                                        switch ($projeto['status']) {
                                            case 'Em andamento':
                                                $badge_class = 'badge-primary';
                                                break;
                                            case 'Não iniciado':
                                                $badge_class = 'badge-secondary';
                                                break;
                                            case 'Finalizado':
                                                $badge_class = 'badge-success';
                                                break;
                                            case 'Paralisado':
                                                $badge_class = 'badge-warning';
                                                break;
                                            default:
                                                $badge_class = 'badge-light';
                                        }
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= $projeto['status'] ?></span>
                                    </td>
                                    <td class="text-center"><?= !empty($projeto['data_publicacao']) ? date('d/m/Y', strtotime($projeto['data_publicacao'])) : '' ?></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex">
                                            <!-- Botão Visualizar -->
                                            <a href="<?= site_url('visao-projeto/' . $projeto['id']) ?>" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar">
                                                <i class="fas fa-eye"></i>
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
                                <td colspan="7" class="text-center">Nenhum projeto encontrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->


<!-- Scripts da página -->
<?php echo view('scripts/projetos.php'); ?>