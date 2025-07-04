<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<style>
    /* Verde mais claro para "Finalizado com atraso" */
    .badge-verde-claro {
        background-color: #d4edda;
        /* Tom pastel de verde */
        color: #155724;
        /* Texto verde escuro */
        border: 1px solid #c3e6cb;
        /* Borda verde mais clara */
    }

    /* Verde padrão do Bootstrap (pode ser redefinido se quiser) */
    .badge-verde-normal {
        background-color: #28a745;
        color: white;
    }
</style>

<script>
    $(document).ready(function() {
        // Configuração inicial dos campos visíveis
        var camposVisiveis = {
            'priorizacao': true,
            'plano': true,
            'projeto': true,
            'etapa': true,
            'acao': true,
            'responsaveis': true,
            'entrega_estimada': true,
            'data_inicio': true,
            'data_fim': true,
            'status': true
        };

        // Mapeamento de colunas
        var mapeamentoColunas = {
            'priorizacao': 0,
            'plano': 1,
            'projeto': 2,
            'etapa': 3,
            'acao': 4,
            'responsaveis': 5,
            'entrega_estimada': 6,
            'data_inicio': 7,
            'data_fim': 8,
            'status': 9
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
            "order": [
                [1, 'asc'], // Plano
                [2, 'asc'], // Projeto
                [3, 'asc'], // Etapa
                [4, 'asc'] // Ação
            ],
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
                    "data": "nome_projeto",
                    "className": "align-middle"
                },
                {
                    "data": "etapa",
                    "className": "align-middle"
                },
                {
                    "data": "acao",
                    "className": "align-middle"
                },
                {
                    "data": "responsaveis",
                    "className": "align-middle"
                },
                {
                    "data": "entrega_estimada_formatada",
                    "className": "text-center align-middle",
                    "type": "date",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            return data || '-';
                        }
                        // Para ordenação/filtro, retorna o valor original da data
                        return row.entrega_estimada || '';
                    }
                },
                {
                    "data": "data_inicio_formatada",
                    "className": "text-center align-middle",
                    "type": "date",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            return data || '-';
                        }
                        return row.data_inicio || '';
                    }
                },
                {
                    "data": "data_fim_formatada",
                    "className": "text-center align-middle",
                    "type": "date",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            return data || '-';
                        }
                        return row.data_fim || '';
                    }
                },
                {
                    "data": "status",
                    "className": "text-center align-middle",
                    "render": function(data, type, row) {
                        const badgeClass = {
                            'Finalizado': 'badge-success',
                            'Finalizado com atraso': 'badge-verde-claro',
                            'Em andamento': 'badge-warning',
                            'Paralisado': 'badge-dark',
                            'Atrasado': 'badge-danger',
                            'Não iniciado': 'badge-primary'
                        } [data] || 'badge-secondary';

                        return '<span class="badge badge-pill ' + badgeClass + ' py-2" style="min-width: 110px; display: inline-block; text-align: center;">' + data + '</span>';
                    }
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
                var idCampo = 'campo' + campo.charAt(0).toUpperCase() + campo.slice(1);
                $('#' + idCampo).prop('checked', visivel);
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
                'plano': $('#filterPlano').val(),
                'projeto': $('#filterProjeto').val(),
                'etapa': $('#filterEtapa').val(),
                'acao': $('#filterAcao').val(),
                'responsaveis': $('#filterResponsaveis').val(),
                'status': $('#filterStatus').val(),
                'priorizacao_gab': $('#filterPriorizacao').val(),
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

        // Carregar projetos quando um plano for selecionado
        $('#filterPlano').change(function() {
            var plano = $(this).val();

            $.ajax({
                url: '<?= site_url('visao-geral/getProjetosPorPlano') ?>',
                type: 'POST',
                data: {
                    plano: plano
                },
                dataType: 'json',
                success: function(response) {
                    var $projetoSelect = $('#filterProjeto');
                    $projetoSelect.empty().append('<option value="">Todos</option>');

                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function(index, projeto) {
                            $projetoSelect.append('<option value="' + projeto.nome_projeto + '">' + projeto.nome_projeto + '</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao carregar projetos:', error);
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