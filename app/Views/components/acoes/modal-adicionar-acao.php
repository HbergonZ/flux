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
                <input type="hidden" name="responsaveis_adicionar" id="responsaveisAdicionarInput" value="">

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
                                        <label for="acaoDataInicio" class="font-weight-bold"><i class="fas fa-play-circle mr-1"></i>Data Início Real</label>
                                        <input type="date" class="form-control" id="acaoDataInicio" name="data_inicio">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="acaoDataFim" class="font-weight-bold"><i class="fas fa-flag-checkered mr-1"></i>Data Fim Real</label>
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
                                                <i class="fas fa-spinner fa-spin"></i> Carregando usuários...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campo hidden para armazenar os IDs dos responsáveis -->
                    <input type="hidden" name="responsaveis_ids" id="responsaveisIdsAdd" value="">

                    <!-- Seção de Evidências -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-file-alt mr-2"></i>Gerenciar Evidências
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="row no-gutters">
                                <!-- Coluna de Evidências (Atuais e para Remover) -->
                                <div class="col-md-6 border-right">
                                    <div class="p-3 d-flex flex-column" style="height: 100%; min-height: 400px;">
                                        <!-- Seção de Evidências Atuais -->
                                        <div class="mb-3 border-bottom flex-grow-1" style="height: 50%;">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-list-ul mr-1"></i>Evidências Atuais
                                                <span class="badge badge-primary badge-pill ml-1" id="contadorEvidenciasAtuaisAdd">0</span>
                                            </h6>
                                            <div id="evidenciasAtuaisListAdd" class="overflow-auto" style="max-height: calc(100% - 30px);">
                                                <div class="list-group">
                                                    <div class="text-center py-3 text-muted">Nenhuma evidência cadastrada</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Seção de Evidências para Remover -->
                                        <div class="flex-grow-1" style="height: 50%;">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-trash-alt mr-1"></i>Evidências a serem removidas
                                                <span class="badge badge-secondary badge-pill ml-1" id="contadorEvidenciasRemoverAdd">0</span>
                                            </h6>
                                            <div id="evidenciasRemoverListAdd" class="overflow-auto" style="max-height: calc(100% - 30px);">
                                                <div class="list-group">
                                                    <div class="text-center py-3 text-muted">Nenhuma evidência marcada para remoção</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Coluna de Adicionar Evidência -->
                                <div class="col-md-6">
                                    <div class="p-3">
                                        <h6 class="font-weight-bold mb-3">
                                            <i class="fas fa-plus-circle mr-1"></i>Adicionar Evidência
                                        </h6>

                                        <div class="form-group mb-3">
                                            <label>Tipo de Evidência</label>
                                            <div class="d-flex">
                                                <div class="form-check mr-3">
                                                    <input class="form-check-input" type="radio" name="evidencia_tipo_add" id="evidenciaTipoTextoAdd" value="texto" checked>
                                                    <label class="form-check-label" for="evidenciaTipoTextoAdd">Texto</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="evidencia_tipo_add" id="evidenciaTipoLinkAdd" value="link">
                                                    <label class="form-check-label" for="evidenciaTipoLinkAdd">Link</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3" id="grupoTextoAdd">
                                            <label for="evidenciaTextoAdd">Evidência (Texto)</label>
                                            <textarea class="form-control" id="evidenciaTextoAdd" rows="3"></textarea>
                                        </div>

                                        <div class="form-group mb-3 d-none" id="grupoLinkAdd">
                                            <label for="evidenciaLinkAdd">URL</label>
                                            <input type="url" class="form-control" id="evidenciaLinkAdd" placeholder="https://exemplo.com">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="evidenciaDescricaoAdd">Descrição</label>
                                            <textarea class="form-control" id="evidenciaDescricaoAdd" rows="2" placeholder="Explique a evidência"></textarea>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-block" id="btnAdicionarEvidenciaAdd">
                                            <i class="fas fa-plus mr-2"></i> Adicionar à Lista
                                        </button>
                                    </div>
                                </div>
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