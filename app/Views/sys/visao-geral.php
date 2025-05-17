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
                            <label for="filterPriorizacao">Priorização</label>
                            <select class="form-control" id="filterPriorizacao" name="priorizacao_gab">
                                <option value="">Todos</option>
                                <option value="1">Priorizadas</option>
                                <option value="0">Não priorizadas</option>
                            </select>
                        </div>
                    </div>
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
                            <input type="text" class="form-control" id="filterResponsavel" name="responsavel" placeholder="Filtrar por responsável">
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
            <h6 class="m-0 font-weight-bold text-primary">Visão Geral dos Projetos</h6>
            <button class="btn btn-sm btn-secondary" id="btnConfigurarCampos">
                <i class="fas fa-cog"></i> Configurar Campos
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0" style="width:100%">
                    <thead>
                        <tr class="text-center">
                            <th class="text-center align-middle">Priorização</th>
                            <th class="align-middle">Plano</th>
                            <th class="align-middle">Projeto</th>
                            <th class="align-middle">Etapa</th>
                            <th class="align-middle">Ação</th>
                            <th class="align-middle">Responsável</th>
                            <th class="align-middle">Equipe</th>
                            <th class="align-middle">Entrega Estimada</th>
                            <th class="align-middle">Data Inicial</th>
                            <th class="align-middle">Data Final</th>
                            <th class="text-center align-middle">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($dados) && !empty($dados)) : ?>
                            <?php foreach ($dados as $registro) : ?>
                                <tr>
                                    <td class="text-center align-middle">
                                        <?= $registro['priorizacao_gab'] == 1 ?
                                            '<i class="fas fa-star text-warning fa-lg" data-toggle="tooltip" title="Priorizada pelo gabinete"></i>' :
                                            '<i class="far fa-star text-secondary fa-lg" data-toggle="tooltip" title="Não priorizada"></i>' ?>
                                    </td>
                                    <td class="text-wrap align-middle"><?= esc($registro['plano']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['nome_projeto']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['etapa']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['acao']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['responsavel']) ?></td>
                                    <td class="text-wrap align-middle"><?= esc($registro['equipe']) ?></td>
                                    <td class="text-center align-middle"><?= !empty($registro['entrega_estimada_formatada']) ? esc($registro['entrega_estimada_formatada']) : '-' ?></td>
                                    <td class="text-center align-middle"><?= !empty($registro['data_inicio_formatada']) ? esc($registro['data_inicio_formatada']) : '-' ?></td>
                                    <td class="text-center align-middle"><?= !empty($registro['data_fim_formatada']) ? esc($registro['data_fim_formatada']) : '-' ?></td>
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
                                        <span class="badge badge-pill <?= $badge_class ?> py-2" style="min-width: 110px; display: inline-block; text-align: center;">
                                            <?= esc($registro['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="11" class="text-center">Nenhum registro encontrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Configurar Campos -->
    <div class="modal fade" id="modalConfigurarCampos" tabindex="-1" role="dialog" aria-labelledby="modalConfigurarCamposLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfigurarCamposLabel">Configurar Campos Visíveis</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formConfigurarCampos">
                        <div class="form-group">
                            <label>Selecione os campos a serem exibidos:</label>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="plano" id="campoPlano" checked>
                                <label class="form-check-label" for="campoPlano">Plano</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="projeto" id="campoProjeto" checked>
                                <label class="form-check-label" for="campoProjeto">Projeto</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="etapa" id="campoEtapa" checked>
                                <label class="form-check-label" for="campoEtapa">Etapa</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="acao" id="campoAcao" checked>
                                <label class="form-check-label" for="campoAcao">Ação</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="responsavel" id="campoResponsavel" checked>
                                <label class="form-check-label" for="campoResponsavel">Responsável</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="equipe" id="campoEquipe" checked>
                                <label class="form-check-label" for="campoEquipe">Equipe</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="entrega_estimada" id="campoEntregaEstimada" checked>
                                <label class="form-check-label" for="campoEntregaEstimada">Entrega Estimada</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="data_inicio" id="campoDataInicio" checked>
                                <label class="form-check-label" for="campoDataInicio">Data Inicial</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="data_fim" id="campoDataFim" checked>
                                <label class="form-check-label" for="campoDataFim">Data Final</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="status" id="campoStatus" checked>
                                <label class="form-check-label" for="campoStatus">Status</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input campo-visivel" type="checkbox" value="priorizacao" id="campoPriorizacao" checked>
                                <label class="form-check-label" for="campoPriorizacao">Priorização</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnAplicarConfigCampos">Aplicar</button>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<?php echo view('scripts/visao-geral'); ?>