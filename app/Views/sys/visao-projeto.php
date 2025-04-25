<!-- Importação de Modais -->



<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('projetos-cadastrados') ?>">Projetos</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $projeto['nome'] ?></li>
        </ol>
    </nav>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Etapas e Ações do Projeto: <?= $projeto['nome'] ?></h1>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <input type="hidden" name="id_projeto" value="<?= $projeto['id'] ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterStage">Etapa</label>
                            <input type="text" class="form-control" id="filterStage" name="etapa" placeholder="Filtrar por etapa">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterAction">Ação</label>
                            <input type="text" class="form-control" id="filterAction" name="acao" placeholder="Filtrar por ação">
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
                            <label for="filterResponsible">Responsável</label>
                            <input type="text" class="form-control" id="filterResponsible" name="responsavel" placeholder="Filtrar por responsável">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterStartDate">Data Início</label>
                            <input type="date" class="form-control" id="filterStartDate" name="data_inicio">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterEndDate">Data Fim</label>
                            <input type="date" class="form-control" id="filterEndDate" name="data_fim">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterCoordination">Coordenação</label>
                            <input type="text" class="form-control" id="filterCoordination" name="coordenacao" placeholder="Filtrar por coordenação">
                        </div>
                    </div>
                    <div class="col-md-3 text-right align-self-end">
                        <button type="button" id="btnLimparFiltros" class="btn btn-secondary btn-icon-split btn-sm">
                            <span class="icon text-white-50">
                                <i class="fas fa-broom"></i>
                            </span>
                            <span class="text">Limpar</span>
                        </button>
                        <button type="submit" class="btn btn-primary btn-icon-split btn-sm">
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Etapas e Ações</h6>
            <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addStageModal">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Incluir Etapa/Ação</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>ID Etapa</th>
                            <th>Etapa</th>
                            <th>ID Ação</th>
                            <th>Ação</th>
                            <th>Coordenação</th>
                            <th>Responsável</th>
                            <th>Tempo Estimado</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etapas as $etapa):
                            $id = $etapa['id_etapa'] . '-' . $etapa['id_acao'];
                            // Determina a classe do badge conforme o status
                            switch ($etapa['status']) {
                                case 'Em andamento':
                                    $badge_class = 'badge-primary';
                                    break;
                                case 'Finalizado':
                                    $badge_class = 'badge-success';
                                    break;
                                case 'Paralisado':
                                    $badge_class = 'badge-warning';
                                    break;
                                case 'Não iniciado':
                                    $badge_class = 'badge-secondary';
                                    break;
                                default:
                                    $badge_class = 'badge-light';
                            }
                        ?>
                            <tr>
                                <td class="text-center">ETP-<?= $etapa['id_etapa'] ?></td>
                                <td class="text-wrap"><?= $etapa['etapa'] ?></td>
                                <td class="text-center">ACT-<?= $etapa['id_acao'] ?></td>
                                <td class="text-wrap"><?= $etapa['acao'] ?></td>
                                <td class="text-center"><?= $etapa['coordenacao'] ?></td>
                                <td class="text-center"><?= $etapa['responsavel'] ?></td>
                                <td class="text-center"><?= $etapa['tempo_estimado_dias'] ?> dias</td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($etapa['data_inicio'])) ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($etapa['data_fim'])) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $badge_class ?>"><?= $etapa['status'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex">
                                        <!-- Botão Detalhes -->
                                        <button type="button" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Detalhes">
                                            <i class="fas fa-info-circle"></i>
                                        </button>

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
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<!-- CSS do DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css" />
<!-- Scripts da página -->
<?php echo view('scripts/etapas.php'); ?>