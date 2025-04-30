<div class="modal fade" id="addEtapaModal" tabindex="-1" role="dialog" aria-labelledby="addEtapaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEtapaModalLabel">Incluir Nova Etapa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddEtapa" action="<?= site_url("etapas/cadastrar/$tipo/$idVinculo") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etapaNome">Etapa*</label>
                                <input type="text" class="form-control" id="etapaNome" name="etapa" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etapaAcao">Ação*</label>
                                <input type="text" class="form-control" id="etapaAcao" name="acao" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etapaCoordenacao">Coordenação*</label>
                                <input type="text" class="form-control" id="etapaCoordenacao" name="coordenacao" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etapaResponsavel">Responsável*</label>
                                <input type="text" class="form-control" id="etapaResponsavel" name="responsavel" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="etapaTempoEstimado">Tempo Estimado (dias)*</label>
                                <input type="number" class="form-control" id="etapaTempoEstimado" name="tempo_estimado_dias" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="etapaDataInicio">Data Início*</label>
                                <input type="date" class="form-control" id="etapaDataInicio" name="data_inicio" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="etapaDataFim">Data Fim*</label>
                                <input type="date" class="form-control" id="etapaDataFim" name="data_fim" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="etapaStatus">Status*</label>
                        <select class="form-control" id="etapaStatus" name="status" required>
                            <option value="Não iniciado">Não iniciado</option>
                            <option value="Em andamento">Em andamento</option>
                            <option value="Paralisado">Paralisado</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Etapa</button>
                </div>
            </form>
        </div>
    </div>
</div>