<div class="modal fade" id="editEtapaModal" tabindex="-1" role="dialog" aria-labelledby="editEtapaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEtapaModalLabel">Editar Etapa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditEtapa" method="post" action="<?= site_url("etapas/atualizar/$tipo/$idVinculo") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_etapa" id="editEtapaId">
                <input type="hidden" name="id_acao" value="<?= $tipo === 'acao' ? $idVinculo : $acao['id'] ?>">
                <input type="hidden" name="id_meta" value="<?= $tipo === 'meta' ? $idVinculo : '' ?>">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEtapaNome">Etapa*</label>
                                <input type="text" class="form-control" id="editEtapaNome" name="etapa" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEtapaAcao">Ação*</label>
                                <input type="text" class="form-control" id="editEtapaAcao" value="<?= $acao['acao'] ?>" readonly>
                                <input type="hidden" name="acao" value="<?= $acao['acao'] ?>">
                            </div>
                        </div>
                    </div>

                    <?php if ($tipo === 'meta' && isset($acao['nome_meta'])): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="editEtapaMeta">Meta</label>
                                    <input type="text" class="form-control" id="editEtapaMeta" value="<?= $acao['nome_meta'] ?>" readonly>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEtapaResponsavel">Responsável*</label>
                                <input type="text" class="form-control" id="editEtapaResponsavel" name="responsavel" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEtapaEquipe">Equipe*</label>
                                <input type="text" class="form-control" id="editEtapaEquipe" name="equipe" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editEtapaTempoEstimado">Tempo Estimado (dias)*</label>
                                <input type="number" class="form-control" id="editEtapaTempoEstimado" name="tempo_estimado_dias" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editEtapaDataInicio">Data Início*</label>
                                <input type="date" class="form-control" id="editEtapaDataInicio" name="data_inicio" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editEtapaDataFim">Data Fim*</label>
                                <input type="date" class="form-control" id="editEtapaDataFim" name="data_fim" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editEtapaStatus">Status*</label>
                        <select class="form-control" id="editEtapaStatus" name="status" required>
                            <option value="Não iniciado">Não iniciado</option>
                            <option value="Em andamento">Em andamento</option>
                            <option value="Paralisado">Paralisado</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
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