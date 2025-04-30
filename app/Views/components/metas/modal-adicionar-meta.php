<div class="modal fade" id="addMetaModal" tabindex="-1" role="dialog" aria-labelledby="addMetaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMetaModalLabel">Incluir Nova Meta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddMeta" action="<?= site_url("metas/cadastrar/$idAcao") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

                <div class="modal-body">
                    <div class="form-group">
                        <label for="metaNome">Nome*</label>
                        <input type="text" class="form-control" id="metaNome" name="nome" required maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Meta</button>
                </div>
            </form>
        </div>
    </div>
</div>