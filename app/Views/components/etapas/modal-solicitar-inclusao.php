<div class="modal fade" id="solicitarInclusaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarInclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarInclusaoModalLabel">Solicitar Inclusão de Etapa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarInclusao" method="post" action="<?= site_url('etapas/solicitar-inclusao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="tipo" value="inclusao">
                <input type="hidden" name="id_plano" value="<?= $acao['id_plano'] ?>">
                <input type="hidden" name="id_acao" value="<?= $tipo === 'acao' ? $idVinculo : $acao['id'] ?>">
                <input type="hidden" name="id_meta" value="<?= $tipo === 'meta' ? $idVinculo : '' ?>">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarInclusaoEtapa">Etapa*</label>
                                <input type="text" class="form-control" id="solicitarInclusaoEtapa" name="etapa" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarInclusaoAcao">Ação*</label>
                                <input type="text" class="form-control" id="solicitarInclusaoAcao" value="<?= $acao['acao'] ?>" readonly>
                                <input type="hidden" name="acao" value="<?= $acao['acao'] ?>">
                            </div>
                        </div>
                    </div>

                    <?php if ($tipo === 'meta' && isset($acao['nome_meta'])): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="solicitarInclusaoMeta">Meta</label>
                                    <input type="text" class="form-control" id="solicitarInclusaoMeta" value="<?= $acao['nome_meta'] ?>" readonly>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarInclusaoResponsavel">Responsável*</label>
                                <input type="text" class="form-control" id="solicitarInclusaoResponsavel" name="responsavel" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarInclusaoEquipe">Equipe*</label>
                                <input type="text" class="form-control" id="solicitarInclusaoEquipe" name="equipe" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarInclusaoTempoEstimado">Tempo Estimado (dias)*</label>
                                <input type="number" class="form-control" id="solicitarInclusaoTempoEstimado" name="tempo_estimado_dias" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarInclusaoDataInicio">Data Início</label>
                                <input type="date" class="form-control  optional-date" id="solicitarInclusaoDataInicio" name="data_inicio">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarInclusaoDataFim">Data Fim</label>
                                <input type="date" class="form-control  optional-date" id="solicitarInclusaoDataFim" name="data_fim">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="solicitarInclusaoStatus">Status*</label>
                        <select class="form-control" id="solicitarInclusaoStatus" name="status" required>
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