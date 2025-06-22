<div class="modal fade" id="solicitarEdicaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarEdicaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Solicitar Edição de Projeto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarEdicao" method="post" action="<?= site_url('projetos/solicitar-edicao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_projeto" id="solicitarEdicaoId">
                <input type="hidden" name="tipo" value="edicao">
                <input type="hidden" name="id_plano" id="idPlano" value="<?= $idPlano ?>">

                <div class="modal-body">
                    <div id="alertNenhumaAlteracao" class="alert alert-warning d-none">
                        Você não fez nenhuma alteração nos campos. Modifique pelo menos um campo para enviar a solicitação.
                    </div>

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
                                        <label for="solicitarEdicaoIdentificador"><i class="fas fa-hashtag mr-1"></i>Identificador*</label>
                                        <input type="text" class="form-control" id="solicitarEdicaoIdentificador" name="identificador" required maxlength="10">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoNome"><i class="fas fa-tag mr-1"></i>Nome*</label>
                                        <input type="text" class="form-control" id="solicitarEdicaoNome" name="nome" required maxlength="255">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="solicitarEdicaoDescricao"><i class="fas fa-align-left mr-1"></i>Descrição</label>
                                <textarea class="form-control" id="solicitarEdicaoDescricao" name="descricao" rows="3"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoVinculado"><i class="fas fa-link mr-1"></i>Projeto Vinculado</label>
                                        <input type="text" class="form-control" id="solicitarEdicaoVinculado" name="projeto_vinculado" maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoEixo"><i class="fas fa-project-diagram mr-1"></i>Eixo</label>
                                        <select class="form-control" id="solicitarEdicaoEixo" name="id_eixo">
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
                                        <label for="solicitarEdicaoPriorizacao"><i class="fas fa-star mr-1"></i>Priorização GAB</label>
                                        <select class="form-control" id="solicitarEdicaoPriorizacao" name="priorizacao_gab">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarEdicaoStatus"><i class="fas fa-info-circle mr-1"></i>Status do Projeto*</label>
                                        <select class="form-control" id="solicitarEdicaoStatus" name="status" required>
                                            <option value="Ativo">Ativo</option>
                                            <option value="Paralisado">Paralisado</option>
                                            <option value="Concluído">Concluído</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="solicitarEdicaoResponsaveis"><i class="fas fa-users mr-1"></i>Responsáveis</label>
                                <textarea class="form-control" id="solicitarEdicaoResponsaveis" name="responsaveis" rows="2"></textarea>
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
                                    <div class="p-3 d-flex flex-column" style="height: 100%; min-height: 400px;">
                                        <!-- Seção de Evidências Atuais -->
                                        <div class="mb-3 border-bottom flex-grow-1" style="height: 50%;">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-list-ul mr-1"></i>Evidências Atuais
                                                <span class="badge badge-primary badge-pill ml-1" id="contadorEvidenciasProjetoAtuaisSolicitacao">0</span>
                                            </h6>
                                            <div id="loadingEvidenciasSolicitacao" class="text-center py-3 d-none">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando evidências...
                                            </div>
                                            <div id="evidenciasProjetoAtuaisListSolicitacao" class="overflow-auto" style="max-height: calc(100% - 30px);">
                                                <div class="list-group">
                                                    <!-- As evidências serão inseridas aqui -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Seção de Evidências para Remover -->
                                        <div class="flex-grow-1" style="height: 50%;">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-trash-alt mr-1"></i>Evidências a serem removidas
                                                <span class="badge badge-secondary badge-pill ml-1" id="contadorEvidenciasProjetoRemoverSolicitacao">0</span>
                                            </h6>
                                            <div id="evidenciasProjetoRemoverListSolicitacao" class="overflow-auto" style="max-height: calc(100% - 30px);">
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
                                                    <input class="form-check-input" type="radio" name="evidencia_projeto_tipo_solicitacao" id="solicitarEdicaoEvidenciaTipoTexto" value="texto" checked>
                                                    <label class="form-check-label" for="solicitarEdicaoEvidenciaTipoTexto">Texto</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="evidencia_projeto_tipo_solicitacao" id="solicitarEdicaoEvidenciaTipoLink" value="link">
                                                    <label class="form-check-label" for="solicitarEdicaoEvidenciaTipoLink">Link</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3" id="solicitarEdicaoGrupoTexto">
                                            <label for="solicitarEdicaoEvidenciaTexto">Evidência (Texto)</label>
                                            <textarea class="form-control" id="solicitarEdicaoEvidenciaTexto" rows="3"></textarea>
                                        </div>

                                        <div class="form-group mb-3 d-none" id="solicitarEdicaoGrupoLink">
                                            <label for="solicitarEdicaoEvidenciaLink">URL</label>
                                            <input type="url" class="form-control" id="solicitarEdicaoEvidenciaLink" placeholder="https://exemplo.com">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="solicitarEdicaoEvidenciaDescricao">Descrição</label>
                                            <textarea class="form-control" id="solicitarEdicaoEvidenciaDescricao" rows="2" placeholder="Explique a evidência"></textarea>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-block" id="btnAdicionarEvidenciaProjetoSolicitacao">
                                            <i class="fas fa-plus mr-2"></i> Adicionar à Lista
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Justificativa -->
                    <div class="form-group">
                        <label for="solicitarEdicaoJustificativa"><i class="fas fa-comment-dots mr-1"></i>Justificativa*</label>
                        <textarea class="form-control" id="solicitarEdicaoJustificativa" name="justificativa" rows="3" required placeholder="Explique por que essas alterações são necessárias"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar Solicitação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>