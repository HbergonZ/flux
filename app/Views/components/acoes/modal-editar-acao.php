<div class="modal fade" id="editAcaoModal" tabindex="-1" role="dialog" aria-labelledby="editAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Editar Ação
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditAcao" method="post" action="<?= site_url("acoes/atualizar/{$idOrigem}/{$tipoOrigem}") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editAcaoId">
                <input type="hidden" name="id_etapa" value="<?= $tipoOrigem === 'etapa' ? $idOrigem : '' ?>">
                <input type="hidden" name="id_projeto" value="<?= $tipoOrigem === 'projeto' ? $idOrigem : ($tipoOrigem === 'etapa' ? $etapa['id_projeto'] : '') ?>">
                <input type="hidden" name="evidencias_adicionar" value="">
                <input type="hidden" name="evidencias_remover" value="">
                <input type="hidden" name="responsaveis_adicionar" value="">
                <input type="hidden" name="responsaveis_remover" value="">

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
                                <label for="editAcaoNome"><i class="fas fa-tag mr-1"></i>Nome*</label>
                                <input type="text" class="form-control" id="editAcaoNome" name="nome" required maxlength="255">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editAcaoEntregaEstimada"><i class="fas fa-calendar-check mr-1"></i>Entrega Estimada</label>
                                        <input type="date" class="form-control" id="editAcaoEntregaEstimada" name="entrega_estimada">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editAcaoOrdem" class="font-weight-bold"><i class="fas fa-sort-numeric-down mr-1"></i>Ordem</label>
                                        <input type="number" class="form-control" id="editAcaoOrdem" name="ordem" min="1" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editAcaoDataInicio"><i class="fas fa-play-circle mr-1"></i>Data Início</label>
                                        <input type="date" class="form-control" id="editAcaoDataInicio" name="data_inicio">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editAcaoDataFim"><i class="fas fa-flag-checkered mr-1"></i>Data Fim</label>
                                        <input type="date" class="form-control" id="editAcaoDataFim" name="data_fim" disabled>
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
                                            <span class="badge badge-primary badge-pill ml-1" id="contadorResponsaveisEdit">0</span>
                                        </h6>
                                        <div id="responsaveisSelecionadosEdit" class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                                            <div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Usuários Disponíveis -->
                                <div class="col-md-6">
                                    <div class="p-3">
                                        <h6 class="text-center font-weight-bold">
                                            <i class="fas fa-user-plus mr-1"></i>Usuários Disponíveis
                                            <span class="badge badge-secondary badge-pill ml-1" id="contadorUsuariosEdit">0</span>
                                        </h6>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                            <input type="text" id="buscarUsuarioEdit" class="form-control" placeholder="Buscar usuário...">
                                        </div>
                                        <div id="usuariosDisponiveisEdit" class="list-group list-group-flush" style="max-height: 150px; overflow-y: auto;">
                                            <div class="text-center py-3">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="responsaveis_ids" id="responsaveisIdsEdit" value="">

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
                                                <span class="badge badge-primary badge-pill ml-1" id="contadorEvidenciasAtuais">0</span>
                                            </h6>
                                            <div id="evidenciasAtuaisList" class="overflow-auto" style="max-height: calc(100% - 30px);">
                                                <div class="list-group">
                                                    <!-- Evidências serão carregadas aqui -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Seção de Evidências para Remover -->
                                        <div class="flex-grow-1" style="height: 50%;">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-trash-alt mr-1"></i>Evidências a serem removidas
                                                <span class="badge badge-secondary badge-pill ml-1" id="contadorEvidenciasRemover">0</span>
                                            </h6>
                                            <div id="evidenciasRemoverList" class="overflow-auto" style="max-height: calc(100% - 30px);">
                                                <div class="list-group">
                                                    <!-- Evidências marcadas para remoção serão exibidas aqui -->
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
                                                    <input class="form-check-input" type="radio" name="evidencia_tipo" id="editAcaoEvidenciaTipoTexto" value="texto" checked>
                                                    <label class="form-check-label" for="editAcaoEvidenciaTipoTexto">Texto</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="evidencia_tipo" id="editAcaoEvidenciaTipoLink" value="link">
                                                    <label class="form-check-label" for="editAcaoEvidenciaTipoLink">Link</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3" id="editAcaoGrupoTexto">
                                            <label for="editAcaoEvidenciaTexto">Evidência (Texto)</label>
                                            <textarea class="form-control" id="editAcaoEvidenciaTexto" rows="3"></textarea>
                                        </div>

                                        <div class="form-group mb-3 d-none" id="editAcaoGrupoLink">
                                            <label for="editAcaoEvidenciaLink">URL</label>
                                            <input type="url" class="form-control" id="editAcaoEvidenciaLink" placeholder="https://exemplo.com">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="editAcaoEvidenciaDescricao">Descrição</label>
                                            <textarea class="form-control" id="editAcaoEvidenciaDescricao" rows="2" placeholder="Explique a evidência"></textarea>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-block" id="btnAdicionarEvidencia">
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
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>