<div class="modal fade" id="deletePlanoModal" tabindex="-1" role="dialog" aria-labelledby="deletePlanoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePlanoModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDeletePlano" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="deletePlanoId">

                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este plano? Esta ação não pode ser desfeita.</p>
                    <p><strong>Plano: </strong><span id="planoNameToDelete"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </div>
            </form>
        </div>
    </div>
</div>