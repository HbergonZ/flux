<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        var dataTable = $('#dataTable').DataTable({
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

        // AJAX setup
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Apply filters
        $('#formFiltros').submit(function(e) {
            e.preventDefault();
            applyFilters();
        });

        // Clear filters
        $('#btnLimparFiltros').click(function() {
            $('#formFiltros')[0].reset();
            applyFilters();
        });

        // Open change group modal
        $(document).on('click', '.btn-warning:not(:disabled)', function() {
            var userId = $(this).data('id');
            var username = $(this).data('username');

            if (!userId || !username) {
                showErrorAlert("Dados do usuário não encontrados");
                return;
            }

            $('#alterarGrupoUserId').val(userId);
            $('#alterarGrupoUsername').text(username);
            $('#alterarGrupoModal').modal('show');
        });

        // Open edit modal - Fixed version
        $(document).on('click', '.btn-primary:not(:disabled)', function() {
            var userId = $(this).data('id');

            if (!userId) {
                showErrorAlert("ID do usuário não encontrado");
                return;
            }

            $.ajax({
                url: 'gerenciar-usuarios/editar/' + userId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        $('#editUserId').val(response.data.id);
                        $('#editUsername').val(response.data.username);
                        $('#editEmail').val(response.data.email);

                        // Show/hide group field based on self-edit
                        if (response.data.is_self_edit) {
                            $('#editGroupContainer').hide();
                        } else {
                            $('#editGroupContainer').show();
                            if (response.data.groups && response.data.groups.length > 0) {
                                $('#editGroup').val(response.data.groups[0]);
                            }
                        }

                        $('#editUsuarioModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar usuário");
                    }
                },
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON?.message || 'Erro na comunicação com o servidor';
                    showErrorAlert(errorMsg);
                }
            });
        });

        // Open delete modal
        $(document).on('click', '.btn-danger:not(:disabled)', function() {
            var userId = $(this).data('id');
            var username = $(this).data('username');

            if (!userId || !username) {
                showErrorAlert("Dados do usuário não encontrados");
                return;
            }

            $('#deleteUserId').val(userId);
            $('#usuarioNameToDelete').text(username);
            $('#deleteUsuarioModal').modal('show');
        });

        // Submit edit form - Fixed version
        $('#formEditUsuario').submit(function(e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            var isSelfEdit = ($('#editUserId').val() == <?= auth()->user()->id ?>);

            // Remove group field if self-edit
            if (isSelfEdit) {
                form.find('[name="group"]').remove();
            }

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');

            $.ajax({
                url: 'gerenciar-usuarios/atualizar',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#editUsuarioModal').modal('hide');
                        showSuccessAlert(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showErrorAlert(response.message);
                    }
                },
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON?.message || 'Erro na comunicação com o servidor';
                    showErrorAlert(errorMsg);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Submit change group form
        $('#formAlterarGrupo').submit(function(e) {
            e.preventDefault();

            var grupoSelecionado = $('#alterarGrupoSelect').val();
            if (!grupoSelecionado) {
                showErrorAlert("Selecione um grupo válido");
                return;
            }

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');

            $.ajax({
                url: 'gerenciar-usuarios/alterar-grupo',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#alterarGrupoModal').modal('hide');
                        showSuccessAlert(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showErrorAlert(response.message || "Erro ao alterar grupo");
                    }
                },
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON?.message || 'Erro na comunicação com o servidor';
                    showErrorAlert(errorMsg);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Submit delete form
        $('#formDeleteUsuario').submit(function(e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Excluindo...');

            $.ajax({
                url: 'gerenciar-usuarios/excluir',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#deleteUsuarioModal').modal('hide');
                        showSuccessAlert(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showErrorAlert(response.message || "Erro ao excluir usuário");
                    }
                },
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON?.message || 'Erro na comunicação com o servidor';
                    showErrorAlert(errorMsg);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Apply filters function
        function applyFilters() {
            const hasFilters = $('#formFiltros').find('input, select').toArray().some(el => $(el).val() !== '' && $(el).val() !== null);

            if (!hasFilters) {
                location.reload();
                return;
            }

            $.ajax({
                type: "POST",
                url: 'gerenciar-usuarios/filtrar',
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
                            var canEdit = (loggedUserIsAdmin && (!userIsAdmin || isCurrentUser)) || (!loggedUserIsAdmin && isCurrentUser);
                            var canDelete = loggedUserIsAdmin && !isCurrentUser && !userIsAdmin;

                            var row = `
                        <tr>
                            <td class="text-center align-middle">${user.id}</td>
                            <td class="align-middle">${user.username}</td>
                            <td class="align-middle">${user.email}</td>
                            <td class="text-center align-middle">${user.groups.map(g => g.charAt(0).toUpperCase() + g.slice(1)).join(', ')}</td>
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
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON?.message || 'Erro na requisição';
                    showErrorAlert(errorMsg);
                },
                complete: function() {
                    $('#dataTable').css('opacity', '1');
                }
            });
        }

        // Show success alert
        function showSuccessAlert(message) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        // Show error alert
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