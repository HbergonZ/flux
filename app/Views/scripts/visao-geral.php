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

            // Verifica se há filtros aplicados
            var hasFilters = false;
            $(this).find('input, select').each(function() {
                if ($(this).val() !== '' && $(this).val() !== null) {
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
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Destroi a tabela atual
                        dataTable.destroy();

                        // Limpa o corpo da tabela
                        $('#dataTable tbody').empty();

                        // Adiciona os novos registros filtrados
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
                                '<td class="text-wrap">' + (registro.coordenacao || '') + '</td>' +
                                '<td class="text-wrap">' + (registro.responsavel_etapa || '') + '</td>' +
                                '<td class="text-center"><span class="badge ' + badge_class + '">' + registro.status + '</span></td>' +
                                '<td class="text-center">' + (registro.data_inicio_formatada || '') + '</td>' +
                                '<td class="text-center">' + (registro.data_fim_formatada || '') + '</td>' +
                                '</tr>';

                            $('#dataTable tbody').append(row);
                        });

                        // Re-inicializa o DataTable
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