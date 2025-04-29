<div class="modal fade" id="editPlanoModal" tabindex="-1" role="dialog" aria-labelledby="editPlanoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPlanoModalLabel">Editar Plano</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditPlano" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editPlanoId">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="editPlanoName">Nome do Plano*</label>
                        <input type="text" class="form-control" id="editPlanoName" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label for="editPlanoSigla">Sigla*</label>
                        <input type="text" class="form-control" id="editPlanoSigla" name="sigla" required maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="editPlanoDescription">Descrição</label>
                        <textarea class="form-control" id="editPlanoDescription" name="descricao" rows="3"></textarea>
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