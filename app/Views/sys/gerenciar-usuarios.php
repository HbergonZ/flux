<!-- Importação de Modais -->
<?php echo view('components/usuarios/modal-editar-usuario.php'); ?>
<?php echo view('components/usuarios/modal-alterar-grupo.php'); ?>
<?php echo view('components/usuarios/modal-confirmar-exclusao.php'); ?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gerenciamento de Usuários</h1>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filterUsername">Nome de Usuário</label>
                            <input type="text" class="form-control" id="filterUsername" name="username" placeholder="Filtrar por usuário">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="filterGroup">Grupo</label>
                            <select class="form-control" id="filterGroup" name="group">
                                <option value="">Todos</option>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?= $group ?>"><?= ucfirst($group) ?></option>
                                <?php endforeach; ?>
                            </select>
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
            <h6 class="m-0 font-weight-bold text-primary">Lista de Usuários</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Nome de Usuário</th>
                            <th>Email</th>
                            <th>Grupo</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)) : ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $user->id ?></td>
                                    <td class="align-middle"><?= $user->username ?></td>
                                    <td class="align-middle"><?= $user->email ?></td>
                                    <td class="text-center align-middle">
                                        <?= implode(', ', array_map('ucfirst', $user->getGroups())) ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge <?= $user->active ? 'badge-success' : 'badge-secondary' ?>">
                                            <?= $user->active ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-inline-flex">
                                            <?php
                                            $isCurrentUser = auth()->user()->id == $user->id;
                                            $userIsAdmin = in_array('admin', $user->getGroups());
                                            $loggedUserIsAdmin = auth()->user()->inGroup('admin');

                                            // Pode editar se:
                                            // 1. É admin E (não está editando outro admin OU é ele mesmo)
                                            // 2. Não é admin mas está editando a si mesmo
                                            $canEdit = ($loggedUserIsAdmin && (!$userIsAdmin || $isCurrentUser)) || (!$loggedUserIsAdmin && $isCurrentUser);

                                            // Pode alterar grupo se:
                                            // É admin E não está editando a si mesmo E usuário não é admin
                                            $canChangeGroup = $loggedUserIsAdmin && !$isCurrentUser && !$userIsAdmin;

                                            // Pode excluir se:
                                            // É admin E não está editando a si mesmo E usuário não é admin
                                            $canDelete = $loggedUserIsAdmin && !$isCurrentUser && !$userIsAdmin;
                                            ?>

                                            <!-- Alterar Grupo -->
                                            <button type="button"
                                                class="btn btn-sm mx-1 d-flex justify-content-center align-items-center <?= $canChangeGroup ? 'btn-warning' : 'btn-secondary' ?>"
                                                style="width: 32px; height: 32px;"
                                                title="Alterar Grupo"
                                                data-id="<?= $user->id ?>"
                                                data-username="<?= $user->username ?>"
                                                <?= !$canChangeGroup ? 'disabled' : '' ?>>
                                                <i class="fas fa-users"></i>
                                            </button>

                                            <!-- Editar -->
                                            <button type="button"
                                                class="btn btn-sm mx-1 d-flex justify-content-center align-items-center <?= $canEdit ? 'btn-primary' : 'btn-secondary' ?>"
                                                style="width: 32px; height: 32px;"
                                                title="Editar"
                                                data-id="<?= $user->id ?>"
                                                <?= !$canEdit ? 'disabled' : '' ?>>
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- Excluir -->
                                            <button type="button"
                                                class="btn btn-sm mx-1 d-flex justify-content-center align-items-center <?= $canDelete ? 'btn-danger' : 'btn-secondary' ?>"
                                                style="width: 32px; height: 32px;"
                                                title="Excluir"
                                                data-id="<?= $user->id ?>"
                                                data-username="<?= $user->username ?>"
                                                <?= !$canDelete ? 'disabled' : '' ?>>
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center">Nenhum usuário encontrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts da página -->
<?php echo view('scripts/gerenciar-usuarios.php'); ?>