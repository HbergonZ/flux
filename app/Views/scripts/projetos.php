<!-- Scripts da página -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        console.log('Documento pronto, inicializando DataTable');

        // Configuração do AJAX
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        });

        // Inicializa o DataTable
        var dataTable = $('#dataTable').DataTable({
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.1/i18n/pt-BR.json",
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
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
            "processing": true,
            "serverSide": false,
            "ajax": {
                "url": '<?= site_url("projetos/filtrar/$idPlano") ?>',
                "type": "POST",
                "data": function(d) {
                    // Adiciona os parâmetros do filtro
                    d.nome = $('#filterProjeto').val();
                    d.projeto_vinculado = $('#filterProjetoVinculado').val();
                    d.id_eixo = $('#filterEixo').val();
                },
                "error": function(xhr, error, thrown) {
                    console.error('Erro ao carregar dados:', xhr, error, thrown);
                    showErrorAlert('Erro ao carregar dados da tabela');
                }
            },
            "columns": [{
                    "data": "identificador",
                    "className": "text-wrap align-middle"
                },
                {
                    "data": "nome",
                    "className": "text-wrap align-middle"
                },
                {
                    "data": "descricao",
                    "className": "text-wrap align-middle"
                },
                {
                    "data": "projeto_vinculado",
                    "className": "text-wrap align-middle"
                },
                {
                    "data": "responsaveis",
                    "className": "text-wrap align-middle"
                },
                {
                    "data": null,
                    "className": "text-center align-middle",
                    "render": function(data, type, row) {
                        var id = row.id + '-' + row.nome.toLowerCase().replace(/\s+/g, '-');
                        var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;

                        if (isAdmin) {
                            return `
                                <div class="d-inline-flex">
                                    <a href="<?= site_url('planos/') ?><?= $plano['id'] ?>/projetos/${row.id}/etapas" class="btn btn-secondary btn-sm mx-1" title="Visualizar Etapas">
                                        <i class="fas fa-tasks"></i>
                                    </a>
                                    <a href="<?= site_url('planos/') ?><?= $plano['id'] ?>/projetos/${row.id}/acoes" class="btn btn-info btn-sm mx-1" title="Acessar Ações">
                                        <i class="fas fa-th-list"></i>
                                    </a>
                                    <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Excluir">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>`;
                        } else {
                            return `
                                <div class="d-inline-flex">
                                    <a href="<?= site_url('planos/') ?><?= $plano['id'] ?>/projetos/${row.id}/etapas" class="btn btn-secondary btn-sm mx-1" title="Visualizar Etapas">
                                        <i class="fas fa-tasks"></i>
                                    </a>
                                    <a href="<?= site_url('planos/') ?><?= $plano['id'] ?>/projetos/${row.id}/acoes" class="btn btn-info btn-sm mx-1" title="Acessar Ações">
                                        <i class="fas fa-th-list"></i>
                                    </a>
                                    <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Solicitar Edição">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Solicitar Exclusão">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>`;
                        }
                    },
                    "orderable": false
                }
            ],
            "createdRow": function(row, data, dataIndex) {
                // Adiciona classes às células se necessário
                $(row).find('td').addClass('align-middle');
            }
        });

        // Armazenar dados originais do formulário
        let formOriginalData = {};

        // Cadastrar novo projeto (apenas admin)
        $('#formAddProjeto').submit(function(e) {
            e.preventDefault();
            console.log('Formulário de adição de projeto submetido');
            submitForm($(this), '#addProjetoModal', 'Projeto cadastrado com sucesso!', true);
        });

        // Editar projeto - Abrir modal (apenas admin)
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            console.log('Botão Editar clicado');
            var projetoId = $(this).data('id').split('-')[0];

            $.ajax({
                url: '<?= site_url('projetos/editar/') ?>' + projetoId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta de edição recebida:', response);
                    if (response.success && response.data) {
                        $('#editProjetoId').val(response.data.id);
                        $('#editProjetoIdentificador').val(response.data.identificador);
                        $('#editProjetoNome').val(response.data.nome);
                        $('#editProjetoDescricao').val(response.data.descricao);
                        $('#editProjetoVinculado').val(response.data.projeto_vinculado);
                        $('#editProjetoEixo').val(response.data.id_eixo);
                        $('#editProjetoResponsaveis').val(response.data.responsaveis);
                        $('#projetoStatus').val(response.data.status || 'Ativo'); // Adicione esta linha
                        $('#editProjetoModal').modal('show');
                    } else {
                        console.error('Erro ao carregar projeto:', response.message);
                        showErrorAlert(response.message || "Erro ao carregar projeto");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição de edição:', error, xhr.responseText);
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Solicitar edição de projeto - Abrir modal (para não-admins)
        $(document).on('click', '.btn-primary[title="Solicitar Edição"]', function() {
            console.log('Botão Solicitar Edição clicado');
            var projetoId = $(this).data('id').split('-')[0];
            var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
            var url = isAdmin ? '<?= site_url('projetos/editar/') ?>' : '<?= site_url('projetos/dados-projeto/') ?>';

            $.ajax({
                url: url + projetoId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta de dados do projeto recebida:', response);
                    if (response.success && response.data) {
                        var projeto = response.data;

                        // Preenche os campos do formulário
                        $('#solicitarEdicaoId').val(projeto.id);
                        $('#solicitarEdicaoIdentificador').val(projeto.identificador);
                        $('#solicitarEdicaoNome').val(projeto.nome);
                        $('#solicitarEdicaoDescricao').val(projeto.descricao);
                        $('#solicitarEdicaoVinculado').val(projeto.projeto_vinculado);
                        $('#solicitarEdicaoEixo').val(projeto.id_eixo);
                        $('#solicitarEdicaoResponsaveis').val(projeto.responsaveis);

                        // Armazena os valores originais para comparação
                        formOriginalData = {
                            identificador: projeto.identificador,
                            nome: projeto.nome,
                            descricao: projeto.descricao,
                            projeto_vinculado: projeto.projeto_vinculado,
                            id_eixo: projeto.id_eixo,
                            responsaveis: projeto.responsaveis
                        };

                        $('#solicitarEdicaoModal').modal('show');
                        $('#alertNenhumaAlteracao').addClass('d-none');
                    } else {
                        console.error('Erro ao carregar projeto:', response.message);
                        showErrorAlert(response.message || "Erro ao carregar projeto");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição de dados do projeto:', error, xhr.responseText);
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

            ['identificador', 'nome', 'descricao', 'projeto_vinculado', 'id_eixo', 'responsaveis'].forEach(field => {
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
            console.log('Formulário de solicitação de edição submetido');
            submitForm($(this), '#solicitarEdicaoModal', 'Solicitação de edição enviada com sucesso!');
        });

        // Solicitar exclusão de projeto - Abrir modal (para não-admins)
        $(document).on('click', '.btn-danger[title="Solicitar Exclusão"]', function() {
            console.log('Botão Solicitar Exclusão clicado');
            var projetoId = $(this).data('id').split('-')[0];
            var projetoName = $(this).closest('tr').find('td:nth-child(2)').text();
            var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
            var url = isAdmin ? '<?= site_url('projetos/editar/') ?>' : '<?= site_url('projetos/dados-projeto/') ?>';

            $.ajax({
                url: url + projetoId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta de dados do projeto para exclusão recebida:', response);
                    if (response.success && response.data) {
                        var projeto = response.data;
                        var dadosAtuais = `Identificador: ${projeto.identificador}\nNome: ${projeto.nome}\nDescrição: ${projeto.descricao}\nProjeto Vinculado: ${projeto.projeto_vinculado}\nEixo: ${projeto.id_eixo}\nResponsáveis: ${projeto.responsaveis}`;

                        $('#solicitarExclusaoId').val(projeto.id);
                        $('#projetoNameToRequestDelete').text(projetoName);
                        $('#solicitarExclusaoDadosAtuais').val(dadosAtuais);
                        $('#solicitarExclusaoModal').modal('show');
                    } else {
                        console.error('Erro ao carregar projeto para exclusão:', response.message);
                        showErrorAlert(response.message || "Erro ao carregar projeto");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição de dados do projeto para exclusão:', error, xhr.responseText);
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Excluir projeto - Abrir modal de confirmação (apenas admin)
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            console.log('Botão Excluir clicado');
            var projetoId = $(this).data('id').split('-')[0];
            var projetoName = $(this).closest('tr').find('td:nth-child(2)').text();

            $('#deleteProjetoId').val(projetoId);
            $('#projetoNameToDelete').text(projetoName);

            // Atualiza o action do formulário corretamente
            $('#formDeleteProjeto').attr('action', '<?= site_url("projetos/excluir/$idPlano") ?>');

            $('#deleteProjetoModal').modal('show');
        });

        // Enviar solicitação de exclusão
        $('#formSolicitarExclusao').submit(function(e) {
            e.preventDefault();
            console.log('Formulário de solicitação de exclusão submetido');
            submitForm($(this), '#solicitarExclusaoModal', 'Solicitação de exclusão enviada com sucesso!');
        });

        // Enviar solicitação de inclusão
        $('#formSolicitarInclusao').submit(function(e) {
            e.preventDefault();
            console.log('Formulário de solicitação de inclusão submetido');
            submitForm($(this), '#solicitarInclusaoModal', 'Solicitação de inclusão enviada com sucesso!');
        });

        // Atualizar projeto (apenas admin)
        $('#formEditProjeto').submit(function(e) {
            e.preventDefault();
            console.log('Formulário de edição de projeto submetido');
            submitForm($(this), '#editProjetoModal', 'Projeto atualizado com sucesso!', true);
        });

        // Confirmar exclusão (apenas admin)
        $('#formDeleteProjeto').submit(function(e) {
            e.preventDefault();
            console.log('Formulário de exclusão de projeto submetido');

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: form.serialize(),
                dataType: "json",
                success: function(response) {
                    console.log('Resposta de exclusão recebida:', response);
                    if (response.success) {
                        $('#deleteProjetoModal').modal('hide');
                        showSuccessAlert(response.message || 'Projeto excluído com sucesso!');
                        dataTable.ajax.reload();
                    } else {
                        console.error('Erro ao excluir projeto:', response.message);
                        showErrorAlert(response.message || 'Ocorreu um erro durante a exclusão.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição de exclusão:', error, xhr.responseText);
                    showErrorAlert('Erro na comunicação com o servidor: ' + error);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        // Aplicar filtros
        $('#formFiltros').submit(function(e) {
            e.preventDefault();
            console.log('Formulário de filtros submetido');
            dataTable.ajax.reload();
        });

        // Limpar filtros
        $('#btnLimparFiltros').click(function() {
            console.log('Botão Limpar Filtros clicado');
            $('#formFiltros')[0].reset();
            dataTable.ajax.reload();
        });

        // Função genérica para enviar formulários
        function submitForm(form, modalId, successMessage = null, reloadTable = false) {
            console.log('Executando submitForm para:', form.attr('id'));
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: form.serialize(),
                dataType: "json",
                success: function(response) {
                    console.log('Resposta recebida:', response);
                    if (response.success) {
                        if (modalId) {
                            $(modalId).modal('hide');
                            form[0].reset();
                        }
                        showSuccessAlert(successMessage || response.message || 'Operação realizada com sucesso!');

                        if (reloadTable) {
                            console.log('Recarregando tabela...');
                            dataTable.ajax.reload();
                        }
                    } else {
                        console.error('Erro na operação:', response.message);
                        showErrorAlert(response.message || 'Ocorreu um erro durante a operação.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição AJAX:', error, xhr.responseText);
                    showErrorAlert('Erro na comunicação com o servidor: ' + error);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        }

        // Funções para exibir alertas
        function showSuccessAlert(message) {
            console.log('Exibindo alerta de sucesso:', message);
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        }

        function showErrorAlert(message) {
            console.error('Exibindo alerta de erro:', message);
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: message,
                confirmButtonText: 'Entendi'
            });
        }
    });
</script>