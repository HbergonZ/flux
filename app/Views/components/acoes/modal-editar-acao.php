<div class="modal fade" id="editAcaoModal" tabindex="-1" role="dialog" aria-labelledby="editAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editAcaoModalLabel">
                    <i class="fas fa-edit mr-2"></i>Editar Ação
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditAcao" method="post" action="<?= site_url("acoes/atualizar/$idOrigem/$tipoOrigem") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editAcaoId">
                <input type="hidden" name="id_etapa" value="<?= $tipoOrigem === 'etapa' ? $idOrigem : '' ?>">
                <input type="hidden" name="id_projeto" value="<?= $tipoOrigem === 'projeto' ? $idOrigem : ($tipoOrigem === 'etapa' ? $etapa['id_projeto'] : '') ?>">

                <div class="modal-body">
                    <!-- Botões de Gerenciamento posicionados aqui -->
                    <div class="d-flex mb-3">
                        <button type="button"
                            class="btn btn-outline-transparent mr-2"
                            style="color: #2c9faf; border-color: #2a96a5"
                            id="btnGerenciarEvidencias">
                            <i class="fas fa-file-alt mr-2"></i> Gerenciar Evidências
                        </button>

                        <button type="button"
                            class="btn btn-outline-transparent"
                            style="color: #2c9faf; border-color: #2a96a5"
                            id="btnVerEquipe">
                            <i class="fas fa-users mr-2"></i> Ver Equipe
                        </button>
                    </div>
                    <hr class="mt-2 mb-4">
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
                                <label for="editAcaoEntregaEstimada">Entrega Estimada</label>
                                <input type="date" class="form-control" id="editAcaoEntregaEstimada" name="entrega_estimada">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editAcaoStatus">Status</label>
                                <select class="form-control" id="editAcaoStatus" name="status">
                                    <option value="Não iniciado">Não iniciado</option>
                                    <option value="Em andamento">Em andamento</option>
                                    <option value="Paralisado">Paralisado</option>
                                    <option value="Finalizado">Finalizado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoDataInicio">Data Início Real</label>
                                <input type="date" class="form-control" id="editAcaoDataInicio" name="data_inicio">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAcaoDataFim">Data Fim Real</label>
                                <input type="date" class="form-control" id="editAcaoDataFim" name="data_fim">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editAcaoOrdem">Ordem</label>
                        <input type="number" class="form-control" id="editAcaoOrdem" name="ordem" min="1" readonly>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>