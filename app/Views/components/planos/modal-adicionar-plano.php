<div class="modal fade" id="addPlanoModal" tabindex="-1" role="dialog" aria-labelledby="addPlanoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPlanoModalLabel">Incluir Novo Plano</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddPlano" action="<?= site_url('planos/cadastrar') ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

                <div class="modal-body">
                    <div class="form-group">
                        <label for="planoName">Nome do Plano*</label>
                        <input type="text" class="form-control" id="planoName" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label for="planoSigla">Sigla*</label>
                        <input type="text" class="form-control" id="planoSigla" name="sigla" required maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="planoDescription">Descrição</label>
                        <textarea class="form-control" id="planoDescription" name="descricao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Plano</button>
                </div>
            </form>
        </div>
    </div>
</div>