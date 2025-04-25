<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Projetos Cadastrados</h1>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterName">Nome</label>
                        <input type="text" class="form-control" id="filterName" placeholder="Filtrar por nome">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterOffice">Status</label>
                        <select class="form-control" id="filterOffice">
                            <option value="">Todos</option>
                            <option value="Em andamento">Em andamento</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="New York">Paralisado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterStartDate">Data Inicial</label>
                        <input type="date" class="form-control" id="filterStartDate">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterEndDate">Data Final</label>
                        <input type="date" class="form-control" id="filterEndDate">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Projetos</h6>
            <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addProjectModal">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Incluir Projeto</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>ID do Projeto</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Data de Publicação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projetos as $projeto) :
                            $id = $projeto['id'] . '-' . str_replace(' ', '-', strtolower($projeto['nome'])); ?>
                            <tr>
                                <td class="text-center"><?= $projeto['id'] ?></td>
                                <td class="text-wrap"><?= $projeto['nome'] ?></td>
                                <td class="text-wrap"><?= $projeto['descricao'] ?></td>
                                <td class="text-center">
                                    <?php
                                    $badge_class = '';
                                    switch ($projeto['status']) {
                                        case 'Em andamento':
                                            $badge_class = 'badge-primary'; // Azul
                                            break;
                                        case 'Não iniciado':
                                            $badge_class = 'badge-secondary'; // Cinza claro
                                            break;
                                        case 'Finalizado':
                                            $badge_class = 'badge-success'; // Verde
                                            break;
                                        case 'Paralisado':
                                            $badge_class = 'badge-warning'; // Amarelo
                                            break;
                                        default:
                                            $badge_class = 'badge-light'; // Padrão
                                    }
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= $projeto['status'] ?></span>
                                </td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($projeto['data_publicacao'])) ?></td>
                                <td class="text-center">
                                    <div class="d-inline-flex">
                                        <!-- Botão Visualizar -->
                                        <a href="<?= site_url('visao-projeto/' . $projeto['id']) ?>" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Botão Editar -->
                                        <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Botão Excluir -->
                                        <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="<?= $id ?>" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- CSS do DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />

<!-- Scripts do DataTables -->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese-Brasil.json",
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "Nenhum registro encontrado",
                "info": "Mostrando página _PAGE_ de _PAGES_",
                "infoEmpty": "Nenhum registro disponível",
                "infoFiltered": "(filtrado de _MAX_ registros totais)",
                "search": "Pesquisar:",
                "paginate": {
                    "first": "Primeira",
                    "last": "Última",
                    "next": "Próxima",
                    "previous": "Anterior"
                }
            },
            "searching": false,
            "columnDefs": [{
                    "width": "10%",
                    "targets": 0
                }, // ID do Projeto
                {
                    "width": "20%",
                    "targets": 1
                }, // Nome
                {
                    "width": "30%",
                    "targets": 2
                }, // Descrição
                {
                    "width": "15%",
                    "targets": 3
                }, // Status
                {
                    "width": "15%",
                    "targets": 4
                }, // Data
                {
                    "width": "10%",
                    "targets": 5
                } // Ações
            ],
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10
        });
    });
</script>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        });

        $('#formAddProject').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#addProjectModal').modal('hide');
                        // Recarrega a página ou atualiza a tabela
                        location.reload();
                    } else {
                        alert('Erro: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Erro na requisição: ' + error);
                }
            });
        });
    });
</script>