<div class="modal fade" id="editMetaModal" tabindex="-1" role="dialog" aria-labelledby="editMetaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMetaModalLabel">Editar Meta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditMeta" method="post" action="<?= site_url("metas/atualizar/$idAcao") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editMetaId">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="editMetaNome">Nome*</label>
                        <input type="text" class="form-control" id="editMetaNome" name="nome" required maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>