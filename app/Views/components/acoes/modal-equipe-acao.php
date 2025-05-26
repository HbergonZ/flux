<div class="modal fade" id="equipeAcaoModal" tabindex="-1" role="dialog" aria-labelledby="equipeAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="equipeAcaoModalLabel">Gerenciar Equipe da Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="form-group">
                        <label for="buscaUsuario">Buscar usuário:</label>
                        <input type="text" class="form-control mb-2" id="buscaUsuario" placeholder="Digite para filtrar usuários...">

                        <label for="selectUsuarioEquipe">Selecione um usuário para adicionar à equipe:</label>
                        <select class="form-control" id="selectUsuarioEquipe" size="5" style="height: auto;">
                            <option value="">Carregando usuários...</option>
                        </select>
                    </div>
                    <button class="btn btn-primary mt-2" type="button" id="btnAdicionarUsuarioEquipe">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="tabelaEquipeAcao">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>E-mail</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Será preenchido via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>