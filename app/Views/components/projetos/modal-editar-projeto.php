<!-- Modal para Editar Projeto -->
<div class="modal fade" id="editProjectModal" tabindex="-1" role="dialog" aria-labelledby="editProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProjectModalLabel">Editar Projeto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditProject" method="post">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="editProjectId">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="editProjectName">Nome do Projeto*</label>
                        <input type="text" class="form-control" id="editProjectName" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label for="editProjectDescription">Descrição*</label>
                        <textarea class="form-control" id="editProjectDescription" name="descricao" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editProjectStatus">Status*</label>
                                <select class="form-control" id="editProjectStatus" name="status" required>
                                    <option value="Em andamento">Em andamento</option>
                                    <option value="Não iniciado">Não iniciado</option>
                                    <option value="Finalizado">Finalizado</option>
                                    <option value="Paralisado">Paralisado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editProjectPublicationDate">Data de Publicação*</label>
                                <input type="date" class="form-control" id="editProjectPublicationDate" name="data_publicacao" required>
                            </div>
                        </div>
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