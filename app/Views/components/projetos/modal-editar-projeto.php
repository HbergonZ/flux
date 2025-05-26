<div class="modal fade" id="editProjetoModal" tabindex="-1" role="dialog" aria-labelledby="editProjetoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProjetoModalLabel">Editar Projeto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditProjeto" method="post" action="<?= site_url("projetos/atualizar/$idPlano") ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editProjetoId">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editProjetoIdentificador">Identificador*</label>
                                <input type="text" class="form-control" id="editProjetoIdentificador" name="identificador" required maxlength="10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editProjetoNome">Nome*</label>
                                <input type="text" class="form-control" id="editProjetoNome" name="nome" required maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editProjetoDescricao">Descrição</label>
                        <textarea class="form-control" id="editProjetoDescricao" name="descricao" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editProjetoVinculado">Projeto Vinculado</label>
                                <input type="text" class="form-control" id="editProjetoVinculado" name="projeto_vinculado" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editProjetoEixo">Eixo</label>
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
                                <label for="editProjetoPriorizacao">Priorização GAB</label>
                                <select class="form-control" id="editProjetoPriorizacao" name="priorizacao_gab">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="projetoStatus">Status do Projeto*</label>
                                <select class="form-control" id="projetoStatus" name="status" required>
                                    <option value="Ativo" <?= ($projeto['status'] ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                    <option value="Paralisado" <?= ($projeto['status'] ?? 'Ativo') === 'Paralisado' ? 'selected' : '' ?>>Paralisado</option>
                                    <option value="Concluído" <?= ($projeto['status'] ?? 'Ativo') === 'Concluído' ? 'selected' : '' ?>>Concluído</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editProjetoResponsaveis">Responsáveis</label>
                        <textarea class="form-control" id="editProjetoResponsaveis" name="responsaveis" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>