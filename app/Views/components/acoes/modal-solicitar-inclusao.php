<div class="modal fade" id="solicitarInclusaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarInclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i>Solicitar Inclusão de Ação
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarInclusao" action="<?= site_url('acoes/solicitar-inclusao') ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_etapa" value="<?= $tipoOrigem === 'etapa' ? $idOrigem : '' ?>">
                <input type="hidden" name="id_projeto" value="<?= $tipoOrigem === 'projeto' ? $idOrigem : ($tipoOrigem === 'etapa' ? $etapa['id_projeto'] : '') ?>">
                <input type="hidden" name="responsaveis_adicionar" id="responsaveisAdicionarInput" value="">

                <div class="modal-body">
                    <!-- Informações Básicas -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>Informações Básicas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="solicitarInclusaoNome" class="font-weight-bold"><i class="fas fa-tag mr-1"></i>Nome*</label>
                                <input type="text" class="form-control" id="solicitarInclusaoNome" name="nome" required maxlength="255" placeholder="Digite o nome da ação">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarInclusaoEntregaEstimada" class="font-weight-bold"><i class="fas fa-calendar-check mr-1"></i>Entrega Estimada*</label>
                                        <input type="date" class="form-control" id="solicitarInclusaoEntregaEstimada" name="entrega_estimada" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarInclusaoOrdem" class="font-weight-bold"><i class="fas fa-sort-numeric-down mr-1"></i>Ordem*</label>
                                        <input type="number" class="form-control" id="solicitarInclusaoOrdem" name="ordem" min="1" required readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarInclusaoDataInicio" class="font-weight-bold"><i class="fas fa-play-circle mr-1"></i>Data Início Real</label>
                                        <input type="date" class="form-control" id="solicitarInclusaoDataInicio" name="data_inicio">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarInclusaoDataFim" class="font-weight-bold"><i class="fas fa-flag-checkered mr-1"></i>Data Fim Real</label>
                                        <input type="date" class="form-control" id="solicitarInclusaoDataFim" name="data_fim" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Justificativa -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-comment-dots mr-2"></i>Justificativa
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label for="solicitarInclusaoJustificativa" class="font-weight-bold">Justificativa para inclusão*</label>
                                <textarea class="form-control" id="solicitarInclusaoJustificativa" name="justificativa" rows="3" required></textarea>
                                <small class="text-muted">Explique por que esta ação deve ser incluída.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i> Enviar Solicitação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>