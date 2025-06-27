<div class="modal fade" id="addProjetoModal" tabindex="-1" role="dialog" aria-labelledby="addProjetoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i>Incluir Novo Projeto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddProjeto" action="<?= site_url("projetos/cadastrar/$idPlano") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

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
                                        <label for="projetoIdentificador"><i class="fas fa-hashtag mr-1"></i>Identificador*</label>
                                        <input type="text" class="form-control" id="projetoIdentificador" name="identificador" required maxlength="10">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="projetoNome"><i class="fas fa-tag mr-1"></i>Nome*</label>
                                        <input type="text" class="form-control" id="projetoNome" name="nome" required maxlength="255">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="projetoDescricao"><i class="fas fa-align-left mr-1"></i>Descrição</label>
                                <textarea class="form-control" id="projetoDescricao" name="descricao" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="projetoMetas"><i class="fas fa-bullseye mr-1"></i>Metas</label>
                                <textarea class="form-control" id="projetoMetas" name="metas" rows="3"></textarea>
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
                                        <label for="projetoVinculado"><i class="fas fa-project-diagram mr-1"></i>Projeto Vinculado</label>
                                        <input type="text" class="form-control" id="projetoVinculado" name="projeto_vinculado" maxlength="255">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="projetoEixo"><i class="fas fa-sitemap mr-1"></i>Eixo</label>
                                        <select class="form-control" id="projetoEixo" name="id_eixo">
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
                                        <label for="projetoPriorizacao"><i class="fas fa-star mr-1"></i>Priorização GAB</label>
                                        <select class="form-control" id="projetoPriorizacao" name="priorizacao_gab">
                                            <option value="0">Não</option>
                                            <option value="1">Sim</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="projetoStatus"><i class="fas fa-info-circle mr-1"></i>Status*</label>
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

                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-users mr-2"></i>Responsáveis
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="projetoResponsaveis"><i class="fas fa-user-friends mr-1"></i>Responsáveis</label>
                                <textarea class="form-control" id="projetoResponsaveis" name="responsaveis" rows="2" placeholder="Liste os responsáveis separados por vírgula"></textarea>
                                <small class="text-muted">Para adicionar/remover responsáveis individualmente, edite o projeto após a criação</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Salvar Projeto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>