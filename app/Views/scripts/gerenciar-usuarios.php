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

        // Abrir modal de alteração de grupo - VERSÃO CORRIGIDA
        $(document).on('click', '.btn-warning[title="Alterar Grupo"]', function() {
            const userId = $(this).data('id');
            const username = $(this).data('username');

            $('#alterarGrupoUserId').val(userId);
            $('#alterarGrupoUsername').text(username);

            // Carregar grupos disponíveis
            $.get('<?= site_url("gerenciar-usuarios/editar") ?>/' + userId, function(response) {
                if (response.success) {
                    const $groupSelect = $('#alterarGrupoSelect');
                    $groupSelect.empty();

                    // Adicionar opções de grupos
                    <?php foreach ($groups as $group): ?>
                        $groupSelect.append($('<option>', {
                            value: '<?= $group ?>',
                            text: '<?= ucfirst($group) ?>'
                        }));
                    <?php endforeach; ?>

                    // Selecionar o grupo atual (se houver)
                    if (response.data.groups && response.data.groups.length > 0) {
                        $groupSelect.val(response.data.groups[0]);
                    }

                    $('#alterarGrupoModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: response.message
                    });
                }
            });
        });

        // Submeter alteração de grupo - VERSÃO CORRIGIDA
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