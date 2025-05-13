<div class="modal fade" id="addEtapaModal" tabindex="-1" role="dialog" aria-labelledby="addEtapaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEtapaModalLabel">Incluir Nova Etapa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddEtapa" action="<?= site_url("etapas/cadastrar/$idProjeto") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

                <div class="modal-body">
                    <div class="form-group">
                        <label for="etapaNome">Nome*</label>
                        <input type="text" class="form-control" id="etapaNome" name="nome" required maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Etapa</button>
                </div>
            </form>
        </div>
    </div>
</div>