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
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json",
                "emptyTable": "Nenhum dado disponível na tabela",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros no total)",
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "loadingRecords": "Carregando...",
                "processing": "Processando...",
                "search": "Pesquisar:",
                "zeroRecords": "Nenhum registro correspondente encontrado",
                "paginate": {
                    "first": "Primeira",
                    "last": "Última",
                    "next": "Próxima",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": ativar para ordenar coluna ascendente",
                    "sortDescending": ": ativar para ordenar coluna descendente"
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

        // Cadastrar nova etapa
        $('#formAddEtapa').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#addEtapaModal').modal('hide');
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

        // Editar etapa
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            var etapaId = $(this).data('id').split('-')[0];

            $.ajax({
                url: '<?= site_url('etapas/editar/') ?>' + etapaId,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        $('#editEtapaId').val(response.data.id_etapa);
                        $('#editEtapaNome').val(response.data.etapa);
                        $('#editEtapaAcao').val(response.data.acao);
                        $('#editEtapaResponsavel').val(response.data.responsavel);
                        $('#editEtapaEquipe').val(response.data.equipe);
                        $('#editEtapaTempoEstimado').val(response.data.tempo_estimado_dias);
                        $('#editEtapaDataInicio').val(response.data.data_inicio);
                        $('#editEtapaDataFim').val(response.data.data_fim);
                        $('#editEtapaStatus').val(response.data.status);
                        $('#editEtapaModal').modal('show');
                    } else {
                        alert(response.message || "Erro ao carregar etapa");
                    }
                },
                error: function(xhr, status, error) {
                    alert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Atualizar etapa
        $('#formEditEtapa').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#editEtapaModal').modal('hide');
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

        // Excluir etapa
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var etapaId = $(this).data('id').split('-')[0];
            var etapaName = $(this).closest('tr').find('td:first').text();

            $('#deleteEtapaId').val(etapaId);
            $('#etapaNameToDelete').text(etapaName);
            $('#deleteEtapaModal').modal('show');
        });

        // Confirmar exclusão
        $('#formDeleteEtapa').submit(function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#deleteEtapaModal').modal('hide');
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
            $(this).find('input, select').each(function() {
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
                url: '<?= site_url("etapas/filtrar/$tipo/$idVinculo") ?>',
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        dataTable.destroy();
                        $('#dataTable tbody').empty();

                        $.each(response.data, function(index, etapa) {
                            var id = etapa.id_etapa + '-' + etapa.etapa.toLowerCase().replace(/\s+/g, '-');

                            var badge_class = '';
                            switch (etapa.status) {
                                case 'Em andamento':
                                    badge_class = 'badge-primary';
                                    break;
                                case 'Finalizado':
                                    badge_class = 'badge-success';
                                    break;
                                case 'Paralisado':
                                    badge_class = 'badge-warning';
                                    break;
                                case 'Não iniciado':
                                    badge_class = 'badge-secondary';
                                    break;
                            }

                            var row = '<tr>' +
                                '<td class="text-wrap align-middle">' + etapa.etapa + '</td>' +
                                '<td class="text-wrap align-middle">' + etapa.acao + '</td>' +
                                '<td class="text-wrap align-middle">' + etapa.responsavel + '</td>' +
                                '<td class="text-wrap align-middle">' + etapa.equipe + '</td>' +
                                '<td class="text-center align-middle">' + (etapa.tempo_estimado_dias ? etapa.tempo_estimado_dias + ' dias' : '') + '</td>' +
                                '<td class="text-center align-middle">' + (etapa.data_inicio ? formatDate(etapa.data_inicio) : '') + '</td>' +
                                '<td class="text-center align-middle">' + (etapa.data_fim ? formatDate(etapa.data_fim) : '') + '</td>' +
                                '<td class="text-center align-middle"><span class="badge ' + badge_class + '">' + etapa.status + '</span></td>' +
                                '<td class="text-center align-middle">' +
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
                                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json",
                                "emptyTable": "Nenhum dado disponível na tabela",
                                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                                "infoFiltered": "(filtrado de _MAX_ registros no total)",
                                "lengthMenu": "Mostrar _MENU_ registros por página",
                                "loadingRecords": "Carregando...",
                                "processing": "Processando...",
                                "search": "Pesquisar:",
                                "zeroRecords": "Nenhum registro correspondente encontrado",
                                "paginate": {
                                    "first": "Primeira",
                                    "last": "Última",
                                    "next": "Próxima",
                                    "previous": "Anterior"
                                },
                                "aria": {
                                    "sortAscending": ": ativar para ordenar coluna ascendente",
                                    "sortDescending": ": ativar para ordenar coluna descendente"
                                }
                            },
                            "searching": false,
                            "responsive": true,
                            "autoWidth": false,
                            "lengthMenu": [5, 10, 25, 50, 100],
                            "pageLength": 10
                        });
                    } else {
                        alert('Erro ao filtrar etapas: ' + response.message);
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

        // Função para formatar data
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }
    });
</script>