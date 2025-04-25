<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Etapas e Ações Cadastradas</h1>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterStage">Etapa</label>
                        <input type="text" class="form-control" id="filterStage" placeholder="Filtrar por etapa">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterAction">Ação</label>
                        <input type="text" class="form-control" id="filterAction" placeholder="Filtrar por ação">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterStatus">Status</label>
                        <select class="form-control" id="filterStatus">
                            <option value="">Todos</option>
                            <option value="Em andamento">Em andamento</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Paralisado">Paralisado</option>
                            <option value="Não iniciado">Não iniciado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterResponsible">Responsável</label>
                        <input type="text" class="form-control" id="filterResponsible" placeholder="Filtrar por responsável">
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterStartDate">Data Início</label>
                        <input type="date" class="form-control" id="filterStartDate">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterEndDate">Data Fim</label>
                        <input type="date" class="form-control" id="filterEndDate">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filterCoordination">Coordenação</label>
                        <input type="text" class="form-control" id="filterCoordination" placeholder="Filtrar por coordenação">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Etapas e Ações</h6>
            <a href="#" class="btn btn-primary btn-icon-split btn-sm" data-toggle="modal" data-target="#addStageModal">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Incluir Etapa/Ação</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>ID Etapa</th>
                            <th>Etapa</th>
                            <th>ID Ação</th>
                            <th>Ação</th>
                            <th>Coordenação</th>
                            <th>Responsável</th>
                            <th>Tempo Estimado</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Dados de exemplo
                        $stages = [];
                        for ($i = 1; $i <= 20; $i++) {
                            $stages[] = [
                                'id_etapa' => 'ETP-' . $i,
                                'etapa' => 'Etapa ' . $i,
                                'id_acao' => 'ACT-' . $i,
                                'acao' => 'Ação ' . $i,
                                'coordenacao' => 'Coordenação ' . ($i % 3 + 1),
                                'responsavel' => 'Responsável ' . $i,
                                'tempo_estimado' => rand(5, 30),
                                'inicio' => date('d/m/Y', strtotime('-' . rand(0, 30) . ' days')),
                                'fim' => date('d/m/Y', strtotime('+' . rand(10, 60) . ' days')),
                                'status' => ['Em andamento', 'Finalizado', 'Paralisado', 'Não iniciado'][rand(0, 3)],
                                'observacoes' => 'Observações sobre a etapa ' . $i,
                                'detalhe_status' => 'Detalhes do status ' . $i,
                                'evidencia' => 'Evidência ' . $i
                            ];
                        }

                        foreach ($stages as $stage) {
                            $id = $stage['id_etapa'] . '-' . $stage['id_acao'];
                            echo '<tr>';
                            echo '<td class="text-center">' . $stage['id_etapa'] . '</td>';
                            echo '<td class="text-wrap">' . $stage['etapa'] . '</td>';
                            echo '<td class="text-center">' . $stage['id_acao'] . '</td>';
                            echo '<td class="text-wrap">' . $stage['acao'] . '</td>';
                            echo '<td class="text-center">' . $stage['coordenacao'] . '</td>';
                            echo '<td class="text-center">' . $stage['responsavel'] . '</td>';
                            echo '<td class="text-center">' . $stage['tempo_estimado'] . '</td>';
                            echo '<td class="text-center">' . $stage['inicio'] . '</td>';
                            echo '<td class="text-center">' . $stage['fim'] . '</td>';
                            echo '<td class="text-center">';

                            // Adicionando badge colorido conforme o status
                            $badge_class = '';
                            switch ($stage['status']) {
                                case 'Em andamento':
                                    $badge_class = 'badge-primary';
                                    break;
                                case 'Finalizado':
                                    $badge_class = 'badge-success';
                                    break;
                                case 'Paralisado':
                                    $badge_class = 'badge-warning';
                                    break;
                                case 'Não iniciado':
                                    $badge_class = 'badge-secondary';
                                    break;
                                default:
                                    $badge_class = 'badge-light';
                            }
                            echo '<span class="badge ' . $badge_class . '">' . $stage['status'] . '</span>';

                            echo '</td>';
                            echo '<td class="text-center">';
                            echo '<div class="d-inline-flex">';

                            // Botão Visualizar (agora com ícone de informações)
                            echo '<button type="button" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" data-id="' . $id . '" title="Detalhes">';
                            echo '<i class="fas fa-info-circle"></i>';
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

<!-- Adicione estas bibliotecas no cabeçalho ou antes do fechamento do body -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

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
            "responsive": {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            "columnDefs": [{
                    className: 'control',
                    orderable: false,
                    targets: -1
                },
                {
                    responsivePriority: 1,
                    targets: 0
                }, // ID Etapa
                {
                    responsivePriority: 2,
                    targets: 1
                }, // Etapa
                {
                    responsivePriority: 3,
                    targets: 3
                }, // Ação
                {
                    responsivePriority: 4,
                    targets: 9
                }, // Status
                {
                    responsivePriority: 5,
                    targets: 10
                } // Ações
            ],
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10
        });
    });
</script>