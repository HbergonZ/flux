<div class="modal fade" id="solicitarEdicaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarEdicaoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarEdicaoModalLabel">Solicitar Edição de Meta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarEdicao" method="post" action="<?= site_url('metas/solicitar-edicao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_meta" id="solicitarEdicaoId">
                <input type="hidden" name="tipo" value="edicao">
                <input type="hidden" name="id_acao" value="<?= $idAcao ?>">
                <input type="hidden" name="id_plano" value="<?= $acao['id_plano'] ?>">

                <div class="modal-body">
                    <div id="alertNenhumaAlteracao" class="alert alert-warning d-none">
                        Você não fez nenhuma alteração nos campos. Modifique pelo menos um campo para enviar a solicitação.
                    </div>

                    <div class="form-group">
                        <label for="solicitarEdicaoNome">Nome*</label>
                        <input type="text" class="form-control" id="solicitarEdicaoNome" name="nome" required>
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