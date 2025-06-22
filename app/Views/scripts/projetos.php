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

        // Funções auxiliares
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

        function submitForm(form, modalId, successMessage = null, reloadTable = false) {
            return new Promise((resolve, reject) => {
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
                            resolve(response);
                        } else {
                            console.error('Erro na operação:', response.message);
                            showErrorAlert(response.message || 'Ocorreu um erro durante a operação.');
                            reject(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro na requisição AJAX:', error, xhr.responseText);
                        showErrorAlert('Erro na comunicação com o servidor: ' + error);
                        reject(error);
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });
        }

        // Inicializa o DataTable
        var dataTable = $('#dataTable').DataTable({
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "language": {
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
                        var buttons = `
                            <div class="d-inline-flex">
                                <a href="<?= site_url('planos/') ?><?= $plano['id'] ?>/projetos/${row.id}/etapas" class="btn btn-secondary btn-sm mx-1" title="Visualizar Etapas">
                                    <i class="fas fa-tasks"></i>
                                </a>
                                <a href="<?= site_url('planos/') ?><?= $plano['id'] ?>/projetos/${row.id}/acoes" class="btn btn-info btn-sm mx-1" title="Acessar Ações">
                                    <i class="fas fa-th-list"></i>
                                </a>`;

                        if (isAdmin) {
                            buttons += `
                                <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>`;
                        } else {
                            buttons += `
                                <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Solicitar Edição">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Solicitar Exclusão">
                                    <i class="fas fa-trash-alt"></i>
                                </button>`;
                        }

                        buttons += `</div>`;
                        return buttons;
                    },
                    "orderable": false
                }
            ],
            "createdRow": function(row, data, dataIndex) {
                $(row).find('td').addClass('align-middle');
            }
        });

        // Armazenar dados originais do formulário
        let formOriginalData = {};

        // Eventos de formulários
        $('#formAddProjeto').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#addProjetoModal', 'Projeto cadastrado com sucesso!', true);
        });

        // No evento de submit do formulário de solicitação de edição
        $('#formSolicitarEdicao').submit(function(e) {
            e.preventDefault();
            e.stopPropagation();

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            // Mostrar indicador de carregamento
            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...'
            );

            // Coletar todos os dados do formulário
            const formData = {
                id_projeto: $('#solicitarEdicaoId').val(),
                id_plano: $('#idPlano').val(),
                identificador: $('#solicitarEdicaoIdentificador').val(),
                nome: $('#solicitarEdicaoNome').val(),
                descricao: $('#solicitarEdicaoDescricao').val(),
                projeto_vinculado: $('#solicitarEdicaoVinculado').val(),
                priorizacao_gab: $('#solicitarEdicaoPriorizacao').val(),
                id_eixo: $('#solicitarEdicaoEixo').val(),
                responsaveis: $('#solicitarEdicaoResponsaveis').val(),
                status: $('#solicitarEdicaoStatus').val(),
                justificativa: $('#solicitarEdicaoJustificativa').val(),
                evidencias_adicionar: getEvidenciasParaAdicionar(),
                evidencias_remover: getEvidenciasParaRemover()
            };

            // Verificar se há alterações
            const projetoOriginal = window.projetoOriginalData || {};
            let hasChanges = false;

            // Verificar alterações nos campos básicos
            const camposParaComparar = [
                'identificador', 'nome', 'descricao', 'projeto_vinculado',
                'priorizacao_gab', 'id_eixo', 'responsaveis', 'status'
            ];

            camposParaComparar.forEach(campo => {
                if (JSON.stringify(projetoOriginal[campo]) !== JSON.stringify(formData[campo])) {
                    hasChanges = true;
                }
            });

            // Verificar alterações nas evidências
            if (formData.evidencias_adicionar.length > 0 || formData.evidencias_remover.length > 0) {
                hasChanges = true;
            }

            if (!hasChanges) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                showErrorAlert('Nenhuma alteração detectada. Modifique pelo menos um campo para enviar a solicitação.');
                return;
            }

            // Enviar via AJAX
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    if (response.success) {
                        showSuccessAlert(response.message);
                        $('#solicitarEdicaoModal').modal('hide');
                        form[0].reset();

                        if (typeof dataTable !== 'undefined') {
                            dataTable.ajax.reload(null, false);
                        }
                    } else {
                        showErrorAlert(response.message);
                    }
                },
                error: function(xhr) {
                    submitBtn.prop('disabled', false).html(originalBtnText);

                    let errorMessage = 'Erro na comunicação com o servidor';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Failed to parse error response:', e);
                    }
                    showErrorAlert(errorMessage);
                }
            });
        });

        // Funções auxiliares para evidências
        function getEvidenciasParaAdicionar() {
            const evidencias = [];
            $('#evidenciasProjetoAtuaisList .list-group-item').each(function() {
                if (!$(this).data('id')) { // Se não tem ID, é nova
                    evidencias.push({
                        tipo: $(this).data('tipo'),
                        conteudo: $(this).data('conteudo'),
                        descricao: $(this).data('descricao') || 'Sem descrição'
                    });
                }
            });
            return evidencias;
        }

        function getEvidenciasParaRemover() {
            const ids = [];
            $('#evidenciasProjetoRemoverListSolicitacao .list-group-item').each(function() {
                const id = $(this).data('id');
                if (id) ids.push(id);
            });
            return ids;
        }

        $('#formSolicitarExclusao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarExclusaoModal', 'Solicitação de exclusão enviada com sucesso!');
        });

        $('#formSolicitarInclusao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarInclusaoModal', 'Solicitação de inclusão enviada com sucesso!');
        });

        // No evento de submit do formulário de edição
        $('#formEditProjeto').submit(function(e) {
            e.preventDefault();
            var form = $(this);

            // Coletar evidências para adicionar (novas)
            var evidenciasAdicionar = [];
            $('#evidenciasProjetoAtuaisList .list-group-item').each(function() {
                if (!$(this).data('id')) { // Se não tem ID, é nova
                    evidenciasAdicionar.push({
                        tipo: $(this).data('tipo'),
                        conteudo: $(this).data('conteudo'),
                        descricao: $(this).data('descricao')
                    });
                }
            });

            // Coletar evidências para remover (IDs)
            var evidenciasRemover = [];
            $('#evidenciasProjetoRemoverList .list-group-item').each(function() {
                var id = $(this).data('id');
                if (id) { // Só adiciona se tiver ID (evidências existentes)
                    evidenciasRemover.push(id);
                }
            });

            // Adicionar dados ao formulário como campos hidden
            form.find('input[name="evidencias_adicionar"]').remove();
            form.find('input[name="evidencias_remover"]').remove();

            form.append(
                $('<input>').attr({
                    type: 'hidden',
                    name: 'evidencias_adicionar',
                    value: JSON.stringify(evidenciasAdicionar)
                }),
                $('<input>').attr({
                    type: 'hidden',
                    name: 'evidencias_remover',
                    value: JSON.stringify(evidenciasRemover)
                })
            );

            // Enviar o formulário
            submitForm(form, '#editProjetoModal', 'Projeto atualizado com sucesso!', true);
        });

        $('#formDeleteProjeto').submit(function(e) {
            e.preventDefault();
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
                    if (response.success) {
                        $('#deleteProjetoModal').modal('hide');
                        showSuccessAlert(response.message || 'Projeto excluído com sucesso!');
                        dataTable.ajax.reload();
                    } else {
                        showErrorAlert(response.message || 'Ocorreu um erro durante a exclusão.');
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert('Erro na comunicação com o servidor: ' + error);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        // Filtros
        $('#formFiltros').submit(function(e) {
            e.preventDefault();
            dataTable.ajax.reload();
        });

        $('#btnLimparFiltros').click(function() {
            $('#formFiltros')[0].reset();
            dataTable.ajax.reload();
        });

        // Botões de ação
        $(document).on('click', '.btn-primary[title="Editar"], .btn-primary[title="Solicitar Edição"]', function() {
            var isAdmin = $(this).attr('title') === 'Editar';
            var projetoCompletoId = $(this).data('id');
            var projetoId = projetoCompletoId.split('-')[0];
            var $botao = $(this);
            var originalHtml = $botao.html();

            $botao.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

            // Determinar a URL correta baseada no tipo de usuário
            var url = isAdmin ?
                '<?= site_url("projetos/editar/") ?>' + projetoId :
                '<?= site_url("projetos/dados-projeto/") ?>' + projetoId;

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    $botao.html(originalHtml).prop('disabled', false);

                    if (response.success && response.data) {
                        if (isAdmin) {
                            // Preencher modal de edição normal para admin
                            $('#editProjetoId').val(response.data.id);
                            $('#editProjetoIdentificador').val(response.data.identificador);
                            $('#editProjetoNome').val(response.data.nome);
                            $('#editProjetoDescricao').val(response.data.descricao);
                            $('#editProjetoVinculado').val(response.data.projeto_vinculado);
                            $('#editProjetoEixo').val(response.data.id_eixo);
                            $('#editProjetoPriorizacao').val(response.data.priorizacao_gab);
                            $('#projetoStatus').val(response.data.status);
                            $('#editProjetoResponsaveis').val(response.data.responsaveis);

                            // Carregar evidências
                            carregarEvidenciasProjeto(projetoId);

                            $('#editProjetoModal').modal('show');
                        } else {
                            // Preencher modal de solicitação de edição para não-admin
                            $('#solicitarEdicaoId').val(response.data.id);
                            $('#solicitarEdicaoIdentificador').val(response.data.identificador);
                            $('#solicitarEdicaoNome').val(response.data.nome);
                            $('#solicitarEdicaoDescricao').val(response.data.descricao);
                            $('#solicitarEdicaoVinculado').val(response.data.projeto_vinculado);
                            $('#solicitarEdicaoEixo').val(response.data.id_eixo);
                            $('#solicitarEdicaoPriorizacao').val(response.data.priorizacao_gab);
                            $('#solicitarEdicaoStatus').val(response.data.status);
                            $('#solicitarEdicaoResponsaveis').val(response.data.responsaveis);

                            // Armazenar dados originais para comparação
                            window.projetoOriginalData = response.data;

                            // Carregar evidências para solicitação
                            $.ajax({
                                url: '<?= site_url("projetos/listar-evidencias/") ?>' + projetoId,
                                type: 'GET',
                                dataType: 'json',
                                success: function(evResponse) {
                                    if (evResponse.success && evResponse.data) {
                                        var $container = $('#evidenciasProjetoAtuaisListSolicitacao .list-group');
                                        $container.empty();

                                        evResponse.data.forEach(function(evidencia) {
                                            var html = `
                                        <div class="list-group-item"
                                             data-id="${evidencia.id}"
                                             data-tipo="${evidencia.tipo}"
                                             data-conteudo="${evidencia.conteudo}"
                                             data-descricao="${evidencia.descricao}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">${evidencia.descricao || 'Sem descrição'}</h6>
                                                    <small class="text-muted">${evidencia.tipo === 'texto' ? 'Texto' : 'Link'}</small>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger btn-remover-evidencia-solicitacao">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2">
                                                ${evidencia.tipo === 'texto'
                                                    ? `<p class="mb-0">${evidencia.conteudo}</p>`
                                                    : `<a href="${evidencia.conteudo}" target="_blank">${evidencia.conteudo}</a>`}
                                            </div>
                                        </div>`;
                                            $container.append(html);
                                        });
                                        $('#contadorEvidenciasProjetoAtuaisSolicitacao').text(evResponse.data.length);
                                    }
                                },
                                error: function() {
                                    $('#evidenciasProjetoAtuaisListSolicitacao .list-group').html(
                                        '<div class="text-center py-3 text-danger">Erro ao carregar evidências</div>'
                                    );
                                }
                            });

                            $('#solicitarEdicaoModal').modal('show');
                        }
                    } else {
                        showErrorAlert(response.message || 'Erro ao carregar dados do projeto');
                    }
                },
                error: function(xhr, status, error) {
                    $botao.html(originalHtml).prop('disabled', false);

                    // Verificar se a resposta contém HTML (possível redirecionamento)
                    if (xhr.responseText && xhr.responseText.startsWith('<!')) {
                        showErrorAlert('Sessão expirada ou acesso não autorizado. Por favor, faça login novamente.');
                        // Redirecionar para login se necessário
                        window.location.href = '<?= site_url("login") ?>';
                    } else {
                        showErrorAlert('Falha ao carregar projeto: ' + error);
                    }
                }
            });
        });

        // Função auxiliar para carregar evidências
        function carregarEvidenciasProjeto(projetoId) {
            $('#evidenciasProjetoAtuaisList .list-group').html(
                '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Carregando evidências...</div>'
            );

            $.get('<?= site_url("projetos/listar-evidencias/") ?>' + projetoId, function(response) {
                if (response.success && response.data) {
                    var $container = $('#evidenciasProjetoAtuaisList .list-group');
                    $container.empty();

                    response.data.forEach(function(evidencia) {
                        var html = `
                <div class="list-group-item"
                     data-id="${evidencia.id}"
                     data-tipo="${evidencia.tipo}"
                     data-conteudo="${evidencia.conteudo}"
                     data-descricao="${evidencia.descricao}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1"><strong>Descrição:</strong> ${evidencia.descricao || 'Sem descrição'}</h6>
                            <small class="text-muted">${evidencia.tipo === 'texto' ? 'Texto' : 'Link'}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger btn-remover-evidencia">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <strong>Evidência:</strong>
                        ${evidencia.tipo === 'texto' ?
                            `<p class="mb-0">${evidencia.conteudo}</p>` :
                            `<a href="${evidencia.conteudo}" target="_blank">${evidencia.conteudo}</a>`}
                    </div>
                </div>`;
                        $container.append(html);
                    });
                    $('#contadorEvidenciasProjetoAtuais').text(response.data.length);
                } else {
                    $('#evidenciasProjetoAtuaisList .list-group').html(
                        '<div class="text-center py-3 text-muted">Nenhuma evidência encontrada</div>'
                    );
                }
            }).fail(function() {
                $('#evidenciasProjetoAtuaisList .list-group').html(
                    '<div class="text-center py-3 text-danger">Erro ao carregar evidências</div>'
                );
            });
        }


        $(document).on('click', '.btn-danger[title="Excluir"], .btn-danger[title="Solicitar Exclusão"]', function() {
            var isAdmin = $(this).attr('title') === 'Excluir';
            var projetoCompletoId = $(this).data('id');
            var projetoId = projetoCompletoId.split('-')[0];
            var projetoName = $(this).closest('tr').find('td:nth-child(2)').text();

            if (isAdmin) {
                $('#deleteProjetoId').val(projetoId);
                $('#projetoNameToDelete').text(projetoName);
                $('#formDeleteProjeto').attr('action', '<?= site_url("projetos/excluir/$idPlano") ?>');
                $('#deleteProjetoModal').modal('show');
            } else {
                $.ajax({
                    url: '<?= site_url('projetos/dados-projeto/') ?>' + projetoId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            var projeto = response.data;
                            var dadosAtuais = `Identificador: ${projeto.identificador}\nNome: ${projeto.nome}\nDescrição: ${projeto.descricao}\nProjeto Vinculado: ${projeto.projeto_vinculado}\nEixo: ${projeto.id_eixo}\nResponsáveis: ${projeto.responsaveis}`;

                            $('#solicitarExclusaoId').val(projeto.id);
                            $('#projetoNameToRequestDelete').text(projetoName);
                            $('#solicitarExclusaoDadosAtuais').val(dadosAtuais);
                            $('#solicitarExclusaoModal').modal('show');
                        } else {
                            showErrorAlert(response.message || "Erro ao carregar projeto");
                        }
                    },
                    error: function(xhr, status, error) {
                        showErrorAlert("Falha na comunicação com o servidor.");
                    }
                });
            }
        });

        // Evento quando o modal de solicitação de edição é aberto
        $('#solicitarEdicaoModal').on('shown.bs.modal', function() {
            const projetoId = $('#solicitarEdicaoId').val();
            if (!projetoId) return;

            // Resetar contadores e listas
            $('#evidenciasProjetoAtuaisListSolicitacao .list-group').html(
                '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Carregando evidências...</div>'
            );
            $('#evidenciasProjetoRemoverListSolicitacao .list-group').empty();
            $('#contadorEvidenciasProjetoAtuaisSolicitacao, #contadorEvidenciasProjetoRemoverSolicitacao').text('0');

            // Carregar evidências
            $.ajax({
                url: `<?= site_url('projetos/listar-evidencias/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        renderizarEvidenciasSolicitacao(response.data, '#evidenciasProjetoAtuaisListSolicitacao .list-group');
                        $('#contadorEvidenciasProjetoAtuaisSolicitacao').text(response.data.length || 0);
                    } else {
                        $('#evidenciasProjetoAtuaisListSolicitacao .list-group').html(
                            '<div class="text-center py-3 text-muted"><i class="fas fa-info-circle"></i> Nenhuma evidência encontrada</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    $('#evidenciasProjetoAtuaisListSolicitacao .list-group').html(
                        '<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle"></i> Erro ao carregar evidências</div>'
                    );
                }
            });
        });

        // Função para renderizar evidências no modal de solicitação
        function renderizarEvidenciasSolicitacao(evidencias, containerSelector) {
            const $container = $(containerSelector);
            $container.empty();

            if (evidencias && evidencias.length > 0) {
                evidencias.forEach(evidencia => {
                    const html = `
                <div class="list-group-item"
                     data-id="${evidencia.id}"
                     data-tipo="${evidencia.tipo}"
                     data-conteudo="${evidencia.conteudo}"
                     data-descricao="${evidencia.descricao}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${evidencia.descricao || 'Sem descrição'}</h6>
                            <small class="text-muted">${evidencia.tipo === 'texto' ? 'Texto' : 'Link'}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger btn-remover-evidencia-solicitacao" title="Remover evidência">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        ${evidencia.tipo === 'texto' ?
                            `<p class="mb-0">${evidencia.conteudo}</p>` :
                            `<a href="${evidencia.conteudo}" target="_blank">${evidencia.conteudo}</a>`}
                    </div>
                </div>`;
                    $container.append(html);
                });
            } else {
                $container.html('<div class="text-center py-3 text-muted"><i class="fas fa-info-circle"></i> Nenhuma evidência encontrada</div>');
            }
        }

        // Alternar entre tipos de evidência (texto/link) no modal de solicitação
        $('input[name="evidencia_projeto_tipo_solicitacao"]').change(function() {
            if ($(this).val() === 'texto') {
                $('#solicitarEdicaoGrupoTexto').removeClass('d-none');
                $('#solicitarEdicaoGrupoLink').addClass('d-none');
            } else {
                $('#solicitarEdicaoGrupoTexto').addClass('d-none');
                $('#solicitarEdicaoGrupoLink').removeClass('d-none');
            }
        });

        // Adicionar evidência à lista (apenas localmente) no modal de solicitação
        $('#btnAdicionarEvidenciaProjetoSolicitacao').click(function() {
            var tipo = $('input[name="evidencia_projeto_tipo_solicitacao"]:checked').val();
            var conteudo = tipo === 'texto' ? $('#solicitarEdicaoEvidenciaTexto').val() : $('#solicitarEdicaoEvidenciaLink').val();
            var descricao = $('#solicitarEdicaoEvidenciaDescricao').val();

            if (!conteudo) {
                showErrorAlert('Preencha o conteúdo da evidência');
                return;
            }

            var html = `
        <div class="list-group-item evidencia-nova"
             data-tipo="${tipo}"
             data-conteudo="${conteudo}"
             data-descricao="${descricao}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">${descricao || 'Sem descrição'}</h6>
                    <small class="text-muted">${tipo === 'texto' ? 'Texto' : 'Link'}</small>
                </div>
                <button class="btn btn-sm btn-outline-danger btn-remover-evidencia-solicitacao" title="Remover evidência">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <div class="mt-2">
                ${tipo === 'texto' ?
                    `<p class="mb-0">${conteudo}</p>` :
                    `<a href="${conteudo}" target="_blank">${conteudo}</a>`}
            </div>
        </div>`;

            $('#evidenciasProjetoAtuaisListSolicitacao .list-group').append(html);

            // Limpa os campos
            if (tipo === 'texto') {
                $('#solicitarEdicaoEvidenciaTexto').val('');
            } else {
                $('#solicitarEdicaoEvidenciaLink').val('');
            }
            $('#solicitarEdicaoEvidenciaDescricao').val('');

            // Atualiza o contador
            var count = $('#evidenciasProjetoAtuaisListSolicitacao .list-group-item').length;
            $('#contadorEvidenciasProjetoAtuaisSolicitacao').text(count);
        });

        // Remover evidência da lista (apenas localmente) no modal de solicitação
        $(document).on('click', '.btn-remover-evidencia-solicitacao', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(this).closest('.list-group-item');
            var id = $item.data('id');

            if (id) {
                // Apenas move para a lista de remoção (não remove do banco ainda)
                $item.removeClass('list-group-item-danger')
                    .addClass('list-group-item-warning')
                    .find('.btn-remover-evidencia-solicitacao')
                    .removeClass('btn-outline-danger')
                    .addClass('btn-outline-secondary')
                    .html('<i class="fas fa-undo"></i>')
                    .attr('title', 'Desfazer remoção');

                // Move o item para a lista de remoção
                $('#evidenciasProjetoRemoverListSolicitacao .list-group').append($item);

                // Atualiza contadores
                $('#contadorEvidenciasProjetoAtuaisSolicitacao').text($('#evidenciasProjetoAtuaisListSolicitacao .list-group-item').length);
                $('#contadorEvidenciasProjetoRemoverSolicitacao').text($('#evidenciasProjetoRemoverListSolicitacao .list-group-item').length);
            } else {
                // Se não tem ID, é nova - pode remover imediatamente
                $item.remove();
                $('#contadorEvidenciasProjetoAtuaisSolicitacao').text($('#evidenciasProjetoAtuaisListSolicitacao .list-group-item').length);
            }
        });

        // Desfazer remoção de evidência no modal de solicitação
        $(document).on('click', '.btn-remover-evidencia-solicitacao.btn-outline-secondary', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(this).closest('.list-group-item');
            $item.removeClass('list-group-item-warning')
                .find('.btn-remover-evidencia-solicitacao')
                .removeClass('btn-outline-secondary')
                .addClass('btn-outline-danger')
                .html('<i class="fas fa-trash-alt"></i>')
                .attr('title', 'Remover evidência');

            // Move de volta para a lista atual
            $('#evidenciasProjetoAtuaisListSolicitacao .list-group').append($item);

            // Atualiza contadores
            $('#contadorEvidenciasProjetoAtuaisSolicitacao').text($('#evidenciasProjetoAtuaisListSolicitacao .list-group-item').length);
            $('#contadorEvidenciasProjetoRemoverSolicitacao').text($('#evidenciasProjetoRemoverListSolicitacao .list-group-item').length);
        });



        // Função para renderizar evidências
        function renderizarEvidencias(evidencias, containerSelector) {
            const $container = $(containerSelector);
            $container.empty();

            if (evidencias && evidencias.length > 0) {
                evidencias.forEach(evidencia => {
                    const html = `
            <div class="list-group-item"
                 data-id="${evidencia.id}"
                 data-tipo="${evidencia.tipo}"
                 data-conteudo="${evidencia.conteudo}"
                 data-descricao="${evidencia.descricao}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1"><strong>Descrição:</strong> ${evidencia.descricao || 'Sem descrição'}</h6>
                        <small class="text-muted">${evidencia.tipo === 'texto' ? 'Texto' : 'Link'}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger btn-remover-evidencia" title="Remover evidência">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="mt-2">
                    <strong>Evidência:</strong>
                    ${evidencia.tipo === 'texto'
                        ? `<p class="mb-0">${evidencia.conteudo}</p>`
                        : `<a href="${evidencia.conteudo}" target="_blank">${evidencia.conteudo}</a>`}
                </div>
            </div>`;
                    $container.append(html);
                });
            } else {
                $container.html('<div class="text-center py-3 text-muted"><i class="fas fa-info-circle"></i> Nenhuma evidência encontrada</div>');
            }
        }

        // Evento quando o modal de edição é aberto
        $('#editProjetoModal').on('shown.bs.modal', function() {
            const projetoId = $('#editProjetoId').val();
            if (!projetoId) return;

            // Resetar contadores e listas
            $('#evidenciasProjetoAtuaisList .list-group').html(
                '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Carregando evidências...</div>'
            );
            $('#evidenciasProjetoRemoverList .list-group').empty();
            $('#contadorEvidenciasProjetoAtuais, #contadorEvidenciasProjetoRemover').text('0');

            // Carregar evidências
            $.ajax({
                url: `<?= site_url('projetos/listar-evidencias/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        // Armazenar evidências originais para comparação
                        window.evidenciasOriginais = response.data;

                        renderizarEvidencias(response.data, '#evidenciasProjetoAtuaisList .list-group');
                        $('#contadorEvidenciasProjetoAtuais').text(response.data.length || 0);
                    } else {
                        $('#evidenciasProjetoAtuaisList .list-group').html(
                            '<div class="text-center py-3 text-muted"><i class="fas fa-info-circle"></i> Nenhuma evidência encontrada</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    $('#evidenciasProjetoAtuaisList .list-group').html(
                        '<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle"></i> Erro ao carregar evidências</div>'
                    );
                }
            });
        });

        // Alternar entre tipos de evidência (texto/link)
        $('input[name="evidencia_projeto_tipo"]').change(function() {
            if ($(this).val() === 'texto') {
                $('#editProjetoGrupoTexto').removeClass('d-none');
                $('#editProjetoGrupoLink').addClass('d-none');
            } else {
                $('#editProjetoGrupoTexto').addClass('d-none');
                $('#editProjetoGrupoLink').removeClass('d-none');
            }
        });

        // Adicionar evidência à lista (apenas localmente)
        $('#btnAdicionarEvidenciaProjeto').click(function() {
            var tipo = $('input[name="evidencia_projeto_tipo"]:checked').val();
            var conteudo = tipo === 'texto' ?
                $('#editProjetoEvidenciaTexto').val().trim() :
                $('#editProjetoEvidenciaLink').val().trim();
            var descricao = $('#editProjetoEvidenciaDescricao').val().trim();

            if (!conteudo) {
                showErrorAlert('Preencha o conteúdo da evidência');
                return;
            }

            var html = `
    <div class="list-group-item evidencia-nova"
         data-tipo="${tipo}"
         data-conteudo="${conteudo}"
         data-descricao="${descricao}">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1"><strong>Descrição:</strong> ${descricao || 'Sem descrição'}</h6>
                <small class="text-muted">${tipo === 'texto' ? 'Texto' : 'Link'}</small>
            </div>
            <button class="btn btn-sm btn-outline-danger btn-remover-evidencia" title="Remover evidência">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
        <div class="mt-2">
            <strong>Evidência:</strong>
            ${tipo === 'texto'
                ? `<p class="mb-0">${conteudo}</p>`
                : `<a href="${conteudo}" target="_blank">${conteudo}</a>`}
        </div>
    </div>`;

            $('#evidenciasProjetoAtuaisList .list-group').append(html);

            // Limpa os campos
            if (tipo === 'texto') {
                $('#editProjetoEvidenciaTexto').val('');
            } else {
                $('#editProjetoEvidenciaLink').val('');
            }
            $('#editProjetoEvidenciaDescricao').val('');

            // Atualiza o contador
            var count = $('#evidenciasProjetoAtuaisList .list-group-item').length;
            $('#contadorEvidenciasProjetoAtuais').text(count);
        });

        // Remover evidência da lista (apenas localmente)
        $(document).on('click', '.btn-remover-evidencia', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(this).closest('.list-group-item');
            var id = $item.data('id');

            if (id) {
                // Apenas move para a lista de remoção (não remove do banco ainda)
                $item.removeClass('list-group-item-danger') // Remove classe de perigo se existir
                    .addClass('list-group-item-warning') // Adiciona classe de aviso
                    .find('.btn-remover-evidencia')
                    .removeClass('btn-outline-danger')
                    .addClass('btn-outline-secondary')
                    .html('<i class="fas fa-undo"></i>')
                    .attr('title', 'Desfazer remoção');

                // Move o item para a lista de remoção
                $('#evidenciasProjetoRemoverList .list-group').append($item);

                // Atualiza contadores
                $('#contadorEvidenciasProjetoAtuais').text($('#evidenciasProjetoAtuaisList .list-group-item').length);
                $('#contadorEvidenciasProjetoRemover').text($('#evidenciasProjetoRemoverList .list-group-item').length);
            } else {
                // Se não tem ID, é nova - pode remover imediatamente
                $item.remove();
                $('#contadorEvidenciasProjetoAtuais').text($('#evidenciasProjetoAtuaisList .list-group-item').length);
            }
        });

        // Adicione este evento para desfazer a remoção
        $(document).on('click', '.btn-remover-evidencia.btn-outline-secondary', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(this).closest('.list-group-item');
            $item.removeClass('list-group-item-warning')
                .find('.btn-remover-evidencia')
                .removeClass('btn-outline-secondary')
                .addClass('btn-outline-danger')
                .html('<i class="fas fa-trash-alt"></i>')
                .attr('title', 'Remover evidência');

            // Move de volta para a lista atual
            $('#evidenciasProjetoAtuaisList .list-group').append($item);

            // Atualiza contadores
            $('#contadorEvidenciasProjetoAtuais').text($('#evidenciasProjetoAtuaisList .list-group-item').length);
            $('#contadorEvidenciasProjetoRemover').text($('#evidenciasProjetoRemoverList .list-group-item').length);
        });
    });
</script>