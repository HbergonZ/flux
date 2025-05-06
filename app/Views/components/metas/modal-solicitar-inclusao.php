<div class="modal fade" id="solicitarInclusaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarInclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarInclusaoModalLabel">Solicitar Inclusão de Meta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarInclusao" method="post" action="<?= site_url('metas/solicitar-inclusao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="tipo" value="inclusao">
                <input type="hidden" name="id_acao" value="<?= $idAcao ?>">
                <input type="hidden" name="id_plano" value="<?= $acao['id_plano'] ?>">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="solicitarInclusaoNome">Nome*</label>
                        <input type="text" class="form-control" id="solicitarInclusaoNome" name="nome" required>
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