<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        // Modal de confirmação de exclusão com verificação de relacionamentos
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var btn = $(this);
            var planoId = btn.data('id').split('-')[0];
            var planoName = btn.closest('tr').find('td:first').text();

            // Verificar se há relacionamentos
            $.ajax({
                url: '<?= site_url('planos/verificar-relacionamentos/') ?>' + planoId,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                },
                success: function(response) {
                    if (response.success) {
                        var totalRelacionamentos = response.contagem.projetos + response.contagem.etapas + response.contagem.acoes;

                        if (totalRelacionamentos > 0) {
                            // Exibir alerta de confirmação com detalhes
                            Swal.fire({
                                title: 'Atenção! Exclusão em Cascata',
                                html: `Você está prestes a excluir o plano <strong>${planoName}</strong> e <strong>TODOS</strong> os seus relacionamentos:<br><br>
                                   <ul class="text-left">
                                       <li>Projetos: ${response.contagem.projetos}</li>
                                       <li>Etapas: ${response.contagem.etapas}</li>
                                       <li>Ações: ${response.contagem.acoes}</li>
                                   </ul>
                                   <p class="text-danger mt-2"><strong>Esta ação é irreversível!</strong></p>`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Sim, excluir tudo!',
                                cancelButtonText: 'Cancelar',
                                width: '600px'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#deletePlanoId').val(planoId);
                                    $('#planoNameToDelete').text(planoName);
                                    $('#deletePlanoModal').modal('show');
                                }
                            });
                        } else {
                            // Exibir confirmação simples se não houver relacionamentos
                            Swal.fire({
                                title: 'Confirmar Exclusão',
                                html: `Tem certeza que deseja excluir o plano <strong>${planoName}</strong>?`,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Sim, excluir!',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#deletePlanoId').val(planoId);
                                    $('#planoNameToDelete').text(planoName);
                                    $('#deletePlanoModal').modal('show');
                                }
                            });
                        }
                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao verificar relacionamentos', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Erro', 'Falha na comunicação com o servidor', 'error');
                }
            });
        });

        // Processar exclusão após confirmação
        $('#formDeletePlano').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Excluindo...');

            $.ajax({
                type: "POST",
                url: '<?= site_url('planos/excluir') ?>',
                data: form.serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#deletePlanoModal').modal('hide');

                        // Mostrar mensagem de sucesso com detalhes
                        var msg = response.message;
                        if (response.contagem) {
                            msg += `<br><small>Itens excluídos:
                                ${response.contagem.projetos} projetos,
                                ${response.contagem.etapas} etapas,
                                ${response.contagem.acoes} ações</small>`;
                        }

                        Swal.fire({
                            title: 'Sucesso!',
                            html: msg,
                            icon: 'success',
                            timer: 3000,
                            timerProgressBar: true,
                            willClose: () => {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Erro', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Erro', 'Falha na comunicação com o servidor', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        // Cadastrar novo plano
        $('#formAddPlano').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#addPlanoModal');
        });

        // Atualizar plano
        $('#formEditPlano').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#editPlanoModal');
        });


        // Aplicar filtros
        $('#formFiltros').submit(function(e) {
            e.preventDefault();
            applyFilters();
        });

        // Limpar filtros
        $('#btnLimparFiltros').click(function() {
            $('#formFiltros')[0].reset();
            applyFilters();
        });

        // Função genérica para enviar formulários
        function submitForm(form, modalId, successMessage = null) {
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: form.serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        if (modalId) {
                            $(modalId).modal('hide');
                        }
                        showSuccessAlert(successMessage || response.message || 'Operação realizada com sucesso!');

                        if (!modalId || (modalId !== '#solicitarEdicaoModal' && modalId !== '#solicitarExclusaoModal' && modalId !== '#solicitarInclusaoModal')) {
                            setTimeout(() => location.reload(), 1500);
                        }
                    } else {
                        showErrorAlert(response.message || 'Ocorreu um erro durante a operação.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    showErrorAlert('Erro na comunicação com o servidor: ' + error);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        }

        // Aplicar filtros na tabela
        function applyFilters() {
            const hasFilters = $('#formFiltros').find('input, select').toArray().some(el => $(el).val() !== '' && $(el).val() !== null);

            if (!hasFilters) {
                location.reload();
                return;
            }

            $.ajax({
                type: "POST",
                url: '<?= site_url('planos/filtrar') ?>',
                data: $('#formFiltros').serialize(),
                dataType: "json",
                beforeSend: function() {
                    $('#dataTable').css('opacity', '0.5');
                },
                success: function(response) {
                    if (response.success) {
                        dataTable.destroy();
                        $('#dataTable tbody').empty();

                        $.each(response.data, function(index, plano) {
                            var id = plano.id + '-' + plano.nome.toLowerCase().replace(/\s+/g, '-');

                            var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
                            var actionButtons = '';

                            if (isAdmin) {
                                actionButtons = `
                                <div class="d-inline-flex">
                                    <a href="<?= site_url('planos/') ?>${plano.id}/projetos" class="btn btn-info btn-sm mx-1" title="Visualizar Projetos">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-primary btn-sm mx-1" data-id="${id}" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm mx-1" data-id="${id}" title="Excluir">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>`;
                            } else {
                                actionButtons = `
                                <div class="d-inline-flex">
                                    <a href="<?= site_url('planos/') ?>${plano.id}/projetos" class="btn btn-info btn-sm mx-1" title="Visualizar Projetos">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-primary btn-sm mx-1" data-id="${id}" title="Solicitar Edição">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm mx-1" data-id="${id}" title="Solicitar Exclusão">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>`;
                            }

                            var row = `
                            <tr>
                                <td class="text-wrap align-middle">${plano.nome}</td>
                                <td class="text-center align-middle">${plano.sigla}</td>
                                <td class="text-wrap align-middle">${plano.descricao || ''}</td>
                                <td class="text-center align-middle">${actionButtons}</td>
                            </tr>`;

                            $('#dataTable tbody').append(row);
                        });

                        dataTable = $('#dataTable').DataTable({
                            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                            "language": {
                                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
                            },
                            "searching": false,
                            "responsive": true,
                            "autoWidth": false,
                            "lengthMenu": [5, 10, 25, 50, 100],
                            "pageLength": 10
                        });
                    } else {
                        showErrorAlert('Erro ao filtrar planos: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert('Erro na requisição: ' + error);
                },
                complete: function() {
                    $('#dataTable').css('opacity', '1');
                }
            });
        }

        // Funções para exibir alertas
        function showSuccessAlert(message) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function showErrorAlert(message) {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: message,
                confirmButtonText: 'Entendi'
            });
        }

        // Abrir modal de edição quando clicar no botão de edição
        $(document).on('click', '.btn-primary[title="Editar"], .btn-primary[title="Solicitar Edição"]', function() {
            var btn = $(this);
            var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
            var planoId = btn.data('id').split('-')[0];

            if (isAdmin) {
                // Carregar dados do plano para edição
                $.ajax({
                    url: '<?= site_url('planos/editar/') ?>' + planoId,
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-edit"></i>');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Preencher o modal de edição com os dados do plano
                            $('#editPlanoId').val(response.data.id);
                            $('#editPlanoName').val(response.data.nome);
                            $('#editPlanoSigla').val(response.data.sigla);
                            $('#editPlanoDescription').val(response.data.descricao);

                            // Configurar o formulário de edição
                            $('#formEditPlano').attr('action', '<?= site_url('planos/atualizar') ?>');

                            // Mostrar o modal
                            $('#editPlanoModal').modal('show');
                        } else {
                            Swal.fire('Erro', response.message || 'Erro ao carregar dados do plano', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Erro', 'Falha na comunicação com o servidor', 'error');
                    }
                });
            } else {

            }
        });

    });
</script>