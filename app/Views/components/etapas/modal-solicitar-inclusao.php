<div class="modal fade" id="solicitarInclusaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarInclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i>Solicitar Inclusão de Etapa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarInclusao" method="post" action="<?= site_url('etapas/solicitar-inclusao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="tipo" value="inclusao">
                <input type="hidden" name="id_projeto" value="<?= $idProjeto ?>">
                <input type="hidden" name="id_plano" value="<?= $projeto['id_plano'] ?>">
                <div class="modal-body">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>Informações da Nova Etapa
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="solicitarInclusaoNome">
                                    <i class="fas fa-tag mr-1"></i>Nome da Etapa*
                                </label>
                                <input type="text" class="form-control" id="solicitarInclusaoNome" name="nome" required maxlength="255" placeholder="Informe o nome da etapa">
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-comment-dots mr-2"></i>Justificativa para inclusão
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label for="solicitarInclusaoJustificativa">
                                    Explique o motivo da inclusão da etapa*
                                </label>
                                <textarea class="form-control" id="solicitarInclusaoJustificativa" name="justificativa" rows="3" required placeholder="Explique por que a nova etapa é necessária"></textarea>
                            </div>
                        </div>
                    </div>
                    <!-- Futuramente: acrescente outras seções em novos cards, se necessário -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar Solicitação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>