<div class="modal fade" id="editAcaoModal" tabindex="-1" role="dialog" aria-labelledby="editAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAcaoModalLabel">Editar Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditAcao" method="post" action="<?= isset($idProjeto) ? site_url("acoes/atualizar/$idProjeto") : '' ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editAcaoId">
                <input type="hidden" name="id_projeto" id="editAcaoIdProjeto" value="<?= $idProjeto ?? '' ?>">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="editAcaoNome">Nome da Ação*</label>
                                <input type="text" class="form-control" id="editAcaoNome" name="nome" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoResponsavel">Responsável*</label>
                                <input type="text" class="form-control" id="editAcaoResponsavel" name="responsavel" required maxlength="255">
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
                                <input type="number" class="form-control" id="editAcaoTempoEstimado" name="tempo_estimado_dias">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editAcaoDataInicio">Data Início Prevista</label>
                                <input type="date" class="form-control" id="editAcaoDataInicio" name="inicio_estimado">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editAcaoDataFim">Data Fim Prevista</label>
                                <input type="date" class="form-control" id="editAcaoDataFim" name="fim_estimado">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editAcaoStatus">Status*</label>
                        <select class="form-control" id="editAcaoStatus" name="status" required>
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