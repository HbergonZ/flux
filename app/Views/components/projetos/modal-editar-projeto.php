<div class="modal fade" id="editProjetoModal" tabindex="-1" role="dialog" aria-labelledby="editProjetoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Editar Projeto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditProjeto" method="post" action="<?= site_url("projetos/atualizar/$idPlano") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editProjetoId">
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
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editProjetoIdentificador"><i class="fas fa-hashtag mr-1"></i>Identificador*</label>
                                        <input type="text" class="form-control" id="editProjetoIdentificador" name="identificador" required maxlength="10">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editProjetoNome"><i class="fas fa-tag mr-1"></i>Nome*</label>
                                        <input type="text" class="form-control" id="editProjetoNome" name="nome" required maxlength="255">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="editProjetoDescricao"><i class="fas fa-align-left mr-1"></i>Descrição</label>
                                <textarea class="form-control" id="editProjetoDescricao" name="descricao" rows="3" maxlength="1000"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="editProjetoMetas"><i class="fas fa-bullseye mr-1"></i>Metas</label>
                                <textarea class="form-control" id="editProjetoMetas" name="metas" rows="3" maxlength="1000"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editProjetoVinculado"><i class="fas fa-link mr-1"></i>Projeto Vinculado</label>
                                        <input type="text" class="form-control" id="editProjetoVinculado" name="projeto_vinculado" maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editProjetoEixo"><i class="fas fa-project-diagram mr-1"></i>Eixo</label>
                                        <select class="form-control" id="editProjetoEixo" name="id_eixo">
                                            <option value="">Selecione um eixo</option>
                                            <?php foreach ($eixos as $eixo): ?>
                                                <option value="<?= $eixo['id'] ?>"><?= $eixo['nome'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="editProjetoPriorizacao"><i class="fas fa-star mr-1"></i>Priorização GAB</label>
                                        <select class="form-control" id="editProjetoPriorizacao" name="priorizacao_gab">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="projetoStatus"><i class="fas fa-info-circle mr-1"></i>Status do Projeto*</label>
                                        <select class="form-control" id="projetoStatus" name="status" required>
                                            <option value="Ativo">Ativo</option>
                                            <option value="Paralisado">Paralisado</option>
                                            <option value="Concluído">Concluído</option>
                                        </select>
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
                                <!-- Responsáveis Atuais -->
                                <div class="col-md-6 border-right">
                                    <div class="p-3">
                                        <h6 class="text-center font-weight-bold">
                                            <i class="fas fa-user-friends mr-1"></i>Responsáveis Atuais
                                            <span class="badge badge-primary badge-pill ml-1" id="contadorResponsaveisAtuais">0</span>
                                        </h6>
                                        <div id="responsaveisAtuaisList" class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
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
                                            <input type="text" id="buscaUsuarioResponsavel" class="form-control" placeholder="Buscar usuário...">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="btnLimparBuscaResponsaveis" title="Limpar busca">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
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
                                    <div class="p-3 d-flex flex-column" style="height: 100%; height: 600px;">
                                        <!-- Seção de Evidências Atuais -->
                                        <div class="mb-3 border-bottom flex-grow-1" style="height: 50%;">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-list-ul mr-1"></i>Evidências Atuais
                                                <span class="badge badge-primary badge-pill ml-1" id="contadorEvidenciasProjetoAtuais">0</span>
                                            </h6>
                                            <div id="loadingEvidencias" class="text-center py-3 d-none">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando evidências...
                                            </div>
                                            <div id="evidenciasProjetoAtuaisList" class="overflow-auto" style="max-height: calc(100% - 30px);">
                                                <div class="list-group">
                                                    <!-- As evidências serão inseridas aqui -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Seção de Evidências para Remover -->
                                        <div class="flex-grow-1" style="height: 50%;">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-trash-alt mr-1"></i>Evidências a serem removidas
                                                <span class="badge badge-secondary badge-pill ml-1" id="contadorEvidenciasProjetoRemover">0</span>
                                            </h6>
                                            <div id="evidenciasProjetoRemoverList" class="overflow-auto" style="max-height: calc(100% - 30px);">
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
                                                    <input class="form-check-input" type="radio" name="evidencia_projeto_tipo" id="editProjetoEvidenciaTipoTexto" value="texto" checked>
                                                    <label class="form-check-label" for="editProjetoEvidenciaTipoTexto">Texto</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="evidencia_projeto_tipo" id="editProjetoEvidenciaTipoLink" value="link">
                                                    <label class="form-check-label" for="editProjetoEvidenciaTipoLink">Link</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3" id="editProjetoGrupoTexto">
                                            <label for="editProjetoEvidenciaTexto">Evidência (Texto)</label>
                                            <textarea class="form-control" id="editProjetoEvidenciaTexto" rows="3"></textarea>
                                        </div>

                                        <div class="form-group mb-3 d-none" id="editProjetoGrupoLink">
                                            <label for="editProjetoEvidenciaLink">URL</label>
                                            <input type="url" class="form-control" id="editProjetoEvidenciaLink" placeholder="https://exemplo.com">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="editProjetoEvidenciaDescricao">Descrição</label>
                                            <textarea class="form-control" id="editProjetoEvidenciaDescricao" rows="2" placeholder="Explique a evidência"></textarea>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-block" id="btnAdicionarEvidenciaProjeto">
                                            <i class="fas fa-plus mr-2"></i> Adicionar à Lista
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Seção de Indicadores -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-line mr-2"></i>Gerenciar Indicadores
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="row no-gutters">
                                <!-- Coluna de Indicadores (Atuais e para Remover) -->
                                <div class="col-md-6 border-right">
                                    <div class="p-3 d-flex flex-column" style="height: 100%; height: 600px;">
                                        <!-- Seção de Indicadores Atuais -->
                                        <div class="mb-3 border-bottom flex-grow-1" style="height: 50%;">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-list-ul mr-1"></i>Indicadores Atuais
                                                <span class="badge badge-primary badge-pill ml-1" id="contadorIndicadoresAtuais">0</span>
                                            </h6>
                                            <div id="loadingIndicadores" class="text-center py-3 d-none">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando indicadores...
                                            </div>
                                            <div id="indicadoresAtuaisList" class="overflow-auto" style="max-height: calc(100% - 30px);">
                                                <div class="list-group">
                                                    <!-- Os indicadores serão inseridos aqui via JavaScript -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Seção de Indicadores para Remover -->
                                        <div class="flex-grow-1" style="height: 50%;">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-trash-alt mr-1"></i>Indicadores a serem removidos
                                                <span class="badge badge-secondary badge-pill ml-1" id="contadorIndicadoresRemover">0</span>
                                            </h6>
                                            <div id="indicadoresRemoverList" class="overflow-auto" style="max-height: calc(100% - 30px);">
                                                <div class="list-group">
                                                    <!-- Indicadores marcados para remoção serão exibidos aqui -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Coluna de Adicionar Indicador -->
                                <div class="col-md-6">
                                    <div class="p-3">
                                        <h6 class="font-weight-bold mb-3">
                                            <i class="fas fa-plus-circle mr-1"></i>Adicionar Indicador
                                        </h6>

                                        <div class="form-group mb-3">
                                            <label for="editProjetoIndicadorConteudo"><i class="fas fa-chart-bar mr-1"></i>Conteúdo</label>
                                            <textarea class="form-control" id="editProjetoIndicadorConteudo" rows="3"></textarea>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="editProjetoIndicadorDescricao"><i class="fas fa-align-left mr-1"></i>Descrição</label>
                                            <textarea class="form-control" id="editProjetoIndicadorDescricao" rows="2" placeholder="Explique o indicador"></textarea>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-block" id="btnAdicionarIndicadorProjeto">
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