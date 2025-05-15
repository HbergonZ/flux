<div class="modal fade" id="editAcaoModal" tabindex="-1" role="dialog" aria-labelledby="editAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAcaoModalLabel">Editar Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditAcao" method="post" action="<?= site_url("acoes/atualizar/$idOrigem/$tipoOrigem") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_acao" id="editAcaoId">
                <input type="hidden" name="id_etapa" value="<?= $tipoOrigem === 'etapa' ? $idOrigem : '' ?>">
                <input type="hidden" name="id_projeto" value="<?= $tipoOrigem === 'projeto' ? $idOrigem : ($tipoOrigem === 'etapa' ? $etapa['id_projeto'] : '') ?>">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="editAcaoNome">Nome*</label>
                        <input type="text" class="form-control" id="editAcaoNome" name="nome" required maxlength="255">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoResponsavel">Responsável</label>
                                <input type="text" class="form-control" id="editAcaoResponsavel" name="responsavel" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoEquipe">Equipe</label>
                                <input type="text" class="form-control" id="editAcaoEquipe" name="equipe" maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editAcaoTempoEstimado">Tempo Estimado (dias)</label>
                                <input type="number" class="form-control" id="editAcaoTempoEstimado" name="tempo_estimado_dias" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editAcaoInicioEstimado">Início Estimado</label>
                                <input type="date" class="form-control" id="editAcaoInicioEstimado" name="inicio_estimado" onchange="validarData(this)">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editAcaoFimEstimado">Fim Estimado</label>
                                <input type="date" class="form-control" id="editAcaoFimEstimado" name="fim_estimado" onchange="validarData(this)">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoDataInicio">Data Início Real</label>
                                <input type="date" class="form-control" id="editAcaoDataInicio" name="data_inicio" onchange="validarData(this)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoDataFim">Data Fim Real</label>
                                <input type="date" class="form-control" id="editAcaoDataFim" name="data_fim" onchange="validarData(this)">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editAcaoStatus">Status</label>
                        <select class="form-control" id="editAcaoStatus" name="status">
                            <option value="Não iniciado">Não iniciado</option>
                            <option value="Em andamento">Em andamento</option>
                            <option value="Paralisado">Paralisado</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editAcaoOrdem">Ordem</label>
                        <input type="number" class="form-control" id="editAcaoOrdem" name="ordem" min="1" readonly>
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