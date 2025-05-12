<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Configuração inicial dos campos visíveis
        var camposVisiveis = {
            'priorizacao': true,
            'etapa': true,
            'equipe': true,
            'status': true
        };

        // Mapeamento de colunas
        var mapeamentoColunas = {
            'priorizacao': 0,
            'plano': 1,
            'acao': 2,
            'meta': 3,
            'etapa': 4,
            'responsavel': 5,
            'equipe': 6,
            'status': 7,
            'data_inicio': 8,
            'data_fim': 9
        };

        // Adiciona a legenda antes da tabela (após os filtros)
        $('#dataTable').before(
            '<div class="mb-3 small text-muted text-center">' +
            '<i class="fas fa-star text-warning"></i> Priorizada pelo gabinete | ' +
            '<i class="far fa-star text-secondary"></i> Não priorizada' +
            '</div>'
        );

        // Inicializa o DataTable com os dados do PHP
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
            "pageLength": 10,
            "columns": [{
                    "data": "priorizacao_gab",
                    "className": "text-center align-middle",
                    "render": function(data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            return data;
                        }
                        return data == 1 ?
                            '<i class="fas fa-star text-warning fa-lg" data-toggle="tooltip" title="Priorizada pelo gabinete"></i>' :
                            '<i class="far fa-star text-secondary fa-lg" data-toggle="tooltip" title="Não priorizada"></i>';
                    },
                    "type": "num"
                },
                {
                    "data": "plano",
                    "className": "align-middle"
                },
                {
                    "data": "acao",
                    "className": "align-middle"
                },
                {
                    "data": "meta",
                    "className": "align-middle"
                },
                {
                    "data": "etapa",
                    "className": "align-middle"
                },
                {
                    "data": "responsavel",
                    "className": "align-middle"
                },
                {
                    "data": "equipe",
                    "className": "align-middle"
                },
                {
                    "data": "status",
                    "className": "text-center align-middle",
                    "render": function(data, type, row) {
                        var badge_class = '';
                        switch (data) {
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
                        return '<span class="badge badge-pill ' + badge_class + ' py-2" style="min-width: 110px; display: inline-block; text-align: center;">' + data + '</span>';
                    }
                },
                {
                    "data": "data_inicio_formatada",
                    "className": "text-center align-middle"
                },
                {
                    "data": "data_fim_formatada",
                    "className": "text-center align-middle"
                }
            ],
            "data": <?= json_encode($dados) ?>
        });

        // Atualiza a visibilidade das colunas
        function atualizarColunasVisiveis() {
            localStorage.setItem('camposVisiveis', JSON.stringify(camposVisiveis));
            $.each(mapeamentoColunas, function(campo, index) {
                dataTable.column(index).visible(camposVisiveis[campo] || false);
            });
        }

        // Carrega configuração salva
        function carregarConfiguracaoCampos() {
            var configSalva = localStorage.getItem('camposVisiveis');
            if (configSalva) {
                camposVisiveis = JSON.parse(configSalva);
            }

            $.each(camposVisiveis, function(campo, visivel) {
                $('#campo' + campo.charAt(0).toUpperCase() + campo.slice(1)).prop('checked', visivel);
            });

            atualizarColunasVisiveis();
        }

        // Inicializa a configuração de campos
        carregarConfiguracaoCampos();

        // Abrir modal de configuração
        $('#btnConfigurarCampos').click(function() {
            $('#modalConfigurarCampos').modal('show');
        });

        // Aplicar configuração de campos
        $('#btnAplicarConfigCampos').click(function() {
            $('.campo-visivel').each(function() {
                var campo = $(this).val();
                camposVisiveis[campo] = $(this).is(':checked');
            });

            atualizarColunasVisiveis();
            $('#modalConfigurarCampos').modal('hide');
        });


        // Inicializa tooltips
        $('[data-toggle="tooltip"]').tooltip();

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
                'priorizacao_gab': $('#filterPriorizacao').val(),
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

            if (!hasFilters) {
                location.reload();
                return;
            }

            $.ajax({
                type: "POST",
                url: '<?= site_url('visao-geral/filtrar') ?>',
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        dataTable.clear().rows.add(response.data).draw();

                        var info = dataTable.page.info();
                        $('.dataTables_info').html(
                            'Mostrando ' + (info.start + 1) + ' até ' +
                            info.end + ' de ' + response.totalRegistros + ' registros'
                        );

                        $('[data-toggle="tooltip"]').tooltip();
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