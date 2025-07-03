<div class="modal fade" id="deleteAcaoModal" tabindex="-1" role="dialog" aria-labelledby="deleteAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirmar Exclusão
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDeleteAcao" method="post" action="<?= site_url("acoes/excluir/{$idOrigem}/{$tipoOrigem}") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="deleteAcaoId">

                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i>Esta ação não pode ser desfeita!
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Ação a ser excluída:</label>
                        <div class="alert alert-light">
                            <i class="fas fa-tasks mr-2"></i>
                            <span id="acaoNameToDelete" class="font-weight-bold"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Motivo (opcional):</label>
                        <textarea class="form-control" name="motivo_exclusao" rows="2" placeholder="Informe o motivo da exclusão (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt mr-2"></i> Confirmar Exclusão
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>