<!-- Modal para Adicionar Projeto -->
<div class="modal fade" id="addProjectModal" tabindex="-1" role="dialog" aria-labelledby="addProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProjectModalLabel">Incluir Novo Projeto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddProject" action="<?= site_url('projetos-cadastrados/cadastrar') ?>" method="post">
                <!-- Token CSRF -->
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

                <div class="modal-body">
                    <div class="form-group">
                        <label for="projectName">Nome do Projeto*</label>
                        <input type="text" class="form-control" id="projectName" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label for="projectDescription">Descrição*</label>
                        <textarea class="form-control" id="projectDescription" name="descricao" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="projectStatus">Status*</label>
                                <select class="form-control" id="projectStatus" name="status" required>
                                    <option value="">Selecione...</option>
                                    <option value="Em andamento">Em andamento</option>
                                    <option value="Não iniciado">Não iniciado</option>
                                    <option value="Finalizado">Finalizado</option>
                                    <option value="Paralisado">Paralisado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="projectPublicationDate">Data de Publicação*</label>
                                <input type="date" class="form-control" id="projectPublicationDate" name="data_publicacao" required>
                            </div>
                        </div>
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