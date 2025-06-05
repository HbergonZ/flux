<div class="modal fade" id="equipeAcaoModal" tabindex="-1" role="dialog" aria-labelledby="equipeAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="equipeAcaoModalLabel">
                    <i class="fas fa-users mr-2"></i>Gerenciar Equipe da Ação
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulário para adicionar membros -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-user-plus mr-2"></i>Adicionar Novo Membro
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="buscaUsuario">Buscar usuário:</label>
                            <input type="text" class="form-control mb-2" id="buscaUsuario" placeholder="Digite para filtrar usuários...">
                        </div>

                        <div class="form-group">
                            <label for="selectUsuarioEquipe">Selecione um usuário:</label>
                            <select class="form-control" id="selectUsuarioEquipe" size="5" style="height: auto;">
                                <option value="">Carregando usuários...</option>
                            </select>
                        </div>

                        <button class="btn btn-primary btn-block" type="button" id="btnAdicionarUsuarioEquipe">
                            <i class="fas fa-plus mr-2"></i> Adicionar à Equipe
                        </button>
                    </div>
                </div>

                <!-- Lista de membros da equipe -->
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-users mr-2"></i>Membros da Equipe
                        </h6>
                        <span class="badge badge-primary badge-pill" id="contadorMembros">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="tabelaEquipeAcao">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th class="text-center" style="width: 120px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Será preenchido via AJAX -->
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <i class="fas fa-spinner fa-spin"></i> Carregando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnVoltarEdicao">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar para Edição
                </button>
            </div>
        </div>
    </div>
</div>