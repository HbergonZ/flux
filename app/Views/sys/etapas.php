<!-- Importação de Modais -->
<?php echo view('components/etapas/modal-editar-etapa.php'); ?>
<?php echo view('components/etapas/modal-confirmar-exclusao.php'); ?>
<?php echo view('components/etapas/modal-adicionar-etapa.php'); ?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Etapas <?= $tipo === 'acao' ? 'da Ação' : 'da Meta' ?>: <?= $nomeVinculo ?></h1>
        <a href="<?= $tipo === 'acao' ? site_url("acoes/{$idVinculo}") : site_url("metas/{$idVinculo}") ?>" class="btn btn-secondary btn-icon-split btn-sm">
            <span class="icon text-white-50">
                <i class="fas fa-arrow-left"></i>
            </span>
            <span class="text">Voltar para <?= $tipo === 'acao' ? 'Ações' : 'Metas' ?></span>
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterEtapa">Etapa</label>
                            <input type="text" class="form-control" id="filterEtapa" name="etapa" placeholder="Filtrar por etapa">
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
                </div>

                <div class="row mt-3">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filterTempoEstimado">Tempo (dias)</label>
                            <input type="number" class="form-control" id="filterTempoEstimado" name="tempo_estimado_dias" placeholder="Dias">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filterDataInicio">Data Início</label>
                            <input type="date" class="form-control" id="filterDataInicio" name="data_inicio">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="filterDataFim">Data Fim</label>
                            <input type="date" class="form-control" id="filterDataFim" name="data_fim">
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
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <div class="text-right">
                                <button type="button" id="btnLimparFiltros" class="btn btn-secondary btn-icon-split btn-sm">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-broom"></i>
                                    </span>
                                    <span class="text">Limpar</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-icon-split btn-sm ml-2">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-filter"></i>
                                    </span>
                                    <span class="text">Filtrar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Etapas</h6>
            <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addEtapaModal">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Incluir Etapa</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Etapa</th>
                            <th>Ação</th>
                            <th>Coordenação</th>
                            <th>Responsável</th>
                            <th>Tempo Estimado</th>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($etapas) && !empty($etapas)) : ?>
                            <?php foreach ($etapas as $etapa) :
                                $id = $etapa['id_etapa'] . '-' . str_replace(' ', '-', strtolower($etapa['etapa'])); ?>
                                <tr>
                                    <td class="text-wrap"><?= $etapa['etapa'] ?></td>
                                    <td class="text-wrap"><?= $etapa['acao'] ?></td>
                                    <td class="text-wrap"><?= $etapa['coordenacao'] ?></td>
                                    <td class="text-wrap"><?= $etapa['responsavel'] ?></td>
                                    <td class="text-center"><?= $etapa['tempo_estimado_dias'] ?> dias</td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($etapa['data_inicio'])) ?></td>
                                    <td class="text-center"><?= date('d/m/Y', strtotime($etapa['data_fim'])) ?></td>
                                    <td class="text-center">
                                        <?php
                                        $badge_class = '';
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
                                        }
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= $etapa['status'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex">
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
                                <td colspan="9" class="text-center">Nenhuma etapa encontrada</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts da página -->
<?php echo view('scripts/etapas.php'); ?>