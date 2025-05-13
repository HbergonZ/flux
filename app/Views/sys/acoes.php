<!-- Importação de Modais -->
<?php echo view('components/acoes/modal-editar-acao.php'); ?>
<?php echo view('components/acoes/modal-confirmar-exclusao.php'); ?>
<?php echo view('components/acoes/modal-adicionar-acao.php'); ?>
<?php echo view('components/acoes/modal-solicitar-edicao.php'); ?>
<?php echo view('components/acoes/modal-solicitar-exclusao.php'); ?>
<?php echo view('components/acoes/modal-solicitar-inclusao.php'); ?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <?= isset($acessoDireto) && $acessoDireto ?
                "Ações do Projeto: {$projeto['nome']}" :
                "Ações da Etapa: {$etapa['nome']}"
            ?>
        </h1>
        <div>
            <a href="<?= isset($acessoDireto) && $acessoDireto ?
                            site_url("planos/{$projeto['id_plano']}/projetos") :
                            site_url("projetos/{$projeto['id']}/etapas")
                        ?>" class="btn btn-secondary btn-icon-split btn-sm">
                <span class="icon text-white-50">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span class="text">
                    <?= isset($acessoDireto) && $acessoDireto ?
                        'Voltar para Projetos' :
                        'Voltar para Etapas'
                    ?>
                </span>
            </a>
        </div>
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
                            <th>Ordem</th>
                            <th>Nome</th>
                            <?php if (!isset($acessoDireto) || !$acessoDireto): ?>
                                <th>Etapa</th>
                            <?php endif; ?>
                            <th>Responsável</th>
                            <th>Status</th>
                            <th>Data Início</th>
                            <th>Data Fim</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (isset($acoes) && !empty($acoes)) : ?>
                            <?php foreach ($acoes as $acao) :
                                $id = $acao['id_acao'] . '-' . str_replace(' ', '-', strtolower($acao['nome'])); ?>
                                <tr>
                                    <td class="text-center"><?= $acao['ordem'] ?? '-' ?></td>
                                    <td class="text-wrap"><?= $acao['nome'] ?></td>
                                    <?php if (!isset($acessoDireto) || !$acessoDireto): ?>
                                        <td><?= $etapa['nome'] ?></td>
                                    <?php endif; ?>
                                    <td><?= $acao['responsavel'] ?? '-' ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-<?=
                                                                    $acao['status'] == 'Finalizado' ? 'success' : ($acao['status'] == 'Em andamento' ? 'primary' : ($acao['status'] == 'Paralisado' ? 'danger' : 'secondary')) ?>">
                                            <?= $acao['status'] ?? 'Não iniciado' ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= $acao['data_inicio'] ? date('d/m/Y', strtotime($acao['data_inicio'])) : '-' ?></td>
                                    <td class="text-center"><?= $acao['data_fim'] ? date('d/m/Y', strtotime($acao['data_fim'])) : '-' ?></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex">
                                            <!-- Botões de ação permanecem os mesmos -->
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="<?= isset($acessoDireto) && $acessoDireto ? '7' : '8' ?>" class="text-center">Nenhuma ação encontrada</td>
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