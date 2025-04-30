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

        // Aplicar filtros
        $('#formFiltros').submit(function(e) {
            e.preventDefault();

            var formData = {
                'plano': $('#filterPlano').val(),
                'acao': $('#filterAcao').val(),
                'meta': $('#filterMeta').val(),
                'etapa': $('#filterEtapa').val(),
                'responsavel': $('#filterResponsavel').val(),
                'equipe': $('#filterEquipe').val(),
                'status': $('#filterStatus').val(),
                'data_inicio': $('#filterStartDate').val(),
                'data_fim': $('#filterEndDate').val()
            };

            // Verifica se há filtros aplicados
            var hasFilters = false;
            $.each(formData, function(key, value) {
                if (value !== '' && value !== null) {
                    hasFilters = true;
                    return false;
                }
            });

            // Se não houver filtros, apenas recarrega a página
            if (!hasFilters) {
                location.reload();
                return;
            }

            $.ajax({
                type: "POST",
                url: '<?= site_url('visao-geral/filtrar') ?>',
                data: formData,
                dataType: "json",
                // No success do AJAX, adicione:
                success: function(response) {
                    if (response.success) {
                        // Destroi a tabela atual
                        if ($.fn.DataTable.isDataTable('#dataTable')) {
                            dataTable.destroy();
                        }

                        // Limpa o corpo da tabela
                        $('#dataTable tbody').empty();

                        // Adiciona os novos registros filtrados
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, registro) {
                                var badge_class = '';

                                switch (registro.status) {
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
                                    '<td class="text-wrap">' + (registro.plano || '') + '</td>' +
                                    '<td class="text-wrap">' + (registro.acao || '') + '</td>' +
                                    '<td class="text-wrap">' + (registro.meta || '') + '</td>' +
                                    '<td class="text-wrap">' + (registro.etapa || '') + '</td>' +
                                    '<td class="text-wrap">' + (registro.responsavel || '') + '</td>' +
                                    '<td class="text-wrap">' + (registro.equipe || '') + '</td>' +
                                    '<td class="text-center"><span class="badge ' + badge_class + '">' + registro.status + '</span></td>' +
                                    '<td class="text-center">' + (registro.data_inicio_formatada || '') + '</td>' +
                                    '<td class="text-center">' + (registro.data_fim_formatada || '') + '</td>' +
                                    '</tr>';

                                $('#dataTable tbody').append(row);
                            });
                        } else {
                            $('#dataTable tbody').append('<tr><td colspan="9" class="text-center">Nenhum registro encontrado</td></tr>');
                        }

                        // Re-inicializa o DataTable
                        dataTable = $('#dataTable').DataTable({
                            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese-Brasil.json",
                                "lengthMenu": "Mostrar _MENU_ registros por página",
                                "zeroRecords": "Nenhum registro encontrado",
                                "info": "Mostrando de _START_ até _END_ de " + response.totalRegistros + " registros",
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
                            "pageLength": 10,
                            "initComplete": function(settings, json) {
                                // Atualiza manualmente a informação de contagem
                                var api = this.api();
                                var pageInfo = api.page.info();
                                var total = response.totalRegistros;
                                var info = api.page.info();

                                $(api.table().container()).find('.dataTables_info').html(
                                    'Mostrando ' + (info.start + 1) + ' até ' +
                                    (info.end) + ' de ' + total + ' registros'
                                );
                            }
                        });

                        // Força a atualização da contagem após a paginação
                        dataTable.on('draw.dt', function() {
                            var api = dataTable.api();
                            var pageInfo = api.page.info();
                            var total = response.totalRegistros;

                            $(api.table().container()).find('.dataTables_info').html(
                                'Mostrando ' + (pageInfo.start + 1) + ' até ' +
                                (pageInfo.end) + ' de ' + total + ' registros'
                            );
                        });
                    } else {
                        alert('Erro ao filtrar registros: ' + response.message);
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