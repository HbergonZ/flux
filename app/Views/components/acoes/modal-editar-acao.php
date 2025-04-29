<div class="modal fade" id="editAcaoModal" tabindex="-1" role="dialog" aria-labelledby="editAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAcaoModalLabel">Editar Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditAcao" method="post" action="<?= site_url("acoes/atualizar/$idPlano") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editAcaoId">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoIdentificador">Identificador*</label>
                                <input type="text" class="form-control" id="editAcaoIdentificador" name="identificador" required maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoNome">Ação*</label>
                                <input type="text" class="form-control" id="editAcaoNome" name="acao" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editAcaoDescricao">Descrição</label>
                        <textarea class="form-control" id="editAcaoDescricao" name="descricao" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoProjetoVinculado">Projeto Vinculado</label>
                                <input type="text" class="form-control" id="editAcaoProjetoVinculado" name="projeto_vinculado" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoEixo">ID Eixo</label>
                                <input type="number" class="form-control" id="editAcaoEixo" name="id_eixo">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editAcaoResponsaveis">Responsáveis</label>
                        <textarea class="form-control" id="editAcaoResponsaveis" name="responsaveis" rows="2"></textarea>
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