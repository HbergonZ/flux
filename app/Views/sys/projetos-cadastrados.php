<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Projetos Cadastrados</h1>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
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
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Projetos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" width="100%" cellspacing="0">
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
                        <?php
                        // Dados de exemplo - agora com 10 entradas distintas
                        $employees = [];
                        for ($i = 1; $i <= 10; $i++) {
                            $employees[] = [
                                'id' => $i,
                                'nome' => 'Projeto ' . $i,
                                'descricao' => 'Descrição do projeto ' . $i,
                                'status' => 'Em andamento',
                                'data' => date('d/m/Y'),
                                'actions' => '-',
                            ];
                        }

                        foreach ($employees as $employee) {
                            $id = $employee['id'] . '-' . str_replace(' ', '-', strtolower($employee['nome']));
                            echo '<tr>';
                            echo '<td class= "text-center">' . $employee['id'] . '</td>';
                            echo '<td class= "text-wrap">' . $employee['nome'] . '</td>';
                            echo '<td class= "text-wrap">' . $employee['descricao'] . '</td>';
                            echo '<td class= "text-center">' . $employee['status'] . '</td>';
                            echo '<td class= "text-center">' . $employee['data'] . '</td>';
                            echo '<td class="text-center">';
                            echo '<div class="d-inline-flex">';

                            // Botão Visualizar
                            echo '<button type="button" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" data-id="' . $id . '" title="Visualizar">';
                            echo '<i class="fas fa-eye"></i>';
                            echo '</button>';

                            // Botão Editar
                            echo '<button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="' . $id . '" title="Editar">';
                            echo '<i class="fas fa-edit"></i>';
                            echo '</button>';

                            // Botão Excluir
                            echo '<button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="' . $id . '" title="Excluir">';
                            echo '<i class="fas fa-trash-alt"></i>';
                            echo '</button>';

                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

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