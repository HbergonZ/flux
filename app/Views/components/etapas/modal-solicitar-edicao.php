<div class="modal fade" id="solicitarEdicaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarEdicaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarEdicaoModalLabel">Solicitar Edição de Etapa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarEdicao" method="post" action="<?= site_url('etapas/solicitar-edicao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_etapa" id="solicitarEdicaoId">
                <input type="hidden" name="tipo" value="edicao">
                <input type="hidden" name="id_plano" value="<?= $acao['id_plano'] ?>">
                <input type="hidden" name="id_acao" value="<?= $tipo === 'acao' ? $idVinculo : $acao['id'] ?>">
                <input type="hidden" name="id_meta" value="<?= $tipo === 'meta' ? $idVinculo : '' ?>">

                <div class="modal-body">
                    <div id="alertNenhumaAlteracao" class="alert alert-warning d-none">
                        Você não fez nenhuma alteração nos campos. Modifique pelo menos um campo para enviar a solicitação.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoEtapa">Etapa*</label>
                                <input type="text" class="form-control" id="solicitarEdicaoEtapa" name="etapa" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoAcao">Ação*</label>
                                <input type="text" class="form-control" id="solicitarEdicaoAcao" value="<?= $acao['acao'] ?>" readonly>
                                <input type="hidden" name="acao" value="<?= $acao['acao'] ?>">
                            </div>
                        </div>
                    </div>

                    <?php if ($tipo === 'meta' && isset($acao['nome_meta'])): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="solicitarEdicaoMeta">Meta</label>
                                    <input type="text" class="form-control" id="solicitarEdicaoMeta" value="<?= $acao['nome_meta'] ?>" readonly>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoResponsavel">Responsável*</label>
                                <input type="text" class="form-control" id="solicitarEdicaoResponsavel" name="responsavel" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoEquipe">Equipe*</label>
                                <input type="text" class="form-control" id="solicitarEdicaoEquipe" name="equipe" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarEdicaoTempoEstimado">Tempo Estimado (dias)*</label>
                                <input type="number" class="form-control" id="solicitarEdicaoTempoEstimado" name="tempo_estimado_dias" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarEdicaoDataInicio">Data Início*</label>
                                <input type="date" class="form-control" id="solicitarEdicaoDataInicio" name="data_inicio" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarEdicaoDataFim">Data Fim*</label>
                                <input type="date" class="form-control" id="solicitarEdicaoDataFim" name="data_fim" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="solicitarEdicaoStatus">Status*</label>
                        <select class="form-control" id="solicitarEdicaoStatus" name="status" required>
                            <option value="Não iniciado">Não iniciado</option>
                            <option value="Em andamento">Em andamento</option>
                            <option value="Paralisado">Paralisado</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="solicitarEdicaoJustificativa">Justificativa para as alterações*</label>
                        <textarea class="form-control" id="solicitarEdicaoJustificativa" name="justificativa" rows="3" required></textarea>
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