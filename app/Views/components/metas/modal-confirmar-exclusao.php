<div class="modal fade" id="deleteMetaModal" tabindex="-1" role="dialog" aria-labelledby="deleteMetaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteMetaModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDeleteMeta" method="post" action="<?= site_url("metas/excluir/$idAcao") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="deleteMetaId">

                <div class="modal-body">
                    <p>Tem certeza que deseja excluir esta meta? Esta ação não pode ser desfeita.</p>
                    <p><strong>Meta: </strong><span id="metaNameToDelete"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </div>
            </form>
        </div>
    </div>
</div>