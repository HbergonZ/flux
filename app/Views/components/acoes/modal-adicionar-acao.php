<div class="modal fade" id="addAcaoModal" tabindex="-1" role="dialog" aria-labelledby="addAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAcaoModalLabel">Incluir Nova Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddAcao" action="<?= site_url("acoes/cadastrar/$idPlano") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoIdentificador">Identificador*</label>
                                <input type="text" class="form-control" id="acaoIdentificador" name="identificador" required maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoNome">Ação*</label>
                                <input type="text" class="form-control" id="acaoNome" name="acao" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="acaoDescricao">Descrição</label>
                        <textarea class="form-control" id="acaoDescricao" name="descricao" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoProjetoVinculado">Projeto Vinculado</label>
                                <input type="text" class="form-control" id="acaoProjetoVinculado" name="projeto_vinculado" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoEixo">ID Eixo</label>
                                <input type="number" class="form-control" id="acaoEixo" name="id_eixo">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="acaoResponsaveis">Responsáveis</label>
                        <textarea class="form-control" id="acaoResponsaveis" name="responsaveis" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Ação</button>
                </div>
            </form>
        </div>
    </div>
</div>