<div class="modal fade" id="editPlanoModal" tabindex="-1" role="dialog" aria-labelledby="editPlanoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-list mr-2"></i>Editar Plano
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditPlano" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editPlanoId">
                <div class="modal-body">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>Informações do Plano
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="editPlanoName">
                                    <i class="fas fa-tag mr-1"></i>Nome do Plano*
                                </label>
                                <input type="text" class="form-control" id="editPlanoName" name="nome" required maxlength="255" placeholder="Digite o nome do plano">
                            </div>
                            <div class="form-group">
                                <label for="editPlanoSigla">
                                    <i class="fas fa-font mr-1"></i>Sigla*
                                </label>
                                <input type="text" class="form-control" id="editPlanoSigla" name="sigla" required maxlength="50" placeholder="Digite a sigla do plano">
                            </div>
                            <div class="form-group mb-0">
                                <label for="editPlanoDescription">
                                    <i class="fas fa-align-left mr-1"></i>Descrição
                                </label>
                                <textarea class="form-control" id="editPlanoDescription" name="descricao" rows="3" placeholder="Descreva brevemente o plano (opcional)"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>