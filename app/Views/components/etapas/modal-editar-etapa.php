<div class="modal fade" id="editEtapaModal" tabindex="-1" role="dialog" aria-labelledby="editEtapaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-layer-group mr-2"></i>Editar Etapa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditEtapa" method="post" action="<?= site_url("etapas/atualizar/$idProjeto") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editEtapaId">
                <!-- Adicione este campo hidden para a ordem -->
                <input type="hidden" name="ordem" id="editEtapaOrdem">

                <div class="modal-body">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>Informações da Etapa
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="editEtapaNome">
                                    <i class="fas fa-tag mr-1"></i>Nome da Etapa*
                                </label>
                                <input type="text" class="form-control" id="editEtapaNome" name="nome" required maxlength="255" placeholder="Digite o nome da etapa">
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