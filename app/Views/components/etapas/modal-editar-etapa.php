<div class="modal fade" id="editEtapaModal" tabindex="-1" role="dialog" aria-labelledby="editEtapaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEtapaModalLabel">Editar Etapa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditEtapa" method="post" action="<?= site_url("etapas/atualizar/$idProjeto") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editEtapaId">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="editEtapaNome">Nome*</label>
                        <input type="text" class="form-control" id="editEtapaNome" name="nome" required maxlength="255">
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