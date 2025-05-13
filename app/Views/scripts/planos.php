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

        // Armazenar dados originais do formulário
        let formOriginalData = {};

        // Cadastrar novo plano
        $('#formAddPlano').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#addPlanoModal');
        });

        // Editar plano - Abrir modal (apenas admin)
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            var planoId = $(this).data('id').split('-')[0];

            $.ajax({
                url: '<?= site_url('planos/editar/') ?>' + planoId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        $('#editPlanoId').val(response.data.id);
                        $('#editPlanoName').val(response.data.nome);
                        $('#editPlanoSigla').val(response.data.sigla);
                        $('#editPlanoDescription').val(response.data.descricao);
                        $('#editPlanoModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar plano");
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Solicitar edição de plano - Abrir modal (para não-admins)
        $(document).on('click', '.btn-primary[title="Solicitar Edição"]', function() {
            var planoId = $(this).data('id').split('-')[0];
            var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
            var url = isAdmin ? '<?= site_url('planos/editar/') ?>' : '<?= site_url('planos/dados-plano/') ?>';

            $.ajax({
                url: url + planoId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var plano = response.data;

                        // Preenche os campos do formulário
                        $('#solicitarEdicaoId').val(plano.id);
                        $('#solicitarEdicaoNome').val(plano.nome);
                        $('#solicitarEdicaoSigla').val(plano.sigla);
                        $('#solicitarEdicaoDescricao').val(plano.descricao);

                        // Armazena os valores originais para comparação
                        formOriginalData = {
                            nome: plano.nome,
                            sigla: plano.sigla,
                            descricao: plano.descricao
                        };

                        $('#solicitarEdicaoModal').modal('show');
                        $('#alertNenhumaAlteracao').addClass('d-none');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar plano");
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Verificar alterações em tempo real no modal de edição
        $('#solicitarEdicaoModal').on('shown.bs.modal', function() {
            $('#formSolicitarEdicao').on('input change', function() {
                checkForChanges();
            });
        });

        function checkForChanges() {
            let hasChanges = false;
            const form = $('#formSolicitarEdicao');

            ['nome', 'sigla', 'descricao'].forEach(field => {
                const currentValue = form.find(`[name="${field}"]`).val();
                if (formOriginalData[field] != currentValue) {
                    hasChanges = true;
                }
            });

            if (hasChanges) {
                $('#alertNenhumaAlteracao').addClass('d-none');
                $('#formSolicitarEdicao button[type="submit"]').prop('disabled', false);
            } else {
                $('#alertNenhumaAlteracao').removeClass('d-none');
                $('#formSolicitarEdicao button[type="submit"]').prop('disabled', true);
            }
        }

        // Enviar solicitação de edição
        $('#formSolicitarEdicao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarEdicaoModal', 'Solicitação de edição enviada com sucesso!');
        });

        // Solicitar exclusão de plano - Abrir modal (para não-admins)
        $(document).on('click', '.btn-danger[title="Solicitar Exclusão"]', function() {
            var planoId = $(this).data('id').split('-')[0];
            var planoName = $(this).closest('tr').find('td:first').text();
            var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
            var url = isAdmin ? '<?= site_url('planos/editar/') ?>' : '<?= site_url('planos/dados-plano/') ?>';

            $.ajax({
                url: url + planoId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var plano = response.data;
                        var dadosAtuais = `Nome: ${plano.nome}\nSigla: ${plano.sigla}\nDescrição: ${plano.descricao}`;

                        $('#solicitarExclusaoId').val(plano.id);
                        $('#planoNameToRequestDelete').text(planoName);
                        $('#solicitarExclusaoDadosAtuais').val(dadosAtuais);
                        $('#solicitarExclusaoModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar plano");
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Enviar solicitação de exclusão
        $('#formSolicitarExclusao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarExclusaoModal', 'Solicitação de exclusão enviada com sucesso!');
        });

        // Enviar solicitação de inclusão
        $('#formSolicitarInclusao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarInclusaoModal', 'Solicitação de inclusão enviada com sucesso!');
        });

        // Atualizar plano (apenas admin)
        $('#formEditPlano').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#editPlanoModal');
        });

        // Excluir plano - Abrir modal de confirmação (apenas admin)
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var planoId = $(this).data('id').split('-')[0];
            var planoName = $(this).closest('tr').find('td:first').text();

            $('#deletePlanoId').val(planoId);
            $('#planoNameToDelete').text(planoName);
            $('#deletePlanoModal').modal('show');
        });

        // Confirmar exclusão (apenas admin)
        $('#formDeletePlano').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#deletePlanoModal');
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

                        // Recarregar a página apenas se for uma operação que altera dados
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
                                        <a href="<?= site_url('plojetos/') ?>${plano.id}" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar Ações">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>`;
                            } else {
                                actionButtons = `
                                    <div class="d-inline-flex">
                                        <a href="<?= site_url('projetos/') ?>${plano.id}" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar Ações">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Solicitar Edição">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Solicitar Exclusão">
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
    });
</script>