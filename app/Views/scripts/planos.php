<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializa o DataTable
        var dataTable = $('#dataTable').DataTable({
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
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10
        });

        // Configuração do AJAX
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        });

        // Cadastrar novo plano
        $('#formAddPlano').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#addPlanoModal').modal('hide');
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

        // Editar plano
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            var planoId = $(this).data('id').split('-')[0];

            $.ajax({
                url: '<?= site_url('planos/editar/') ?>' + planoId,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        $('#editPlanoId').val(response.data.id);
                        $('#editPlanoName').val(response.data.nome);
                        $('#editPlanoSigla').val(response.data.sigla);
                        $('#editPlanoDescription').val(response.data.descricao);
                        $('#editPlanoModal').modal('show');
                    } else {
                        alert(response.message || "Erro ao carregar plano");
                    }
                },
                error: function(xhr, status, error) {
                    alert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Atualizar plano
        $('#formEditPlano').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: '<?= site_url('planos/atualizar') ?>',
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#editPlanoModal').modal('hide');
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

        // Excluir plano
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var planoId = $(this).data('id').split('-')[0];
            var planoName = $(this).closest('tr').find('td:first').text();

            $('#deletePlanoId').val(planoId);
            $('#planoNameToDelete').text(planoName);
            $('#deletePlanoModal').modal('show');
        });

        // Confirmar exclusão
        $('#formDeletePlano').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: '<?= site_url('planos/excluir') ?>',
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#deletePlanoModal').modal('hide');
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

        // Aplicar filtros
        $('#formFiltros').submit(function(e) {
            e.preventDefault();

            var hasFilters = false;
            $(this).find('input').each(function() {
                if ($(this).val() !== '' && $(this).val() !== null) {
                    hasFilters = true;
                    return false;
                }
            });

            if (!hasFilters) {
                location.reload();
                return;
            }

            $.ajax({
                type: "POST",
                url: '<?= site_url('planos/filtrar') ?>',
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        dataTable.destroy();
                        $('#dataTable tbody').empty();

                        $.each(response.data, function(index, plano) {
                            var id = plano.id + '-' + plano.nome.toLowerCase().replace(/\s+/g, '-');

                            var row = '<tr>' +
                                '<td class="text-wrap">' + plano.nome + '</td>' +
                                '<td class="text-center">' + plano.sigla + '</td>' +
                                '<td class="text-wrap">' + (plano.descricao || '') + '</td>' +
                                '<td class="text-center">' +
                                '<div class="d-inline-flex">' +
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

                        dataTable = $('#dataTable').DataTable({
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
                            "responsive": true,
                            "autoWidth": false,
                            "lengthMenu": [5, 10, 25, 50, 100],
                            "pageLength": 10
                        });
                    } else {
                        alert('Erro ao filtrar planos: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Erro na requisição: ' + error);
                }
            });
        });

        // Limpar filtros
        $('#btnLimparFiltros').click(function() {
            $('#formFiltros')[0].reset();
            $('#formFiltros').submit();
        });
    });
</script>