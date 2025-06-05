<div class="modal fade" id="solicitarInclusaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarInclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarInclusaoModalLabel">Solicitar Inclusão de Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarInclusao" method="post" action="<?= site_url('acoes/solicitar-inclusao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_etapa" value="<?= $tipoOrigem === 'etapa' ? $idOrigem : '' ?>">
                <input type="hidden" name="id_projeto" value="<?= $tipoOrigem === 'projeto' ? $idOrigem : ($tipoOrigem === 'etapa' ? $etapa['id_projeto'] : '') ?>">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="solicitarInclusaoNome">Nome*</label>
                        <input type="text" class="form-control" id="solicitarInclusaoNome" name="nome" required maxlength="255">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarInclusaoResponsavel">Responsável</label>
                                <input type="text" class="form-control" id="solicitarInclusaoResponsavel" name="responsavel" maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarInclusaoTempoEstimado">Tempo Estimado (dias)</label>
                                <input type="number" class="form-control" id="solicitarInclusaoTempoEstimado" name="tempo_estimado_dias">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarInclusaoEntregaEstimada">Entrega Estimada</label>
                                <input type="date" class="form-control" id="solicitarInclusaoEntregaEstimada" name="entrega_estimada">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarInclusaoDataInicio">Data Início Real</label>
                                <input type="date" class="form-control" id="solicitarInclusaoDataInicio" name="data_inicio">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarInclusaoDataFim">Data Fim Real</label>
                                <input type="date" class="form-control" id="solicitarInclusaoDataFim" name="data_fim">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="solicitarInclusaoStatus">Status</label>
                        <select class="form-control" id="solicitarInclusaoStatus" name="status">
                            <option value="Não iniciado">Não iniciado</option>
                            <option value="Em andamento">Em andamento</option>
                            <option value="Paralisado">Paralisado</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="solicitarInclusaoJustificativa">Justificativa*</label>
                        <textarea class="form-control" id="solicitarInclusaoJustificativa" name="justificativa" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
                </div>
            </form>
        </div>
    </div>
</div>