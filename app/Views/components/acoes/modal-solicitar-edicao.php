<div class="modal fade" id="solicitarEdicaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarEdicaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarEdicaoModalLabel">Solicitar Edição de Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarEdicao" method="post" action="<?= site_url('acoes/solicitar-edicao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_acao" id="solicitarEdicaoId">
                <input type="hidden" name="tipo" value="edicao">
                <input type="hidden" name="id_etapa" value="<?= $idEtapa ?>">
                <input type="hidden" name="id_projeto" value="<?= $projeto['id'] ?>">

                <div class="modal-body">
                    <div id="alertNenhumaAlteracao" class="alert alert-warning d-none">
                        Você não fez nenhuma alteração nos campos. Modifique pelo menos um campo para enviar a solicitação.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoNome">Nome*</label>
                                <input type="text" class="form-control" id="solicitarEdicaoNome" name="nome" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoResponsavel">Responsável</label>
                                <input type="text" class="form-control" id="solicitarEdicaoResponsavel" name="responsavel">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoEquipe">Equipe</label>
                                <input type="text" class="form-control" id="solicitarEdicaoEquipe" name="equipe">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoStatus">Status</label>
                                <select class="form-control" id="solicitarEdicaoStatus" name="status">
                                    <option value="Não iniciado">Não iniciado</option>
                                    <option value="Em andamento">Em andamento</option>
                                    <option value="Paralisado">Paralisado</option>
                                    <option value="Finalizado">Finalizado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarEdicaoTempoEstimado">Tempo Estimado (dias)</label>
                                <input type="number" class="form-control" id="solicitarEdicaoTempoEstimado" name="tempo_estimado_dias" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarEdicaoInicioEstimado">Início Estimado</label>
                                <input type="date" class="form-control" id="solicitarEdicaoInicioEstimado" name="inicio_estimado">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarEdicaoFimEstimado">Fim Estimado</label>
                                <input type="date" class="form-control" id="solicitarEdicaoFimEstimado" name="fim_estimado">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoDataInicio">Data Início Real</label>
                                <input type="date" class="form-control" id="solicitarEdicaoDataInicio" name="data_inicio">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoDataFim">Data Fim Real</label>
                                <input type="date" class="form-control" id="solicitarEdicaoDataFim" name="data_fim">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="solicitarEdicaoOrdem">Ordem</label>
                        <input type="number" class="form-control" id="solicitarEdicaoOrdem" name="ordem" min="1">
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