<div class="modal fade" id="addProjetoModal" tabindex="-1" role="dialog" aria-labelledby="addProjetoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProjetoModalLabel">Incluir Novo Projeto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddProjeto" action="<?= site_url("projetos/cadastrar/$idPlano") ?>" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="projetoIdentificador">Identificador*</label>
                                <input type="text" class="form-control" id="projetoIdentificador" name="identificador" required maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="projetoNome">Nome*</label>
                                <input type="text" class="form-control" id="projetoNome" name="nome" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="projetoDescricao">Descrição</label>
                        <textarea class="form-control" id="projetoDescricao" name="descricao" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="solicitarEdicaoMetas"><i class="fas fa-bullseye mr-1"></i>Metas</label>
                        <textarea class="form-control" id="solicitarEdicaoMetas" name="metas" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="projetoVinculado">Projeto Vinculado</label>
                                <input type="text" class="form-control" id="projetoVinculado" name="projeto_vinculado" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="projetoEixo">Eixo</label>
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
                                <label for="projetoPriorizacao">Priorização GAB</label>
                                <select class="form-control" id="projetoPriorizacao" name="priorizacao_gab">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="projetoResponsaveis">Responsáveis</label>
                        <textarea class="form-control" id="projetoResponsaveis" name="responsaveis" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Projeto</button>
                </div>
            </form>
        </div>
    </div>
</div>