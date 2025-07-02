<!-- Importação de Modais -->
<?php echo view('components/usuarios/modal-editar-usuario.php'); ?>
<?php echo view('components/usuarios/modal-alterar-grupo.php'); ?>
<?php echo view('components/usuarios/modal-confirmar-exclusao.php'); ?>
<?php
$loggedUser = auth()->user();
$isSuperadmin = $loggedUser->inGroup('superadmin');
$isAdmin = $loggedUser->inGroup('admin');
?>
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterName">Nome</label>
                            <input type="text" class="form-control" id="filterName" name="name" placeholder="Filtrar por nome">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterGroup">Grupo</label>
                            <select class="form-control" id="filterGroup" name="group">
                                <option value="">Todos</option>
                                <option value="superadmin">Superadmin</option>
                                <option value="admin">Administrador</option>
                                <option value="user">Usuário</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterCreatedAt">Data de Criação</label>
                            <input type="date" class="form-control" id="filterCreatedAt" name="created_at" placeholder="Filtrar por data de criação">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 text-right">
                        <button type="button" id="btnLimparFiltros" class="btn btn-secondary btn-icon-split btn-sm">
                            <span class="icon text-white-50"><i class="fas fa-broom"></i></span>
                            <span class="text">Limpar</span>
                        </button>
                        <button type="submit" class="btn btn-primary btn-icon-split btn-sm mr-2">
                            <span class="icon text-white-50"><i class="fas fa-filter"></i></span>
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
            <?php if ($isSuperadmin): ?>
                <?php $registroAtivo = service('settings')->get('Registro.ativo', null) ?? false; ?>
                <button id="toggleRegistroBtn" class="btn btn-sm btn-<?= $registroAtivo ? 'success' : 'secondary' ?>">
                    <i class="fas fa-user-plus"></i> Registro: <?= $registroAtivo ? 'Ativo' : 'Inativo' ?>
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Grupo</th>
                            <th>Status</th>
                            <th>Data de Criação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)) : ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="text-center align-middle"><?= $user->id ?></td>
                                    <td class="align-middle"><?= $user->name ?></td>
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
                                        <?php
                                        if (!empty($user->created_at) && $user->created_at != "0000-00-00 00:00:00") {
                                            $date = new DateTime($user->created_at);
                                            echo $date->format('d/m/Y H:i');
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-inline-flex">
                                            <?php
                                            $isCurrentUser = $loggedUser->id == $user->id;
                                            $userGroups = $user->getGroups();
                                            $userIsSuperadmin = in_array('superadmin', $userGroups);
                                            $userIsAdmin = in_array('admin', $userGroups);
                                            $canEdit = $isSuperadmin ||
                                                ($isAdmin && (!$userIsAdmin && !$userIsSuperadmin || $isCurrentUser)) ||
                                                (!$isAdmin && $isCurrentUser);
                                            $canChangeGroup = ($isSuperadmin && !$isCurrentUser) ||
                                                ($isAdmin && !$isCurrentUser && !$userIsAdmin && !$userIsSuperadmin);
                                            $canDelete = ($isSuperadmin && !$isCurrentUser && !$userIsSuperadmin) ||
                                                ($isAdmin && !$isCurrentUser && !$userIsAdmin && !$userIsSuperadmin);
                                            ?>
                                            <!-- Alterar Grupo -->
                                            <button type="button"
                                                class="btn btn-sm mx-1 d-flex justify-content-center align-items-center <?= $canChangeGroup ? 'btn-warning' : 'btn-secondary' ?>"
                                                style="width: 32px; height: 32px;"
                                                title="Alterar Grupo"
                                                data-id="<?= $user->id ?>"
                                                data-name="<?= $user->name ?>"
                                                data-superadmin="<?= $userIsSuperadmin ? 'true' : 'false' ?>"
                                                data-admin="<?= $userIsAdmin ? 'true' : 'false' ?>"
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
                                                data-name="<?= $user->name ?>"
                                                <?= !$canDelete ? 'disabled' : '' ?>>
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhum usuário encontrado</td>
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