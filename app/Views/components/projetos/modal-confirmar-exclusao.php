<div class="modal fade" id="deleteProjetoModal" tabindex="-1" role="dialog" aria-labelledby="deleteProjetoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProjetoModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDeleteProjeto" method="post" action="<?= site_url("projetos/excluir/$idPlano") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="deleteProjetoId">

                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o projeto <strong id="projetoNameToDelete"></strong>?</p>
                    <p class="text-danger"><small>Esta ação não pode ser desfeita e todas as etapas e ações vinculadas também serão removidas.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </div>
            </form>
        </div>
    </div>
</div>