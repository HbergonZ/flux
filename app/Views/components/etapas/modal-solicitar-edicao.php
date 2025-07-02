<div class="modal fade" id="solicitarEdicaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarEdicaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Solicitar Edição de Etapa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarEdicao" method="post" action="<?= site_url('etapas/solicitar-edicao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_etapa" id="solicitarEdicaoId">
                <input type="hidden" name="tipo" value="edicao">
                <input type="hidden" name="id_projeto" value="<?= $idProjeto ?>">
                <input type="hidden" name="id_plano" value="<?= $projeto['id_plano'] ?>">
                <div class="modal-body">
                    <div id="alertNenhumaAlteracao" class="alert alert-warning d-none">
                        Você não fez nenhuma alteração nos campos. Modifique pelo menos um campo para enviar a solicitação.
                    </div>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>Informações da Etapa
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoNome">
                                            <i class="fas fa-tag mr-1"></i>Nome da Etapa*
                                        </label>
                                        <input type="text" class="form-control" id="solicitarEdicaoNome" name="nome" required maxlength="255" placeholder="Informe o nome da etapa">
                                    </div>
                                </div>
                            </div>
                            <!-- Você pode expandir com mais campos relevantes à etapa aqui, se existir -->
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-comment-dots mr-2"></i>Justificativa para as alterações
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label for="solicitarEdicaoJustificativa">
                                    Explique o motivo dessas alterações*
                                </label>
                                <textarea class="form-control" id="solicitarEdicaoJustificativa" name="justificativa" rows="3" required placeholder="Explique por que essas alterações são necessárias"></textarea>
                            </div>
                        </div>
                    </div>
                    <!-- Caso sua tela de ETAPA tenha outras seções como "Evidências" ou "Indicadores", inclua também em formato card aqui -->
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