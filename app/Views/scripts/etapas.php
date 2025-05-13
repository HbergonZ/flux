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

        // Cadastrar nova etapa (apenas admin)
        $('#formAddEtapa').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#addEtapaModal');
        });

        // Editar etapa - Abrir modal (apenas admin)
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            var etapaId = $(this).data('id').split('-')[0];

            $.ajax({
                url: '<?= site_url('etapas/editar/') ?>' + etapaId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        $('#editEtapaId').val(response.data.id);
                        $('#editEtapaNome').val(response.data.nome);
                        $('#editEtapaOrdem').val(response.data.ordem);
                        $('#editEtapaModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar etapa");
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Solicitar edição de etapa - Abrir modal (para não-admins)
        $(document).on('click', '.btn-primary[title="Solicitar Edição"]', function() {
            var etapaId = $(this).data('id').split('-')[0];
            var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
            var url = isAdmin ? '<?= site_url('etapas/editar/') ?>' : '<?= site_url('etapas/dados-etapa/') ?>';

            $.ajax({
                url: url + etapaId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var etapa = response.data;

                        // Preenche os campos do formulário
                        $('#solicitarEdicaoId').val(etapa.id);
                        $('#solicitarEdicaoNome').val(etapa.nome);
                        $('#solicitarEdicaoOrdem').val(etapa.ordem);

                        // Armazena os valores originais para comparação
                        formOriginalData = {
                            nome: etapa.nome,
                            ordem: etapa.ordem
                        };

                        $('#solicitarEdicaoModal').modal('show');
                        $('#alertNenhumaAlteracao').addClass('d-none');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar etapa");
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

            ['nome', 'ordem'].forEach(field => {
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

        // Solicitar exclusão de etapa - Abrir modal (para não-admins)
        $(document).on('click', '.btn-danger[title="Solicitar Exclusão"]', function() {
            var etapaId = $(this).data('id').split('-')[0];
            var etapaName = $(this).closest('tr').find('td:nth-child(2)').text();
            var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
            var url = isAdmin ? '<?= site_url('etapas/editar/') ?>' : '<?= site_url('etapas/dados-etapa/') ?>';

            $.ajax({
                url: url + etapaId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var etapa = response.data;
                        var dadosAtuais = `Nome: ${etapa.nome}\nOrdem: ${etapa.ordem}\nProjeto: ${etapa.id_projeto}`;

                        $('#solicitarExclusaoId').val(etapa.id);
                        $('#etapaNameToRequestDelete').text(etapaName);
                        $('#solicitarExclusaoDadosAtuais').val(dadosAtuais);
                        $('#solicitarExclusaoModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar etapa");
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

        // Atualizar etapa (apenas admin)
        $('#formEditEtapa').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#editEtapaModal');
        });

        // Excluir etapa - Abrir modal de confirmação (apenas admin)
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var etapaId = $(this).data('id').split('-')[0];
            var etapaName = $(this).closest('tr').find('td:nth-child(2)').text();

            $('#deleteEtapaId').val(etapaId);
            $('#etapaNameToDelete').text(etapaName);
            $('#deleteEtapaModal').modal('show');
        });

        // Confirmar exclusão (apenas admin)
        $('#formDeleteEtapa').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#deleteEtapaModal');
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
                url: '<?= site_url("etapas/filtrar/$idProjeto") ?>',
                data: $('#formFiltros').serialize(),
                dataType: "json",
                beforeSend: function() {
                    $('#dataTable').css('opacity', '0.5');
                },
                success: function(response) {
                    if (response.success) {
                        dataTable.destroy();
                        $('#dataTable tbody').empty();

                        $.each(response.data, function(index, etapa) {
                            var id = etapa.id + '-' + etapa.nome.toLowerCase().replace(/\s+/g, '-');
                            var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
                            var actionButtons = '';

                            if (isAdmin) {
                                actionButtons = `
                                    <div class="d-inline-flex">
                                        <a href="<?= site_url('etapas/') ?>${etapa.id}/acoes" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar Ações">
                                            <i class="fas fa-tasks"></i>
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
                                        <a href="<?= site_url('etapas/') ?>${etapa.id}/acoes" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar Ações">
                                            <i class="fas fa-tasks"></i>
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
                                    <td class="text-center">${etapa.ordem || '-'}</td>
                                    <td class="text-wrap">${etapa.nome}</td>
                                    <td class="text-center">${formatDate(etapa.data_criacao)}</td>
                                    <td class="text-center">${formatDate(etapa.data_atualizacao)}</td>
                                    <td class="text-center">${actionButtons}</td>
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
                            "pageLength": 10,
                            "order": [
                                [0, 'asc']
                            ]
                        });
                    } else {
                        showErrorAlert('Erro ao filtrar etapas: ' + response.message);
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

        // Função para formatar data
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR');
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