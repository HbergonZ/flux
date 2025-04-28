<!-- Importação de Modais -->
<!-- Modal de Solicitação de Edição -->
<div class="modal fade" id="solicitarEdicaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarEdicaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="solicitarEdicaoModalLabel">Solicitar Edição</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarEdicao">
                <div class="modal-body">
                    <input type="hidden" name="id_etapa" id="edit_id_etapa">
                    <input type="hidden" name="id_acao" id="edit_id_acao">
                    <input type="hidden" name="id_projeto" id="edit_id_projeto" value="<?= $projeto['id'] ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_etapa">Etapa</label>
                                <input type="text" class="form-control" id="edit_etapa" name="etapa" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_acao">Ação</label>
                                <input type="text" class="form-control" id="edit_acao" name="acao" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_coordenacao">Coordenação</label>
                                <input type="text" class="form-control" id="edit_coordenacao" name="coordenacao">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_responsavel">Responsável</label>
                                <input type="text" class="form-control" id="edit_responsavel" name="responsavel">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="Em andamento">Em andamento</option>
                                    <option value="Finalizado">Finalizado</option>
                                    <option value="Paralisado">Paralisado</option>
                                    <option value="Não iniciado">Não iniciado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_tempo_estimado">Tempo Estimado (dias)</label>
                                <input type="number" class="form-control" id="edit_tempo_estimado" name="tempo_estimado_dias" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_data_inicio">Data Início</label>
                                <input type="date" class="form-control" id="edit_data_inicio" name="data_inicio">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_data_fim">Data Fim</label>
                                <input type="date" class="form-control" id="edit_data_fim" name="data_fim">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_justificativa">Justificativa para as alterações <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_justificativa" name="justificativa" rows="3" required placeholder="Descreva detalhadamente o motivo das alterações propostas"></textarea>
                        <small class="form-text text-muted">Mínimo 10 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitSolicitacao">
                        <i class="fas fa-paper-plane mr-1"></i> Enviar Solicitação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


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
        <h1 class="h3 mb-0 text-gray-800">Projeto: <?= $projeto['nome'] ?></h1>
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
                            <th>Etapa</th>
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
                                <td class="text-wrap"><?= $etapa['etapa'] ?></td>
                                <td class="text-wrap"><?= $etapa['acao'] ?></td>
                                <td class="text-center"><?= $etapa['coordenacao'] ?></td>
                                <td class="text-center"><?= $etapa['responsavel'] ?></td>
                                <td class="text-center"><?= !empty($etapa['tempo_estimado_dias']) ? $etapa['tempo_estimado_dias'] . ' dias' : '' ?></td>

                                <td class="text-center"><?= !empty($etapa['data_inicio']) ? date('d/m/Y', strtotime($etapa['data_inicio'])) : '' ?></td>
                                <td class="text-center"><?= !empty($etapa['data_fim']) ? date('d/m/Y', strtotime($etapa['data_fim'])) : '' ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $badge_class ?>"><?= $etapa['status'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex">
                                        <!-- Mantém apenas o botão de Solicitar Edição -->
                                        <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Solicitar Edição">
                                            <i class="fas fa-edit"></i>
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