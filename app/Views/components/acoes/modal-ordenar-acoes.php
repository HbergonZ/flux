<div class="modal fade" id="ordenarAcoesModal" tabindex="-1" role="dialog" aria-labelledby="ordenarAcoesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-sort mr-2"></i>Alterar Ordem das Ações
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formOrdenarAcoes" action="<?= site_url("acoes/salvar-ordem/$idOrigem/$tipoOrigem") ?>">
                <div class="modal-body">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle mr-2"></i>Selecione a nova posição para cada ação. A ordem atual será mantida como referência.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="60%">Ação</th>
                                    <th width="15%" class="text-center">Ordem Atual</th>
                                    <th width="25%" class="text-center">Nova Ordem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Conteúdo será carregado dinamicamente via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i> Salvar Ordem
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>