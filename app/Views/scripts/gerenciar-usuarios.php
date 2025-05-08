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
                }
            },
            "searching": false,
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": 10,
            "columnDefs": [{
                "orderable": false,
                "targets": [5]
            }]
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
            applyFilters();
        });

        // Limpar filtros
        $('#btnLimparFiltros').click(function() {
            $('#formFiltros')[0].reset();
            applyFilters();
        });

        // Abrir modal de alterar grupo
        $(document).on('click', '.btn-warning:not(:disabled)', function() {
            var userId = $(this).data('id');
            var username = $(this).data('username');

            $('#alterarGrupoUserId').val(userId);
            $('#alterarGrupoUsername').text(username);
            $('#alterarGrupoModal').modal('show');
        });

        // Abrir modal de edição
        $(document).on('click', '.btn-primary:not(:disabled)', function() {
            var userId = $(this).data('id');

            $.ajax({
                url: '<?= site_url('usuarios/editar/') ?>' + userId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        $('#editUserId').val(response.data.id);
                        $('#editUsername').val(response.data.username);
                        $('#editEmail').val(response.data.email);
                        $('#editUsuarioModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar usuário");
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Abrir modal de exclusão
        $(document).on('click', '.btn-danger:not(:disabled)', function() {
            var userId = $(this).data('id');
            var username = $(this).data('username');

            $('#deleteUserId').val(userId);
            $('#usuarioNameToDelete').text(username);
            $('#deleteUsuarioModal').modal('show');
        });

        // Enviar formulário de edição
        $('#formEditUsuario').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#editUsuarioModal');
        });

        $('#formAlterarGrupo').submit(function(e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#alterarGrupoModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message || 'Erro ao alterar grupo'
                        });
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Erro na comunicação com o servidor';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        errorMsg = 'Token de segurança inválido. Recarregue a página e tente novamente.';
                    } else if (xhr.status === 405) {
                        errorMsg = 'Método não permitido';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: errorMsg
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Enviar formulário de exclusão
        $('#formDeleteUsuario').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#deleteUsuarioModal');
        });

        // Função para aplicar filtros
        function applyFilters() {
            const hasFilters = $('#formFiltros').find('input, select').toArray().some(el => $(el).val() !== '' && $(el).val() !== null);

            if (!hasFilters) {
                location.reload();
                return;
            }

            $.ajax({
                type: "POST",
                url: '<?= site_url('usuarios/filtrar') ?>',
                data: $('#formFiltros').serialize(),
                dataType: "json",
                beforeSend: function() {
                    $('#dataTable').css('opacity', '0.5');
                },
                success: function(response) {
                    if (response.success) {
                        dataTable.destroy();
                        $('#dataTable tbody').empty();

                        $.each(response.data, function(index, user) {
                            var isCurrentUser = <?= auth()->user()->id ?> == user.id;
                            var userIsAdmin = user.groups.includes('admin');
                            var loggedUserIsAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;

                            var canChangeGroup = loggedUserIsAdmin && !isCurrentUser && !userIsAdmin;
                            var canEdit = loggedUserIsAdmin || isCurrentUser;
                            var canDelete = loggedUserIsAdmin && !isCurrentUser && !userIsAdmin;

                            var row = `
                            <tr>
                                <td class="text-center align-middle">${user.id}</td>
                                <td class="align-middle">${user.username}</td>
                                <td class="align-middle">${user.email}</td>
                                <td class="text-center align-middle">${user.groups.map(g => g === 'admin' ? 'Administrador' : 'Usuário').join(', ')}</td>
                                <td class="text-center align-middle">
                                    <span class="badge ${user.active ? 'badge-success' : 'badge-secondary'}">
                                        ${user.active ? 'Ativo' : 'Inativo'}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-inline-flex">
                                        <button type="button" class="btn btn-sm mx-1 ${canChangeGroup ? 'btn-warning' : 'btn-secondary'}"
                                            style="width: 32px; height: 32px;"
                                            title="Alterar Grupo" data-id="${user.id}" data-username="${user.username}" ${!canChangeGroup ? 'disabled' : ''}>
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm mx-1 ${canEdit ? 'btn-primary' : 'btn-secondary'}"
                                            style="width: 32px; height: 32px;"
                                            title="Editar" data-id="${user.id}" ${!canEdit ? 'disabled' : ''}>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm mx-1 ${canDelete ? 'btn-danger' : 'btn-secondary'}"
                                            style="width: 32px; height: 32px;"
                                            title="Excluir" data-id="${user.id}" data-username="${user.username}" ${!canDelete ? 'disabled' : ''}>
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
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
                            "lengthMenu": [10, 25, 50, 100],
                            "pageLength": 10,
                            "columnDefs": [{
                                "orderable": false,
                                "targets": [5]
                            }]
                        });
                    } else {
                        showErrorAlert('Erro ao filtrar usuários: ' + response.message);
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
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showErrorAlert(response.message || 'Ocorreu um erro durante a operação.');
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert('Erro na comunicação com o servidor: ' + error);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
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