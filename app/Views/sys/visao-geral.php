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
                            <select class="form-control" id="filterPlano" name="plano">
                                <option value="">Todos</option>
                                <?php foreach ($filtros['planos'] as $plano) : ?>
                                    <option value="<?= esc($plano['plano']) ?>"><?= esc($plano['plano']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterAcao">Ação</label>
                            <select class="form-control" id="filterAcao" name="acao">
                                <option value="">Todos</option>
                                <?php foreach ($filtros['acoes'] as $acao) : ?>
                                    <option value="<?= esc($acao['acao']) ?>"><?= esc($acao['acao']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterMeta">Meta</label>
                            <select class="form-control" id="filterMeta" name="meta">
                                <option value="">Todos</option>
                                <?php foreach ($filtros['metas'] as $meta) : ?>
                                    <option value="<?= esc($meta['meta']) ?>"><?= esc($meta['meta']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterStatus">Status</label>
                            <select class="form-control" id="filterStatus" name="status">
                                <option value="">Todos</option>
                                <?php foreach ($filtros['status'] as $status) : ?>
                                    <option value="<?= esc($status['status']) ?>"><?= esc($status['status']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterEtapa">Etapa</label>
                            <select class="form-control" id="filterEtapa" name="etapa">
                                <option value="">Todos</option>
                                <?php foreach ($filtros['etapas'] as $etapa) : ?>
                                    <option value="<?= esc($etapa['etapa']) ?>"><?= esc($etapa['etapa']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterResponsavel">Responsável</label>
                            <select class="form-control" id="filterResponsavel" name="responsavel">
                                <option value="">Todos</option>
                                <?php foreach ($filtros['responsavel'] as $responsavel) : ?>
                                    <option value="<?= esc($responsavel['responsavel']) ?>"><?= esc($responsavel['responsavel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterEquipe">Equipe</label>
                            <input type="text" class="form-control" id="filterEquipe" name="equipe" placeholder="Filtrar por equipe">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterStartDate">Período</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="filterStartDate" name="data_inicio" placeholder="Data inicial">
                                <input type="date" class="form-control" id="filterEndDate" name="data_fim" placeholder="Data final">
                            </div>
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
                            <th>Responsável</th>
                            <th>Equipe</th>
                            <th>Status</th>
                            <th>Início</th>
                            <th>Término</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($dados) && !empty($dados)) : ?>
                            <?php foreach ($dados as $registro) : ?>
                                <tr>
                                    <td class="text-wrap align-middle"><?= esc($registro['plano']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['acao']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['meta']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['etapa']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['responsavel']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['equipe']) ?></td>
                                    <td class="text-center align-middle">
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
                                        <span class="badge <?= $badge_class ?>"><?= esc($registro['status']) ?></span>
                                    </td>
                                    <td class="text-center align-middle"><?= !empty($registro['data_inicio']) ? esc(date('d/m/Y', strtotime($registro['data_inicio']))) : '' ?></td>
                                    <td class="text-center align-middle"><?= !empty($registro['data_fim']) ? esc(date('d/m/Y', strtotime($registro['data_fim']))) : '' ?></td>
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

<?php echo view('scripts/visao-geral'); ?>