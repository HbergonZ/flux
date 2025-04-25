<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- CSS do DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />

<!-- Scripts do DataTables -->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

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
    // Função para editar projeto
    $(document).on('click', '.btn-primary[title="Editar"]', function() {
        var projectId = $(this).data('id').split('-')[0]; // Pega apenas o ID numérico

        $.get('<?= site_url('projetos-cadastrados/editar/') ?>' + projectId, function(response) {
            if (response.success) {
                $('#editProjectId').val(response.data.id);
                $('#editProjectName').val(response.data.nome);
                $('#editProjectDescription').val(response.data.descricao);
                $('#editProjectStatus').val(response.data.status);
                $('#editProjectPublicationDate').val(response.data.data_publicacao);

                $('#editProjectModal').modal('show');
            } else {
                alert(response.message);
            }
        }).fail(function() {
            alert('Erro ao carregar dados do projeto');
        });
    });

    // Submit do formulário de edição
    $('#formEditProject').submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: '<?= site_url('projetos-cadastrados/atualizar') ?>',
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('#editProjectModal').modal('hide');
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

    // Função para excluir projeto
    $(document).on('click', '.btn-danger[title="Excluir"]', function() {
        var projectId = $(this).data('id').split('-')[0]; // Pega apenas o ID numérico
        var projectName = $(this).closest('tr').find('td:nth-child(2)').text();

        $('#deleteProjectId').val(projectId);
        $('#projectNameToDelete').text(projectName);
        $('#deleteProjectModal').modal('show');
    });

    // Submit do formulário de exclusão
    $('#formDeleteProject').submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: '<?= site_url('projetos-cadastrados/excluir') ?>',
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('#deleteProjectModal').modal('hide');
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
    // Função para aplicar filtros
    $('#formFiltros').submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: '<?= site_url('projetos-cadastrados/filtrar') ?>',
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // Limpa a tabela
                    $('#dataTable tbody').empty();

                    // Adiciona os novos registros filtrados
                    $.each(response.data, function(index, projeto) {
                        var id = projeto.id + '-' + projeto.nome.toLowerCase().replace(/\s+/g, '-');
                        var badge_class = '';

                        switch (projeto.status) {
                            case 'Em andamento':
                                badge_class = 'badge-primary';
                                break;
                            case 'Não iniciado':
                                badge_class = 'badge-secondary';
                                break;
                            case 'Finalizado':
                                badge_class = 'badge-success';
                                break;
                            case 'Paralisado':
                                badge_class = 'badge-warning';
                                break;
                            default:
                                badge_class = 'badge-light';
                        }

                        var row = '<tr>' +
                            '<td class="text-center">' + projeto.id + '</td>' +
                            '<td class="text-wrap">' + projeto.nome + '</td>' +
                            '<td class="text-wrap">' + projeto.descricao + '</td>' +
                            '<td class="text-center"><span class="badge ' + badge_class + '">' + projeto.status + '</span></td>' +
                            '<td class="text-center">' + new Date(projeto.data_publicacao).toLocaleDateString('pt-BR') + '</td>' +
                            '<td class="text-center">' +
                            '<div class="d-inline-flex">' +
                            '<a href="<?= site_url('visao-projeto/') ?>' + projeto.id + '" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar">' +
                            '<i class="fas fa-eye"></i>' +
                            '</a>' +
                            '<button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="' + id + '" title="Editar">' +
                            '<i class="fas fa-edit"></i>' +
                            '</button>' +
                            '<button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="' + id + '" title="Excluir">' +
                            '<i class="fas fa-trash-alt"></i>' +
                            '</button>' +
                            '</div>' +
                            '</td>' +
                            '</tr>';

                        $('#dataTable tbody').append(row);
                    });

                    // Reaplica os eventos de edição/exclusão
                    aplicarEventosBotoes();
                } else {
                    alert('Erro ao filtrar projetos');
                }
            },
            error: function(xhr, status, error) {
                alert('Erro na requisição: ' + error);
            }
        });
    });

    // Função para reaplicar eventos após filtragem
    function aplicarEventosBotoes() {
        // Remove eventos antigos para evitar duplicação
        $('.btn-primary[title="Editar"]').off('click');
        $('.btn-danger[title="Excluir"]').off('click');

        // Aplica os eventos novamente
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            var projectId = $(this).data('id').split('-')[0];
            // ... (código existente de edição)
        });

        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var projectId = $(this).data('id').split('-')[0];
            // ... (código existente de exclusão)
        });
    }

    // Limpar filtros - MODIFICAÇÃO AQUI
    $('#btnLimparFiltros').click(function() {
        // Limpa o formulário
        $('#formFiltros')[0].reset();

        // Dispara o evento submit do formulário para recarregar os dados
        $('#formFiltros').submit();
    });
</script>