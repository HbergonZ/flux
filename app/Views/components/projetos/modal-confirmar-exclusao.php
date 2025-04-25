<!-- Modal para Confirmar Exclusão -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1" role="dialog" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProjectModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDeleteProject" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="deleteProjectId">

                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este projeto? Esta ação não pode ser desfeita.</p>
                    <p><strong>Projeto: </strong><span id="projectNameToDelete"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </div>
            </form>
        </div>
    </div>
</div>