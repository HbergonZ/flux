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
            <form id="formAddAcao" action="<?= site_url("acoes/cadastrar/{$idOrigem}/{$tipoOrigem}") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_etapa" value="<?= $tipoOrigem === 'etapa' ? $idOrigem : '' ?>">
                <input type="hidden" name="id_projeto" value="<?= $tipoOrigem === 'projeto' ? $idOrigem : ($tipoOrigem === 'etapa' ? $etapa['id_projeto'] : '') ?>">

                <div class="modal-body">
                    <!-- Informações Básicas -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>Informações Básicas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="acaoNome" class="font-weight-bold"><i class="fas fa-tag mr-1"></i>Nome*</label>
                                <input type="text" class="form-control" id="acaoNome" name="nome" required maxlength="255" placeholder="Digite o nome da ação">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="acaoEntregaEstimada" class="font-weight-bold"><i class="fas fa-calendar-check mr-1"></i>Entrega Estimada*</label>
                                        <input type="date" class="form-control" id="acaoEntregaEstimada" name="entrega_estimada" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="acaoOrdem" class="font-weight-bold"><i class="fas fa-sort-numeric-down mr-1"></i>Ordem*</label>
                                        <input type="number" class="form-control" id="acaoOrdem" name="ordem" min="1" required readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="acaoDataInicio" class="font-weight-bold"><i class="fas fa-play-circle mr-1"></i>Data Início</label>
                                        <input type="date" class="form-control" id="acaoDataInicio" name="data_inicio">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="acaoDataFim" class="font-weight-bold"><i class="fas fa-flag-checkered mr-1"></i>Data Fim</label>
                                        <input type="date" class="form-control" id="acaoDataFim" name="data_fim" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gerenciamento de Responsáveis -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-users mr-2"></i>Gerenciar Responsáveis
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="row no-gutters">
                                <!-- Responsáveis Selecionados -->
                                <div class="col-md-6 border-right">
                                    <div class="p-3">
                                        <h6 class="text-center font-weight-bold">
                                            <i class="fas fa-user-friends mr-1"></i>Responsáveis
                                            <span class="badge badge-primary badge-pill ml-1" id="contadorResponsaveisAdd">0</span>
                                        </h6>
                                        <div id="responsaveisSelecionadosAdd" class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                                            <div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Usuários Disponíveis -->
                                <div class="col-md-6">
                                    <div class="p-3">
                                        <h6 class="text-center font-weight-bold">
                                            <i class="fas fa-user-plus mr-1"></i>Usuários Disponíveis
                                            <span class="badge badge-secondary badge-pill ml-1" id="contadorUsuariosAdd">0</span>
                                        </h6>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                            <input type="text" id="buscarUsuarioAdd" class="form-control" placeholder="Buscar usuário...">
                                        </div>
                                        <div id="usuariosDisponiveisAdd" class="list-group list-group-flush" style="max-height: 150px; overflow-y: auto;">
                                            <div class="text-center py-3">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campo hidden para armazenar os IDs dos responsáveis -->
                    <input type="hidden" name="responsaveis_ids" id="responsaveisIdsAdd" value="">
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