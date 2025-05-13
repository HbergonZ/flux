<div class="modal fade" id="deleteAcaoModal" tabindex="-1" role="dialog" aria-labelledby="deleteAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAcaoModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDeleteAcao" method="post" action="<?= site_url("acoes/excluir/$idProjeto") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="deleteAcaoId">

                <div class="modal-body">
                    <p>Tem certeza que deseja excluir esta ação? Esta ação não pode ser desfeita.</p>
                    <p><strong>Ação: </strong><span id="acaoNameToDelete"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </div>
            </form>
        </div>
    </div>
</div>