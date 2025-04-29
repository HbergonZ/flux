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

        // Cadastrar novo projeto
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

        // Editar projeto
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            // Extrai o ID do projeto (formato: "1-nome-projeto")
            var projectId = $(this).data('id').split('-')[0];

            // Configuração do AJAX com CSRF
            $.ajax({
                url: '<?= site_url('projetos-cadastrados/editar/') ?>' + projectId,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                beforeSend: function() {
                    // Opcional: Mostrar loader
                    console.log("Carregando dados do projeto...");
                },
                success: function(response) {
                    console.log("Resposta:", response);

                    if (response.success && response.data) {
                        // Preenche o formulário do modal
                        $('#editProjectId').val(response.data.id);
                        $('#editProjectName').val(response.data.nome);
                        $('#editProjectObjective').val(response.data.objetivo);
                        $('#editProjectPerspective').val(response.data.perspectiva_estrategica);
                        $('#editProjectStakeholders').val(response.data.interessados);
                        $('#editProjectStatus').val(response.data.status);

                        // Formata a data (YYYY-MM-DD para o input date)
                        if (response.data.data_publicacao) {
                            var dataParts = response.data.data_publicacao.split(' ')[0].split('-');
                            var formattedDate = dataParts[0] + '-' + dataParts[1] + '-' + dataParts[2];
                            $('#editProjectPublicationDate').val(formattedDate);
                        }

                        // Abre o modal
                        $('#editProjectModal').modal('show');
                    } else {
                        alert(response.message || "Erro ao carregar projeto");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erro AJAX:", {
                        Status: xhr.status,
                        StatusText: xhr.statusText,
                        Response: xhr.responseText
                    });
                    alert("Falha na comunicação com o servidor. Verifique o console.");
                }
            });
        });

        // Atualizar projeto
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

        // Excluir projeto
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var projectId = $(this).data('id').split('-')[0];
            var projectName = $(this).closest('tr').find('td:nth-child(2)').text();

            $('#deleteProjectId').val(projectId);
            $('#projectNameToDelete').text(projectName);
            $('#deleteProjectModal').modal('show');
        });

        // Confirmar exclusão
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

            // Mostra loading
            /* $('#dataTable').closest('.card-body').append('<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>'); */

            $.ajax({
                type: "POST",
                url: '<?= site_url('projetos-cadastrados/filtrar') ?>',
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    // Remove loading
                    $('.overlay').remove();

                    if (response.success) {
                        // Destroi a tabela atual
                        dataTable.destroy();

                        // Limpa o corpo da tabela
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

                            // Formata a data corretamente
                            var dataFormatada = projeto.data_formatada || formatDate(projeto.data_publicacao);

                            var row = '<tr>' +
                                '<td class="text-wrap">' + projeto.nome + '</td>' +
                                '<td class="text-wrap">' + projeto.objetivo + '</td>' +
                                '<td class="text-wrap">' + (projeto.perspectiva_estrategica || '') + '</td>' +
                                '<td class="text-wrap">' + (projeto.interessados || '') + '</td>' +
                                '<td class="text-center"><span class="badge ' + badge_class + '">' + projeto.status + '</span></td>' +
                                '<td class="text-center">' + dataFormatada + '</td>' +
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
                            "columnDefs": [{
                                "targets": 0
                            }, {
                                "targets": 1
                            }, {
                                "targets": 2
                            }, {
                                "targets": 3
                            }, {
                                "targets": 4
                            }, {
                                "targets": 5
                            }, {
                                "targets": 6
                            }],
                            "responsive": true,
                            "autoWidth": false,
                            "lengthMenu": [5, 10, 25, 50, 100],
                            "pageLength": 10
                        });
                    } else {
                        alert('Erro ao filtrar projetos: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('.overlay').remove();
                    alert('Erro na requisição: ' + error);
                }
            });
        });

        // Limpar filtros
        $('#btnLimparFiltros').click(function() {
            $('#formFiltros')[0].reset();
            $('#formFiltros').submit();
        });

        // Função para formatar data corretamente (sem alterar o valor)
        function formatDate(dateString) {
            if (!dateString) return '';

            // Se já estiver no formato dd/mm/yyyy, retorna como está
            if (dateString.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                return dateString;
            }

            // Se for uma data ISO (yyyy-mm-dd)
            if (dateString.match(/^\d{4}-\d{2}-\d{2}(?:T|$)/)) {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return dateString;
                }

                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }

            // Para outros formatos, tenta converter
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return dateString;
                }

                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            } catch (e) {
                return dateString;
            }
        }
    });
</script>