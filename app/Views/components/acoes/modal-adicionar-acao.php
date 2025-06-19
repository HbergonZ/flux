<div class="modal fade" id="addAcaoModal" tabindex="-1" role="dialog" aria-labelledby="addAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i>Incluir Nova Ação
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddAcao" action="<?= site_url("acoes/cadastrar/$idOrigem/$tipoOrigem") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_etapa" value="<?= $tipoOrigem === 'etapa' ? $idOrigem : '' ?>">
                <input type="hidden" name="id_projeto" value="<?= $tipoOrigem === 'projeto' ? $idOrigem : ($tipoOrigem === 'etapa' ? $etapa['id_projeto'] : '') ?>">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="acaoNome" class="font-weight-bold">Nome*</label>
                        <input type="text" class="form-control" id="acaoNome" name="nome" required maxlength="255" placeholder="Digite o nome da ação">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoResponsavel" class="font-weight-bold">Responsável</label>
                                <input type="text" class="form-control" id="acaoResponsavel" name="responsavel" maxlength="255" placeholder="Nome do responsável">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoTempoEstimado" class="font-weight-bold">Tempo Estimado (dias)</label>
                                <input type="number" class="form-control" id="acaoTempoEstimado" name="tempo_estimado_dias" placeholder="Duração estimada">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoEntregaEstimada" class="font-weight-bold">Entrega Estimada*</label>
                                <input type="date" class="form-control" id="acaoEntregaEstimada" name="entrega_estimada" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoOrdem" class="font-weight-bold">Ordem</label>
                                <input type="number" class="form-control" id="acaoOrdem" name="ordem" min="1" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoDataInicio" class="font-weight-bold">Data Início Real</label>
                                <input type="date" class="form-control" id="acaoDataInicio" name="data_inicio">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="acaoDataFim" class="font-weight-bold">Data Fim Real</label>
                                <input type="date" class="form-control" id="acaoDataFim" name="data_fim">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check mr-2"></i> Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>