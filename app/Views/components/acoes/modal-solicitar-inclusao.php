<div class="modal fade" id="solicitarInclusaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarInclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarInclusaoModalLabel">Solicitar Inclusão de Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarInclusao" method="post" action="<?= site_url('acoes/solicitar-inclusao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="tipo" value="inclusao">
                <input type="hidden" name="id_plano" value="<?= $idPlano ?>">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="solicitarInclusaoAcao">Ação*</label>
                                <input type="text" class="form-control" id="solicitarInclusaoAcao" name="acao" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="solicitarInclusaoDescricao">Descrição</label>
                                <textarea class="form-control" id="solicitarInclusaoDescricao" name="descricao" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarInclusaoProjetoVinculado">Projeto Vinculado</label>
                                <input type="text" class="form-control" id="solicitarInclusaoProjetoVinculado" name="projeto_vinculado">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarInclusaoEixo">Eixo</label>
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
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="solicitarInclusaoResponsaveis">Responsáveis</label>
                                <textarea class="form-control" id="solicitarInclusaoResponsaveis" name="responsaveis" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="solicitarInclusaoJustificativa">Justificativa*</label>
                        <textarea class="form-control" id="solicitarInclusaoJustificativa" name="justificativa" rows="3" required></textarea>
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