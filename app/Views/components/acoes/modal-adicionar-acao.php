<div class="modal fade" id="addAcaoModal" tabindex="-1" role="dialog" aria-labelledby="addAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAcaoModalLabel">Incluir Nova Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddAcao" action="<?= site_url("acoes/cadastrar/$idEtapa") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoNome">Nome*</label>
                                <input type="text" class="form-control" id="acaoNome" name="nome" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoResponsavel">Responsável</label>
                                <input type="text" class="form-control" id="acaoResponsavel" name="responsavel" maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoInicioEstimado">Início Estimado</label>
                                <input type="date" class="form-control" id="acaoInicioEstimado" name="inicio_estimado">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoFimEstimado">Término Estimado</label>
                                <input type="date" class="form-control" id="acaoFimEstimado" name="fim_estimado">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="acaoTempoEstimado">Tempo Estimado (dias)</label>
                                <input type="number" class="form-control" id="acaoTempoEstimado" name="tempo_estimado_dias" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="acaoStatus">Status</label>
                                <select class="form-control" id="acaoStatus" name="status">
                                    <option value="Não iniciado">Não iniciado</option>
                                    <option value="Em andamento">Em andamento</option>
                                    <option value="Paralisado">Paralisado</option>
                                    <option value="Finalizado">Finalizado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="acaoOrdem">Ordem</label>
                                <input type="number" class="form-control" id="acaoOrdem" name="ordem" min="1">
                            </div>
                        </div>
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