<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicialização da DataTable
        $('#dataTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
            },
            columnDefs: [{
                orderable: false,
                targets: [5]
            }]
        });

        // Verificar status do registro ao carregar a página
        $.get('<?= site_url("gerenciar-usuarios/status-registro") ?>', function(response) {
            if (response.success) {
                const btn = $('#toggleRegistroBtn');
                if (response.status) {
                    btn.removeClass('btn-secondary').addClass('btn-success');
                    btn.html('<i class="fas fa-user-plus"></i> Registro: Ativo');
                } else {
                    btn.removeClass('btn-success').addClass('btn-secondary');
                    btn.html('<i class="fas fa-user-plus"></i> Registro: Inativo');
                }
            }
        });

        // Alternar status do registro
        $('#toggleRegistroBtn').on('click', function() {
            Swal.fire({
                title: 'Alterar status do registro',
                text: 'Deseja realmente alterar o status do registro de usuários?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= site_url("gerenciar-usuarios/toggle-registro") ?>', function(response) {
                        if (response.success) {
                            const btn = $('#toggleRegistroBtn');
                            if (response.novoStatus) {
                                btn.removeClass('btn-secondary').addClass('btn-success');
                                btn.html('<i class="fas fa-user-plus"></i> Registro: Ativo');
                            } else {
                                btn.removeClass('btn-success').addClass('btn-secondary');
                                btn.html('<i class="fas fa-user-plus"></i> Registro: Inativo');
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    }).fail(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Falha ao alterar status do registro'
                        });
                    });
                }
            });
        });

        // Filtros
        $('#formFiltros').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.ajax({
                url: '<?= site_url("gerenciar-usuarios/filtrar") ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = '<?= site_url("gerenciar-usuarios") ?>?' + formData;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Falha ao aplicar filtros'
                    });
                }
            });
        });

        // Limpar filtros
        $('#btnLimparFiltros').on('click', function() {
            $('#formFiltros')[0].reset();
            window.location.href = '<?= site_url("gerenciar-usuarios") ?>';
        });

        // Abrir modal de edição
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            const userId = $(this).data('id');

            $.get('<?= site_url("gerenciar-usuarios/editar") ?>/' + userId, function(response) {
                if (response.success) {
                    $('#editUserId').val(response.data.id);
                    $('#editUsername').val(response.data.username);
                    $('#editEmail').val(response.data.email);

                    $('#editUsuarioModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message
                    });
                }
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Falha ao carregar dados do usuário'
                });
            });
        });

        // Submeter edição de usuário
        $('#formEditarUsuario').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: '<?= site_url("gerenciar-usuarios/atualizar") ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#editUsuarioModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            html: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Erro ao atualizar usuário';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: errorMessage
                    });
                }
            });
        });

        // Abrir modal de alteração de grupo
        $(document).on('click', '.btn-warning[title="Alterar Grupo"]', function() {
            const userId = $(this).data('id');
            const username = $(this).data('username');
            const isSuperadmin = $(this).data('superadmin') === 'true';
            const isAdmin = $(this).data('admin') === 'true';

            $('#alterarGrupoUserId').val(userId);
            $('#alterarGrupoUsername').text(username);

            // Determinar grupos permitidos baseado no usuário logado
            let allowedGroups = ['user']; // Padrão

            <?php if (auth()->user()->inGroup('superadmin')): ?>
                allowedGroups = ['superadmin', 'admin', 'user'];
            <?php elseif (auth()->user()->inGroup('admin')): ?>
                allowedGroups = ['admin', 'user'];
            <?php endif; ?>

            // Carregar grupos disponíveis
            const $groupSelect = $('#alterarGrupoSelect');
            $groupSelect.empty();

            // Adicionar opções permitidas
            allowedGroups.forEach(group => {
                $groupSelect.append($('<option>', {
                    value: group,
                    text: group.charAt(0).toUpperCase() + group.slice(1)
                }));
            });

            // Selecionar o grupo atual
            $.get('<?= site_url("gerenciar-usuarios/editar") ?>/' + userId, function(response) {
                if (response.success && response.data.groups && response.data.groups.length > 0) {
                    $groupSelect.val(response.data.groups[0]);
                }
            });

            $('#alterarGrupoModal').modal('show');
        });

        // Submeter alteração de grupo
        $('#formAlterarGrupo').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?= site_url("gerenciar-usuarios/alterar-grupo") ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#alterarGrupoModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Falha ao alterar grupo do usuário';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: errorMessage
                    });
                }
            });
        });

        // Abrir modal de confirmação de exclusão
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            const userId = $(this).data('id');
            const username = $(this).data('username');

            $('#deleteUserId').val(userId);
            $('#deleteUsername').text(username);
            $('#confirmDeleteModal').modal('show');
        });

        // Submeter exclusão de usuário
        $('#formConfirmarExclusao').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?= site_url("gerenciar-usuarios/excluir") ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#confirmDeleteModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Falha ao excluir usuário';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: errorMessage
                    });
                }
            });
        });
    });
</script>