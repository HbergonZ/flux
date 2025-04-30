<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Visão Geral</h1>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterPlano">Plano</label>
                            <input type="text" class="form-control" id="filterPlano" name="plano" placeholder="Filtrar por plano">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterAcao">Ação</label>
                            <input type="text" class="form-control" id="filterAcao" name="acao" placeholder="Filtrar por ação">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterMeta">Meta</label>
                            <input type="text" class="form-control" id="filterMeta" name="meta" placeholder="Filtrar por meta">
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
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterEtapa">Etapa</label>
                            <input type="text" class="form-control" id="filterEtapa" name="etapa" placeholder="Filtrar por etapa">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterCoordenacao">Coordenação</label>
                            <input type="text" class="form-control" id="filterCoordenacao" name="coordenacao" placeholder="Filtrar por coordenação">
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
                            <label for="filterStartDate">Data Inicial</label>
                            <input type="date" class="form-control" id="filterStartDate" name="data_inicio">
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
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Visão Geral dos Projetos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Plano</th>
                            <th>Ação</th>
                            <th>Meta</th>
                            <th>Etapa</th>
                            <th>Coordenação</th>
                            <th>Responsável</th>
                            <th>Status</th>
                            <th>Início</th>
                            <th>Término</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($dados) && !empty($dados)) : ?>
                            <?php foreach ($dados as $registro) : ?>
                                <tr>
                                    <td class="text-wrap"><?= $registro['plano'] ?></td>
                                    <td class="text-wrap"><?= $registro['acao'] ?></td>
                                    <td class="text-wrap"><?= $registro['meta'] ?></td>
                                    <td class="text-wrap"><?= $registro['etapa'] ?></td>
                                    <td class="text-wrap"><?= $registro['coordenacao'] ?></td>
                                    <td class="text-wrap"><?= $registro['responsavel_etapa'] ?></td>
                                    <td class="text-center">
                                        <?php
                                        $badge_class = '';
                                        switch ($registro['status']) {
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
                                        <span class="badge <?= $badge_class ?>"><?= $registro['status'] ?></span>
                                    </td>
                                    <td class="text-center"><?= !empty($registro['data_inicio']) ? date('d/m/Y', strtotime($registro['data_inicio'])) : '' ?></td>
                                    <td class="text-center"><?= !empty($registro['data_fim']) ? date('d/m/Y', strtotime($registro['data_fim'])) : '' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center">Nenhum registro encontrado</td>
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
<?php echo view('scripts/visao-geral.php'); ?>