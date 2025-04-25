<!-- Scripts do DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

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
                },
                {
                    responsivePriority: 2,
                    targets: 1
                },
                {
                    responsivePriority: 3,
                    targets: 3
                },
                {
                    responsivePriority: 4,
                    targets: 9
                },
                {
                    responsivePriority: 5,
                    targets: 10
                },
                {
                    className: 'text-center',
                    targets: [0, 2, 4, 5, 6, 7, 8, 9, 10]
                }
            ],
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
            "searching": false
        });

        // Configuração do AJAX
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        });

        // Função para aplicar filtros
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

            // Mostra loading
            /* $('#dataTable').closest('.card-body').append('<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>'); */

            var idProjeto = $('input[name="id_projeto"]').val();

            $.ajax({
                type: "POST",
                url: '<?= site_url('visao-projeto/filtrar/') ?>' + idProjeto,
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    // Remove loading
                    $('.overlay').remove();

                    if (response.success) {
                        // Limpa a tabela
                        dataTable.clear().draw();

                        // Adiciona os novos registros
                        $.each(response.data, function(index, etapa) {
                            var id = etapa.id_etapa + '-' + etapa.id_acao;
                            var badge_class = getBadgeClass(etapa.status);

                            // Usa as datas já formatadas pelo servidor
                            var dataInicio = etapa.data_inicio_formatada || etapa.data_inicio;
                            var dataFim = etapa.data_fim_formatada || etapa.data_fim;

                            dataTable.row.add([
                                'ETP-' + etapa.id_etapa,
                                etapa.etapa,
                                'ACT-' + etapa.id_acao,
                                etapa.acao,
                                etapa.coordenacao,
                                etapa.responsavel,
                                etapa.tempo_estimado_dias + ' dias',
                                dataInicio,
                                dataFim,
                                '<span class="badge ' + badge_class + '">' + etapa.status + '</span>',
                                getActionButtons(id)
                            ]).draw(false);
                        });
                    } else {
                        alert('Erro ao filtrar etapas: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('.overlay').remove();
                    alert('Erro na requisição: ' + error);
                }
            });
        });

        // Função para limpar filtros
        $('#btnLimparFiltros').click(function() {
            $('#formFiltros')[0].reset();
            $('#formFiltros').submit();
        });

        // Função auxiliar para determinar a classe do badge
        function getBadgeClass(status) {
            switch (status) {
                case 'Em andamento':
                    return 'badge-primary';
                case 'Finalizado':
                    return 'badge-success';
                case 'Paralisado':
                    return 'badge-warning';
                case 'Não iniciado':
                    return 'badge-secondary';
                default:
                    return 'badge-light';
            }
        }

        // Função auxiliar para gerar botões de ação
        function getActionButtons(id) {
            return '<div class="d-flex justify-content-center">' +
                '<button type="button" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" data-id="' + id + '" title="Detalhes">' +
                '<i class="fas fa-info-circle"></i>' +
                '</button>' +
                '<button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="' + id + '" title="Editar">' +
                '<i class="fas fa-edit"></i>' +
                '</button>' +
                '<button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="' + id + '" title="Excluir">' +
                '<i class="fas fa-trash-alt"></i>' +
                '</button>' +
                '</div>';
        }

        // Eventos para os botões de ação
        $('#dataTable').on('click', '.btn-primary[title="Editar"]', function() {
            var ids = $(this).data('id').split('-');
            var idEtapa = ids[0];
            var idAcao = ids[1];
            console.log('Editar etapa:', idEtapa, 'ação:', idAcao);
        });
    });
</script>