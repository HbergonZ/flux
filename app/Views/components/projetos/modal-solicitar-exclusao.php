<div class="modal fade" id="solicitarExclusaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarExclusaoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarExclusaoModalLabel">Solicitar Exclusão de Projeto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarExclusao" method="post" action="<?= site_url('projetos/solicitar-exclusao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id_projeto" id="solicitarExclusaoId">
                <input type="hidden" name="tipo" value="exclusao">
                <input type="hidden" name="id_plano" value="<?= $idPlano ?>">

                <div class="modal-body">
                    <p>Você está solicitando a exclusão do projeto: <strong id="projetoNameToRequestDelete"></strong></p>

                    <div class="form-group">
                        <label for="solicitarExclusaoDadosAtuais">Dados Atuais</label>
                        <textarea class="form-control" id="solicitarExclusaoDadosAtuais" name="dados_atuais" rows="6" readonly></textarea>
                    </div>

                    <div class="form-group">
                        <label for="solicitarExclusaoJustificativa">Justificativa*</label>
                        <textarea class="form-control" id="solicitarExclusaoJustificativa" name="justificativa" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Enviar Solicitação</button>
                </div>
            </form>
        </div>
    </div>
</div>