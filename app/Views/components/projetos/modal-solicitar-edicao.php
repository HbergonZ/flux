<div class="modal fade" id="solicitarEdicaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarEdicaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarEdicaoModalLabel">Solicitar Edição de Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarEdicao" method="post" action="<?= site_url('acoes/solicitar-edicao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_acao" id="solicitarEdicaoId">
                <input type="hidden" name="tipo" value="edicao">
                <input type="hidden" name="id_plano" value="<?= $idPlano ?>">

                <div class="modal-body">
                    <div id="alertNenhumaAlteracao" class="alert alert-warning d-none">
                        Você não fez nenhuma alteração nos campos. Modifique pelo menos um campo para enviar a solicitação.
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="solicitarEdicaoAcao">Ação*</label>
                                <input type="text" class="form-control" id="solicitarEdicaoAcao" name="acao" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="solicitarEdicaoDescricao">Descrição</label>
                                <textarea class="form-control" id="solicitarEdicaoDescricao" name="descricao" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoProjetoVinculado">Projeto Vinculado</label>
                                <input type="text" class="form-control" id="solicitarEdicaoProjetoVinculado" name="projeto_vinculado">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoEixo">Eixo</label>
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
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="solicitarEdicaoResponsaveis">Responsáveis</label>
                                <textarea class="form-control" id="solicitarEdicaoResponsaveis" name="responsaveis" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="solicitarEdicaoJustificativa">Justificativa para as alterações*</label>
                        <textarea class="form-control" id="solicitarEdicaoJustificativa" name="justificativa" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
                </div>
            </form>
        </div>
    </div>
</div>