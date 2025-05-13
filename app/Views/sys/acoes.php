<!-- Importação de Modais -->
<?php echo view('components/acoes/modal-editar-acao.php'); ?>
<?php echo view('components/acoes/modal-confirmar-exclusao.php'); ?>
<?php echo view('components/acoes/modal-adicionar-acao.php'); ?>
<?php echo view('components/acoes/modal-solicitar-edicao.php'); ?>
<?php echo view('components/acoes/modal-solicitar-exclusao.php'); ?>
<?php echo view('components/acoes/modal-solicitar-inclusao.php'); ?>

<div class="container-fluid">
    <!-- Page Heading -->
    <!-- No cabeçalho da página -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <?php if (isset($acesso_direto) && $acesso_direto): ?>
            <h1 class="h3 mb-0 text-gray-800">Ações Diretas do Projeto: <?= $projeto['nome'] ?></h1>
            <a href="<?= site_url("projetos/{$projeto['id_plano']}/projetos") ?>" class="btn btn-secondary btn-icon-split btn-sm">
                <span class="text">Voltar para Projetos</span>
            </a>
        <?php else: ?>
            <!-- Cabeçalho original para ações de etapas -->
            <h1 class="h3 mb-0 text-gray-800">Ações da Etapa: <?= $etapa['nome'] ?></h1>
            <a href="<?= site_url("projetos/{$projeto['id']}/etapas") ?>" class="btn btn-secondary btn-icon-split btn-sm">
                <span class="text">Voltar para Etapas</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterAcao">Ação</label>
                            <input type="text" class="form-control" id="filterAcao" name="acao" placeholder="Filtrar por ação">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="filterProjeto">Projeto</label>
                            <input type="text" class="form-control" id="filterProjeto" name="projeto" placeholder="Filtrar por projeto">
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Ações</h6>
            <?php if (auth()->user()->inGroup('admin')): ?>
                <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addAcaoModal">
                    <span class="icon text-white-50">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span class="text">Incluir Ação</span>
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
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Ação</th>
                            <th>Projeto</th>
                            <th>Responsável</th>
                            <th>Equipe</th>
                            <th>Tempo Estimado</th>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($acoes) && !empty($acoes)) : ?>
                            <?php foreach ($acoes as $acao) :
                                $id = $acao['id_acao'] . '-' . str_replace(' ', '-', strtolower($acao['acao'])); ?>
                                <tr>
                                    <td class="text-wrap align-middle"><?= $acao['acao'] ?></td>
                                    <td class="text-wrap align-middle"><?= $acao['projeto'] ?></td>
                                    <td class="text-center align-middle"><?= $acao['responsavel'] ?></td>
                                    <td class="text-wrap align-middle"><?= $acao['equipe'] ?></td>
                                    <td class="text-center align-middle"><?= !empty($acao['tempo_estimado_dias']) ? $acao['tempo_estimado_dias'] . ' dias' : '' ?></td>
                                    <td class="text-center align-middle"><?= !empty($acao['data_inicio']) ? date('d/m/Y', strtotime($acao['data_inicio'])) : '' ?></td>
                                    <td class="text-center align-middle"><?= !empty($acao['data_fim']) ? date('d/m/Y', strtotime($acao['data_fim'])) : '' ?></td>
                                    <td class="text-center align-middle">
                                        <?php
                                        $badge_class = '';
                                        switch ($acao['status']) {
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
                                        <span class="badge <?= $badge_class ?>"><?= $acao['status'] ?></span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-inline-flex">
                                            <?php if (auth()->user()->inGroup('admin')): ?>
                                                <!-- Botão Editar -->
                                                <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <!-- Botão Excluir -->
                                                <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Excluir">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- Botão Solicitar Edição -->
                                                <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Solicitar Edição">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <!-- Botão Solicitar Exclusão -->
                                                <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Solicitar Exclusão">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center">Nenhuma ação encontrada</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts da página -->
<?php echo view('scripts/acoes.php'); ?>