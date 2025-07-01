<div class="modal fade" id="addEtapaModal" tabindex="-1" role="dialog" aria-labelledby="addEtapaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-layer-group mr-2"></i>Incluir Nova Etapa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddEtapa" action="<?= site_url("etapas/cadastrar/$idProjeto") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <div class="modal-body">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>Informações da Etapa
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="etapaNome">
                                    <i class="fas fa-tag mr-1"></i>Nome da Etapa*
                                </label>
                                <input type="text" class="form-control" id="etapaNome" name="nome" required maxlength="255" placeholder="Digite o nome da etapa">
                            </div>
                            <div class="form-group mb-0">
                                <label for="etapaOrdem">
                                    <i class="fas fa-sort-numeric-up mr-1"></i>Ordem
                                </label>
                                <input type="number" class="form-control" id="etapaOrdem" name="ordem" min="1" readonly placeholder="Ordem da etapa">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Salvar Etapa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>