<div class="modal fade" id="solicitarInclusaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarInclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i>Solicitar Inclusão de Projeto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarInclusao" method="post" action="<?= site_url('projetos/solicitar-inclusao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="tipo" value="inclusao">
                <input type="hidden" name="id_plano" value="<?= $idPlano ?>">

                <div class="modal-body">
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
                                        <label for="solicitarInclusaoIdentificador"><i class="fas fa-hashtag mr-1"></i>Identificador*</label>
                                        <input type="text" class="form-control" id="solicitarInclusaoIdentificador" name="identificador" required maxlength="10">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarInclusaoNome"><i class="fas fa-tag mr-1"></i>Nome*</label>
                                        <input type="text" class="form-control" id="solicitarInclusaoNome" name="nome" required maxlength="255">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="solicitarInclusaoDescricao"><i class="fas fa-align-left mr-1"></i>Descrição</label>
                                <textarea class="form-control" id="solicitarInclusaoDescricao" name="descricao" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="solicitarInclusaoMetas"><i class="fas fa-bullseye mr-1"></i>Metas</label>
                                <textarea class="form-control" id="solicitarInclusaoMetas" name="metas" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-link mr-2"></i>Vinculações e Classificação
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarInclusaoVinculado"><i class="fas fa-project-diagram mr-1"></i>Projeto Vinculado</label>
                                        <input type="text" class="form-control" id="solicitarInclusaoVinculado" name="projeto_vinculado" maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarInclusaoEixo"><i class="fas fa-sitemap mr-1"></i>Eixo</label>
                                        <select class="form-control" id="solicitarInclusaoEixo" name="id_eixo">
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
                                        <label for="solicitarInclusaoPriorizacao"><i class="fas fa-star mr-1"></i>Priorização GAB</label>
                                        <select class="form-control" id="solicitarInclusaoPriorizacao" name="priorizacao_gab">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="solicitarInclusaoStatus"><i class="fas fa-info-circle mr-1"></i>Status*</label>
                                        <select class="form-control" id="solicitarInclusaoStatus" name="status" required>
                                            <option value="Ativo">Ativo</option>
                                            <option value="Paralisado">Paralisado</option>
                                            <option value="Concluído">Concluído</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-comment-dots mr-2"></i>Justificativa
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="solicitarInclusaoJustificativa"><i class="fas fa-question-circle mr-1"></i>Justificativa*</label>
                                <textarea class="form-control" id="solicitarInclusaoJustificativa" name="justificativa" rows="3" required placeholder="Explique por que este projeto deve ser incluído"></textarea>
                            </div>
                        </div>
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