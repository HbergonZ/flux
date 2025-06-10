<div class="modal fade" id="solicitarEdicaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarEdicaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Solicitar Edição de Ação
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarEdicao" method="post" action="<?= site_url('acoes/solicitar-edicao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="solicitarEdicaoId">
                <input type="hidden" name="id_etapa" value="<?= $tipoOrigem === 'etapa' ? $idOrigem : '' ?>">
                <input type="hidden" name="id_projeto" value="<?= $tipoOrigem === 'projeto' ? $idOrigem : ($tipoOrigem === 'etapa' ? $etapa['id_projeto'] : '') ?>">
                <input type="hidden" name="adicionar_membro" id="adicionarMembroInput">
                <input type="hidden" name="remover_membro" id="removerMembroInput">
                <input type="hidden" id="equipeOriginal" name="equipe_original" value="">

                <div class="modal-body">
                    <div id="alertNenhumaAlteracao" class="alert alert-warning d-none">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Você não fez nenhuma alteração nos campos. Modifique pelo menos um campo para enviar a solicitação.
                    </div>

                    <!-- Informações Básicas -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle mr-2"></i>Informações Básicas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="solicitarEdicaoNome"><i class="fas fa-tag mr-1"></i>Nome*</label>
                                <input type="text" class="form-control" id="solicitarEdicaoNome" name="nome" required maxlength="255">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoResponsavel"><i class="fas fa-user-tie mr-1"></i>Responsável</label>
                                        <input type="text" class="form-control" id="solicitarEdicaoResponsavel" name="responsavel" maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoStatus"><i class="fas fa-tasks mr-1"></i>Status</label>
                                        <select class="form-control" id="solicitarEdicaoStatus" name="status">
                                            <option value="Não iniciado">Não iniciado</option>
                                            <option value="Em andamento">Em andamento</option>
                                            <option value="Paralisado">Paralisado</option>
                                            <option value="Finalizado">Finalizado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datas e Prazos -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-alt mr-2"></i>Datas e Prazos
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoTempoEstimado"><i class="fas fa-clock mr-1"></i>Tempo Estimado (dias)</label>
                                        <input type="number" class="form-control" id="solicitarEdicaoTempoEstimado" name="tempo_estimado_dias">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoEntregaEstimada"><i class="fas fa-calendar-check mr-1"></i>Entrega Estimada</label>
                                        <input type="date" class="form-control" id="solicitarEdicaoEntregaEstimada" name="entrega_estimada">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoOrdem"><i class="fas fa-sort-numeric-down mr-1"></i>Ordem</label>
                                        <input type="number" class="form-control" id="solicitarEdicaoOrdem" name="ordem">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoDataInicio"><i class="fas fa-play-circle mr-1"></i>Data Início Real</label>
                                        <input type="date" class="form-control" id="solicitarEdicaoDataInicio" name="data_inicio">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoDataFim"><i class="fas fa-flag-checkered mr-1"></i>Data Fim Real</label>
                                        <input type="date" class="form-control" id="solicitarEdicaoDataFim" name="data_fim">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gerenciamento de Equipe -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-users mr-2"></i> Gerenciar Equipe
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="row no-gutters">
                                <!-- Membros Atuais -->
                                <div class="col-md-6 border-right">
                                    <div class="p-3">
                                        <h6 class="text-center font-weight-bold">
                                            <i class="fas fa-user-friends mr-1"></i>Membros Atuais
                                            <span class="badge badge-primary badge-pill ml-1" id="contadorMembrosAtuais">0</span>
                                        </h6>
                                        <div id="equipeAtualList" class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                                            <div class="text-center py-3">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando...
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Usuários Disponíveis -->
                                <div class="col-md-6">
                                    <div class="p-3">
                                        <h6 class="text-center font-weight-bold">
                                            <i class="fas fa-user-plus mr-1"></i>Usuários Disponíveis
                                            <span class="badge badge-secondary badge-pill ml-1" id="contadorUsuariosDisponiveis">0</span>
                                        </h6>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                            <input type="text" id="buscaUsuarioEquipe" class="form-control" placeholder="Buscar usuário...">
                                        </div>
                                        <div id="usuariosDisponiveisList" class="list-group list-group-flush" style="max-height: 150px; overflow-y: auto;">
                                            <div class="text-center py-3">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Justificativa -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-comment-dots mr-2"></i>Justificativa
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label for="solicitarEdicaoJustificativa">Justificativa para as alterações*</label>
                                <textarea class="form-control" id="solicitarEdicaoJustificativa" name="justificativa" rows="3" required></textarea>
                                <small class="text-muted">Explique por que estas alterações são necessárias.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnEnviarSolicitacao">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar Solicitação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>