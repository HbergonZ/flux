<div class="modal fade" id="alterarGrupoModal" tabindex="-1" role="dialog" aria-labelledby="alterarGrupoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alterarGrupoModalLabel">Alterar Grupo do Usuário</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAlterarGrupo" method="post" action="<?= site_url('usuarios/alterar-grupo') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="alterarGrupoUserId">

                <div class="modal-body">
                    <p>Alterar grupo do usuário: <strong id="alterarGrupoUsername"></strong></p>

                    <div class="form-group">
                        <label for="alterarGrupoSelect">Novo Grupo*</label>
                        <select class="form-control" id="alterarGrupoSelect" name="group" required>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?= $group ?>"><?= ucfirst($group) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Alterar Grupo</button>
                </div>
            </form>
        </div>
    </div>
</div>