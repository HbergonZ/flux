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
            "searching": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": '<?= site_url("projetos/filtrar/$idPlano") ?>',
                "type": "POST",
                "data": function(d) {
                    // Adicione seus filtros aqui
                    d.nome = $('#filterProjeto').val();
                    d.projeto_vinculado = $('#filterProjetoVinculado').val();
                    d.id_eixo = $('#filterEixo').val();
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
                    "data": "metas",
                    "className": "text-wrap align-middle"
                },
                {
                    "data": "projeto_vinculado",
                    "className": "text-wrap align-middle"
                },
                {
                    "data": "responsaveis",
                    "className": "text-wrap align-middle",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            if (data && data.length > 0) {
                                return data.map(user => user ? user.name || '' : '').filter(Boolean).join(', ');
                            }
                            return 'Nenhum responsável';
                        }
                        // Para ordenação/filtro, retorna texto simples
                        return data && data.length > 0 ?
                            data.map(u => u ? `${u.username || ''}` : '').filter(Boolean).join(', ') :
                            'Nenhum responsável';
                    }
                },
                {
                    "data": "data_fim",
                    "className": "text-center align-middle",
                    "type": "date", // Isso é essencial para a ordenação correta
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            if (!data) {
                                return '<span class="text-muted">-</span>'; // Exibe um traço para datas vazias
                            }
                            // Formata a data para exibição (DD/MM/YYYY)
                            const date = new Date(data);
                            const day = String(date.getDate()).padStart(2, '0');
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const year = date.getFullYear();
                            return `${day}/${month}/${year}`;
                        }
                        // Para ordenação/filtro, retorna o valor original no formato ISO (YYYY-MM-DD)
                        return data || ''; // Retorna string vazia se data for nula
                    }
                },
                {
                    "data": "progresso",
                    "className": "text-center align-middle",
                    "orderable": false,
                    "type": "num",
                    "render": function(data, type, row) {
                        if (type === 'display') {
                            const percentual = data?.percentual || 0;
                            const texto = data?.texto || 'Dados de progresso não disponíveis';
                            const progressClass = data?.class || 'bg-secondary';

                            return `
                <div class="progress-container" title="${texto}" data-sort="${percentual}">
                    <div class="progress progress-sm">
                        <div class="progress-bar progress-bar-striped ${progressClass}"
                            role="progressbar" style="width: ${percentual}%"
                            aria-valuenow="${percentual}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="progress-text">${percentual}%</small>
                </div>
            `;
                        }
                        // Para ordenação e filtro, retorna apenas o valor numérico
                        return data?.percentual || 0;
                    }
                },
                {
                    "data": "acoes",
                    "className": "text-center align-middle",
                    "orderable": false,
                    "render": function(data, type, row) {
                        var buttons = `
                    <div class="d-inline-flex">
                        <a href="<?= site_url('planos/') ?><?= $plano['id'] ?>/projetos/${data.id.split('-')[0]}/etapas"
                           class="btn btn-secondary btn-sm mx-1" title="Visualizar Etapas">
                            <i class="fas fa-tasks"></i>
                        </a>
                        <a href="<?= site_url('planos/') ?><?= $plano['id'] ?>/projetos/${data.id.split('-')[0]}/acoes"
                           class="btn btn-info btn-sm mx-1" title="Acessar ações não vinculadas à etapas">
                            <i class="fas fa-th-list"></i>
                        </a>`;

                        if (data.isAdmin) {
                            buttons += `
                        <button type="button" class="btn btn-primary btn-sm mx-1"
                                style="width: 32px; height: 32px;" data-id="${data.id}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm mx-1"
                                style="width: 32px; height: 32px;" data-id="${data.id}" title="Excluir">
                            <i class="fas fa-trash-alt"></i>
                        </button>`;
                        } else {
                            buttons += `
                        <button type="button" class="btn btn-primary btn-sm mx-1"
                                style="width: 32px; height: 32px;" data-id="${data.id}" title="Solicitar Edição">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm mx-1"
                                style="width: 32px; height: 32px;" data-id="${data.id}" title="Solicitar Exclusão">
                            <i class="fas fa-trash-alt"></i>
                        </button>`;
                        }

                        buttons += `</div>`;
                        return buttons;
                    }
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

            // Coletar dados básicos do formulário
            const formData = {
                id_projeto: $('#solicitarEdicaoId').val(),
                id_plano: $('#idPlano').val(),
                identificador: $('#solicitarEdicaoIdentificador').val(),
                nome: $('#solicitarEdicaoNome').val(),
                descricao: $('#solicitarEdicaoDescricao').val(),
                metas: $('#solicitarEdicaoMetas').val(),
                projeto_vinculado: $('#solicitarEdicaoVinculado').val(),
                priorizacao_gab: $('#solicitarEdicaoPriorizacao').val(),
                id_eixo: $('#solicitarEdicaoEixo').val(),
                status: $('#solicitarEdicaoStatus').val(),
                justificativa: $('#solicitarEdicaoJustificativa').val(),
                responsaveis: {
                    atuais: getResponsaveisAtuaisSolicitacao()
                },
                evidencias: {
                    adicionar: getEvidenciasParaAdicionarSolicitacao(),
                    remover: getEvidenciasParaRemoverSolicitacao()
                },
                indicadores: {
                    adicionar: getIndicadoresParaAdicionarSolicitacao(),
                    remover: getIndicadoresParaRemoverSolicitacao()
                }
            };

            // Verificar se há alterações em qualquer campo
            const projetoOriginal = window.projetoOriginalData || {};
            let hasChanges = false;

            // Campos básicos para comparar
            const camposParaComparar = [
                'identificador', 'nome', 'descricao', 'metas', 'projeto_vinculado',
                'priorizacao_gab', 'id_eixo', 'status'
            ];

            camposParaComparar.forEach(campo => {
                if (JSON.stringify(projetoOriginal[campo]) !== JSON.stringify(formData[campo])) {
                    hasChanges = true;
                }
            });

            // Verificar alterações em evidências e indicadores
            if (formData.evidencias.adicionar.length > 0 || formData.evidencias.remover.length > 0 ||
                formData.indicadores.adicionar.length > 0 || formData.indicadores.remover.length > 0) {
                hasChanges = true;
            }

            // Verificar alterações em responsáveis
            const responsaveisAtuais = $('#responsaveisAtuaisListSolicitacao .list-group-item')
                .map(function() {
                    const id = $(this).data('user-id');
                    return id ? parseInt(id) : null;
                }).get().filter(Boolean);

            const responsaveisOriginais = window.projetoOriginalData.responsaveis || [];
            const responsaveisOriginaisIds = responsaveisOriginais.map(r => {
                return r.usuario_id || r.id || r;
            }).filter(Boolean).map(id => parseInt(id));

            if (JSON.stringify(responsaveisAtuais.sort()) !== JSON.stringify(responsaveisOriginaisIds.sort())) {
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
            $('#evidenciasProjetoAtuaisListSolicitacao .list-group-item').each(function() {
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

            // 🔹 Coletar indicadores para adicionar (novos)
            var indicadoresAdicionar = [];
            $('#indicadoresAtuaisList .list-group-item').each(function() {
                if (!$(this).data('id')) {
                    indicadoresAdicionar.push({
                        conteudo: $(this).data('conteudo'),
                        descricao: $(this).data('descricao')
                    });
                }
            });

            // 🔹 Coletar indicadores para remover (IDs)
            var indicadoresRemover = [];
            $('#indicadoresRemoverList .list-group-item').each(function() {
                var id = $(this).data('id');
                if (id) indicadoresRemover.push(id);
            });

            // 🔹 Coletar evidências para adicionar (novas)
            var evidenciasAdicionar = [];
            $('#evidenciasProjetoAtuaisList .list-group-item').each(function() {
                if (!$(this).data('id')) {
                    evidenciasAdicionar.push({
                        tipo: $(this).data('tipo'),
                        conteudo: $(this).data('conteudo'),
                        descricao: $(this).data('descricao')
                    });
                }
            });

            // 🔹 Coletar evidências para remover (IDs)
            var evidenciasRemover = [];
            $('#evidenciasProjetoRemoverList .list-group-item').each(function() {
                var id = $(this).data('id');
                if (id) evidenciasRemover.push(id);
            });

            // 🔹 Coletar responsáveis
            var responsaveisAtuaisIds = [];
            $('#responsaveisAtuaisList .list-group-item').each(function() {
                responsaveisAtuaisIds.push($(this).data('user-id'));
            });

            var responsaveisOriginais = window.responsaveisOriginais || [];
            var responsaveisOriginaisIds = responsaveisOriginais.map(u => u.usuario_id);

            var responsaveisAdicionar = responsaveisAtuaisIds.filter(id =>
                !responsaveisOriginaisIds.includes(id));
            var responsaveisRemover = responsaveisOriginaisIds.filter(id =>
                !responsaveisAtuaisIds.includes(id));

            // 🔹 Limpar campos hidden existentes
            form.find('input[name="indicadores_adicionar"], input[name="indicadores_remover"], input[name="evidencias_adicionar"], input[name="evidencias_remover"], input[name="responsaveis_adicionar"], input[name="responsaveis_remover"]').remove();

            // 🔹 Adicionar campos hidden com dados atualizados
            form.append(
                $('<input>', {
                    type: 'hidden',
                    name: 'indicadores_adicionar',
                    value: JSON.stringify(indicadoresAdicionar)
                }),
                $('<input>', {
                    type: 'hidden',
                    name: 'indicadores_remover',
                    value: JSON.stringify(indicadoresRemover)
                }),
                $('<input>', {
                    type: 'hidden',
                    name: 'evidencias_adicionar',
                    value: JSON.stringify(evidenciasAdicionar)
                }),
                $('<input>', {
                    type: 'hidden',
                    name: 'evidencias_remover',
                    value: JSON.stringify(evidenciasRemover)
                }),
                $('<input>', {
                    type: 'hidden',
                    name: 'responsaveis_adicionar',
                    value: JSON.stringify(responsaveisAdicionar)
                }),
                $('<input>', {
                    type: 'hidden',
                    name: 'responsaveis_remover',
                    value: JSON.stringify(responsaveisRemover)
                })
            );

            // 🔹 Enviar o formulário
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
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            var isAdmin = $(this).attr('title') === 'Editar';
            var projetoCompletoId = $(this).data('id');
            var projetoId = projetoCompletoId.split('-')[0];

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
                    if (response.success && response.data) {
                        if (isAdmin) {
                            // Preencher modal de edição normal para admin
                            $('#editProjetoId').val(response.data.id);
                            $('#editProjetoIdentificador').val(response.data.identificador);
                            $('#editProjetoNome').val(response.data.nome);
                            $('#editProjetoDescricao').val(response.data.descricao);
                            $('#editProjetoMetas').val(response.data.metas);
                            $('#editProjetoVinculado').val(response.data.projeto_vinculado);
                            $('#editProjetoEixo').val(response.data.id_eixo);
                            $('#editProjetoPriorizacao').val(response.data.priorizacao_gab);
                            $('#projetoStatus').val(response.data.status);
                            $('#editProjetoResponsaveis').val(response.data.responsaveis);

                            // Carregar responsáveis e usuários disponíveis
                            carregarResponsaveis(projetoId);
                            carregarUsuariosDisponiveis(projetoId);

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
                            carregarEvidenciasSolicitacao(projetoId);

                            $('#solicitarEdicaoModal').modal('show');
                        }
                    } else {
                        showErrorAlert(response.message || 'Erro ao carregar dados do projeto');
                    }
                },
                error: function(xhr, status, error) {
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

        // Função para carregar responsáveis atualizados
        function carregarResponsaveis(projetoId) {
            console.log('Carregando responsáveis para o projeto:', projetoId);

            $.ajax({
                url: `<?= site_url('projetos/responsaveis/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta de responsáveis:', response);
                    const $container = $('#responsaveisAtuaisList');
                    $container.empty();

                    if (response.success && response.data) {
                        // Armazenar os responsáveis originais para comparação posterior
                        window.responsaveisOriginais = response.data;

                        if (response.data.length > 0) {
                            response.data.forEach(user => {
                                $container.append(`
                            <div class="list-group-item d-flex justify-content-between align-items-center"
                                 data-user-id="${user.usuario_id}">
                                <div>
                                    <strong>${user.name || user.username}</strong>
                                    ${user.email ? `<div class="text-muted small">${user.email}</div>` : ''}
                                </div>
                                <button class="btn btn-sm btn-outline-danger btn-remover-responsavel"
                                        title="Remover responsável">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </div>
                        `);
                            });
                            $('#contadorResponsaveisAtuais').text(response.data.length);
                        } else {
                            $container.html('<div class="text-center py-3 text-muted">Nenhum responsável</div>');
                            $('#contadorResponsaveisAtuais').text('0');
                        }
                    } else {
                        $container.html('<div class="text-center py-3 text-danger">Erro ao carregar responsáveis</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao carregar responsáveis:', error);
                    $('#responsaveisAtuaisList').html('<div class="text-center py-3 text-danger">Erro ao carregar responsáveis</div>');
                }
            });
        }

        function carregarUsuariosDisponiveis(projetoId) {
            console.log('Carregando usuários disponíveis para o projeto:', projetoId);

            $.ajax({
                url: `<?= site_url('projetos/usuarios-disponiveis/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta de usuários disponíveis:', response);
                    const $container = $('#usuariosDisponiveisList');
                    $container.empty();

                    if (response.success && response.data) {
                        if (response.data.length > 0) {
                            response.data.forEach(user => {
                                $container.append(`
                            <div class="list-group-item d-flex justify-content-between align-items-center"
                                 data-user-id="${user.id}">
                                <div>
                                    <strong>${user.name || user.username}</strong>
                                    ${user.email ? `<div class="text-muted small">${user.email}</div>` : ''}
                                </div>
                                <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel"
                                        title="Adicionar como responsável">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        `);
                            });
                        } else {
                            $container.html('<div class="text-center py-3 text-muted">Todos os usuários já são responsáveis</div>');
                        }
                        $('#contadorUsuariosDisponiveis').text(response.data.length);
                    } else {
                        $container.html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao carregar usuários disponíveis:', error);
                    $('#usuariosDisponiveisList').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
            });
        }

        // Função para carregar usuários disponíveis
        function carregarUsuariosDisponiveis(projetoId) {
            console.log('Carregando usuários disponíveis para o projeto:', projetoId);

            $.ajax({
                url: `<?= site_url('projetos/usuarios-disponiveis/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta de usuários disponíveis:', response);
                    const $container = $('#usuariosDisponiveisList');
                    $container.empty();

                    if (response.success && response.data) {
                        if (response.data.length > 0) {
                            response.data.forEach(user => {
                                $container.append(`
                            <div class="list-group-item d-flex justify-content-between align-items-center"
                                 data-user-id="${user.id}">
                                <div>
                                    <strong>${user.name}</strong>
                                    ${user.email ? `<div class="text-muted small">${user.email}</div>` : ''}
                                </div>
                                <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel"
                                        title="Adicionar como responsável">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        `);
                            });
                        } else {
                            $container.html('<div class="text-center py-3 text-muted">Todos os usuários já são responsáveis</div>');
                        }
                        $('#contadorUsuariosDisponiveis').text(response.data.length);
                    } else {
                        $container.html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao carregar usuários disponíveis:', error);
                    $('#usuariosDisponiveisList').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
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

            carregarEvidencias(
                projetoId,
                '#evidenciasProjetoAtuaisListSolicitacao .list-group',
                '#contadorEvidenciasProjetoAtuaisSolicitacao',
                true
            );

            $('#evidenciasProjetoRemoverListSolicitacao .list-group').empty();
            $('#contadorEvidenciasProjetoRemoverSolicitacao').text('0');
        });

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
            var conteudo = tipo === 'texto' ?
                $('#solicitarEdicaoEvidenciaTexto').val().trim() :
                $('#solicitarEdicaoEvidenciaLink').val().trim();
            var descricao = $('#solicitarEdicaoEvidenciaDescricao').val().trim();

            if (!conteudo) {
                showErrorAlert('Preencha o conteúdo da evidência');
                return;
            }

            var novaEvidencia = {
                tipo: tipo,
                conteudo: conteudo,
                descricao: descricao
            };

            $('#evidenciasProjetoAtuaisListSolicitacao .list-group').append(renderizarEvidencia(novaEvidencia, true));

            // Limpar campos
            $('#solicitarEdicaoEvidenciaTexto, #solicitarEdicaoEvidenciaLink, #solicitarEdicaoEvidenciaDescricao').val('');

            // Atualizar contador
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
        function renderizarEvidencia(evidencia, isSolicitacao = false) {
            // Garante valores padrão consistentes
            const id = evidencia.id || '';
            const tipo = evidencia.tipo || 'texto';
            const conteudo = evidencia.conteudo || '';
            const descricao = evidencia.descricao || 'Sem descrição';
            const btnClass = isSolicitacao ? 'btn-remover-evidencia-solicitacao' : 'btn-remover-evidencia';

            return `
<div class="list-group-item ${!id ? 'evidencia-nova' : ''}"
     data-id="${id}"
     data-tipo="${tipo}"
     data-conteudo="${conteudo}"
     data-descricao="${descricao}">
    <!-- Container principal com posicionamento relativo -->
    <div class="position-relative">
        <!-- Botão de remover posicionado absolutamente no topo direito -->
        <button class="btn btn-sm btn-outline-danger ${btnClass} position-absolute"
                style="top: 0; right: 0;"
                title="Remover evidência">
            <i class="fas fa-trash-alt"></i>
        </button>

        <!-- Conteúdo da evidência com padding para não sobrepor o botão -->
        <div style="padding-right: 30px;">
            <!-- Conteúdo da evidência -->
            <div class="mb-2">
                <strong>Evidência:</strong>
                ${tipo === 'texto'
                    ? `<p class="mb-1">${conteudo}</p>`
                    : `<a href="${conteudo}" target="_blank">${conteudo}</a>`}
            </div>

            <!-- Descrição -->
            <div>
                <strong>Descrição:</strong>
                <p class="mb-1">${descricao}</p>
            </div>

            <!-- Tipo -->
            <small class="text-muted">${tipo === 'texto' ? 'Texto' : 'Link'}</small>
        </div>
    </div>
</div>`;
        }

        function carregarEvidencias(projetoId, containerSelector, contadorSelector, isSolicitacao = false) {
            $.ajax({
                url: `<?= site_url('projetos/listar-evidencias/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        var $container = $(containerSelector);
                        $container.empty();

                        response.data.forEach(function(ev) {
                            $container.append(renderizarEvidencia(ev, isSolicitacao));
                        });

                        $(contadorSelector).text(response.data.length || 0);
                    } else {
                        $(containerSelector).html(
                            '<div class="text-center py-3 text-muted">Nenhuma evidência encontrada</div>'
                        );
                    }
                },
                error: function() {
                    $(containerSelector).html(
                        '<div class="text-center py-3 text-danger">Erro ao carregar evidências</div>'
                    );
                }
            });
        }

        // Função auxiliar para carregar evidências do projeto
        function carregarEvidenciasProjeto(projetoId) {
            $.get(`<?= site_url('projetos/listar-evidencias/') ?>${projetoId}`, function(response) {
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

        // Função auxiliar para carregar evidências na solicitação
        function carregarEvidenciasSolicitacao(projetoId) {
            $('#loadingEvidenciasSolicitacao').removeClass('d-none');
            $('#evidenciasProjetoAtuaisListSolicitacao .list-group').empty();

            $.ajax({
                url: '<?= site_url("projetos/listar-evidencias/") ?>' + projetoId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#loadingEvidenciasSolicitacao').addClass('d-none');

                    if (response.success && response.data) {
                        var $container = $('#evidenciasProjetoAtuaisListSolicitacao .list-group');
                        $container.empty();

                        response.data.forEach(function(ev) {
                            $container.append(renderizarEvidencia({
                                id: ev.id,
                                tipo: ev.tipo,
                                conteudo: ev.tipo === 'texto' ? ev.evidencia : ev.link,
                                descricao: ev.descricao
                            }));
                        });

                        $('#contadorEvidenciasProjetoAtuaisSolicitacao').text(response.data.length);
                    } else {
                        $container.html('<div class="text-center py-3 text-muted">Nenhuma evidência encontrada</div>');
                    }
                },
                error: function() {
                    $('#loadingEvidenciasSolicitacao').addClass('d-none');
                    $('#evidenciasProjetoAtuaisListSolicitacao .list-group').html(
                        '<div class="text-center py-3 text-danger">Erro ao carregar evidências</div>'
                    );
                }
            });
        }

        // Evento quando o modal de edição é aberto
        // Variáveis globais para controle
        let responsaveisOriginais = [];

        $('#editProjetoModal').on('shown.bs.modal', function() {
            const projetoId = $('#editProjetoId').val();
            if (!projetoId) return;

            // 🔹 Limpar arrays de controle de responsáveis
            $('#formEditProjeto input[name="responsaveis_adicionar"]').val('[]');
            $('#formEditProjeto input[name="responsaveis_remover"]').val('[]');

            // 🔹 Carregar responsáveis atuais
            carregarResponsaveis(projetoId);

            // 🔹 Carregar indicadores
            carregarIndicadoresProjeto(projetoId);

            // 🔹 Limpar lista de indicadores para remoção
            $('#indicadoresRemoverList .list-group').empty();
            $('#contadorIndicadoresRemover').text('0');
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

        // Função para carregar progresso dos projetos
        function carregarProgressoProjetos() {
            $('.progress-container').each(function() {
                const container = $(this);
                const projetoId = container.data('projeto-id');

                if (!projetoId) {
                    console.error('ID do projeto não encontrado no container de progresso');
                    return;
                }

                $.ajax({
                    url: `<?= site_url("projetos/progresso/") ?>${projetoId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const percentual = response.percentual || 0;
                            const progressBar = container.find('.progress-bar');
                            const progressText = container.find('.progress-text');

                            // Atualiza a barra de progresso
                            progressBar.css('width', percentual + '%');
                            progressBar.attr('aria-valuenow', percentual);

                            // Atualiza o texto
                            progressText.text(percentual + '%');

                            // Atualiza o tooltip com o texto formatado
                            container.attr('title', response.texto ||
                                `${response.acoes_finalizadas} de ${response.total_acoes} ações finalizadas`);

                            // Muda a cor baseada no percentual
                            progressBar.removeClass('bg-success bg-warning bg-danger')
                                .addClass(getProgressClass(percentual));
                        } else {
                            container.attr('title', 'Erro ao carregar progresso');
                            console.error('Erro no progresso:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        container.attr('title', 'Erro ao carregar progresso');
                        console.error('Erro na requisição de progresso:', error);
                    }
                });
            });
        }

        // Função auxiliar para determinar a classe CSS do progresso
        function getProgressClass(percentual) {
            if (percentual >= 80) return 'bg-success';
            if (percentual >= 50) return 'bg-warning';
            return 'bg-danger';
        }

        // Chamada inicial quando a página carrega
        $(document).ready(function() {
            carregarProgressoProjetos();
        });

        // Eventos para o modal de edição
        $(document).on('click', '.btn-adicionar-responsavel', function() {
            const $item = $(this).closest('.list-group-item');
            const userId = $item.data('user-id');
            const userName = $item.find('strong').text();
            const userEmail = $item.find('.text-muted').text() || '';

            // Adiciona à lista de responsáveis
            $('#responsaveisAtuaisList').append(`
        <div class="list-group-item d-flex justify-content-between align-items-center"
             data-user-id="${userId}">
            <div>
                <strong>${userName}</strong>
                ${userEmail ? `<div class="text-muted small">${userEmail}</div>` : ''}
            </div>
            <button class="btn btn-sm btn-outline-danger btn-remover-responsavel"
                    title="Remover responsável">
                <i class="fas fa-user-minus"></i>
            </button>
        </div>
    `);

            // Remove da lista de disponíveis
            $item.remove();

            // Atualiza contadores
            $('#contadorResponsaveisAtuais').text(parseInt($('#contadorResponsaveisAtuais').text()) + 1);
            $('#contadorUsuariosDisponiveis').text(parseInt($('#contadorUsuariosDisponiveis').text()) - 1);

            // Atualiza lista de adições
            const adicionados = JSON.parse($('#formEditProjeto input[name="responsaveis_adicionar"]').val() || '[]');
            if (!adicionados.includes(userId)) {
                adicionados.push(userId);
                $('#formEditProjeto input[name="responsaveis_adicionar"]').val(JSON.stringify(adicionados));
            }

            // Remove da lista de remoções se estava lá
            const removidos = JSON.parse($('#formEditProjeto input[name="responsaveis_remover"]').val() || '[]');
            const index = removidos.indexOf(userId);
            if (index !== -1) {
                removidos.splice(index, 1);
                $('#formEditProjeto input[name="responsaveis_remover"]').val(JSON.stringify(removidos));
            }
        });


        $(document).on('click', '.btn-remover-responsavel', function() {
            const $item = $(this).closest('.list-group-item');
            const userId = $item.data('user-id');
            const userName = $item.find('strong').text();
            const userEmail = $item.find('.text-muted').text() || '';

            // Adiciona à lista de disponíveis
            $('#usuariosDisponiveisList').append(`
        <div class="list-group-item d-flex justify-content-between align-items-center"
             data-user-id="${userId}">
            <div>
                <strong>${userName}</strong>
                ${userEmail ? `<div class="text-muted small">${userEmail}</div>` : ''}
            </div>
            <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel"
                    title="Adicionar como responsável">
                <i class="fas fa-user-plus"></i>
            </button>
        </div>
    `);

            // Remove da lista de responsáveis
            $item.remove();

            // Atualiza contadores
            $('#contadorUsuariosDisponiveis').text(parseInt($('#contadorUsuariosDisponiveis').text()) + 1);
            $('#contadorResponsaveisAtuais').text(parseInt($('#contadorResponsaveisAtuais').text()) - 1);

            // Atualiza lista de remoções
            const removidos = JSON.parse($('#formEditProjeto input[name="responsaveis_remover"]').val() || '[]');
            if (!removidos.includes(userId)) {
                removidos.push(userId);
                $('#formEditProjeto input[name="responsaveis_remover"]').val(JSON.stringify(removidos));
            }

            // Remove da lista de adições se estava lá
            const adicionados = JSON.parse($('#formEditProjeto input[name="responsaveis_adicionar"]').val() || '[]');
            const index = adicionados.indexOf(userId);
            if (index !== -1) {
                adicionados.splice(index, 1);
                $('#formEditProjeto input[name="responsaveis_adicionar"]').val(JSON.stringify(adicionados));
            }
        });


        // Busca de usuários
        $('#buscaUsuarioResponsavel').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();

            $('#usuariosDisponiveisList .list-group-item').each(function() {
                const $item = $(this);
                const text = $item.text().toLowerCase();
                $item.toggle(text.includes(searchTerm));
            });
        });

        // Botão de limpar busca
        $('#btnLimparBuscaResponsaveis').click(function() {
            $('#buscaUsuarioResponsavel').val('');
            $('#usuariosDisponiveisList .list-group-item').show();
        });

        // Funções de renderização
        function renderizarResponsavel(user) {
            return `
<div class="list-group-item d-flex justify-content-between align-items-center" data-user-id="${user.usuario_id}">
    <div>
        <strong>${user.name || user.username}</strong>
        ${user.email ? `<div class="text-muted small">${user.email}</div>` : ''}
    </div>
    <button class="btn btn-sm btn-outline-danger btn-remover-responsavel" title="Remover responsável">
        <i class="fas fa-user-minus"></i>
    </button>
</div>`;
        }

        function renderizarUsuarioDisponivel(user) {
            return `
<div class="list-group-item d-flex justify-content-between align-items-center" data-user-id="${user.id}">
    <div>
        <strong>${user.name || user.username}</strong>
        ${user.email ? `<div class="text-muted small">${user.email}</div>` : ''}
    </div>
    <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel" title="Adicionar como responsável">
        <i class="fas fa-user-plus"></i>
    </button>
</div>`;
        }

        // Função para renderizar indicador
        function renderizarIndicador(indicador) {
            return `
<div class="list-group-item ${!indicador.id ? 'indicador-nova' : ''}"
     data-id="${indicador.id || ''}"
     data-conteudo="${indicador.conteudo}"
     data-descricao="${indicador.descricao || ''}">
    <div class="position-relative">
        <button class="btn btn-sm btn-outline-danger btn-remover-indicador position-absolute"
                style="top: 0; right: 0;"
                title="Remover indicador">
            <i class="fas fa-trash-alt"></i>
        </button>

        <div style="padding-right: 30px;">
            <div class="mb-2">
                <strong>Indicador:</strong>
                <p class="mb-1">${indicador.conteudo}</p>
            </div>

            <div>
                <strong>Descrição:</strong>
                <p class="mb-1">${indicador.descricao || 'Sem descrição'}</p>
            </div>
        </div>
    </div>
</div>`;
        }

        // Carregar indicadores do projeto
        function carregarIndicadoresProjeto(projetoId) {
            $.ajax({
                url: `<?= site_url('projetos/listar-indicadores/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const $container = $('#indicadoresAtuaisList .list-group');
                    $container.empty();

                    if (response.success && response.data) {
                        response.data.forEach(function(ind) {
                            $container.append(renderizarIndicador({
                                id: ind.id,
                                conteudo: ind.conteudo,
                                descricao: ind.descricao
                            }));
                        });
                        $('#contadorIndicadoresAtuais').text(response.data.length);
                    } else {
                        $container.html('<div class="text-center py-3 text-muted">Nenhum indicador encontrado</div>');
                    }
                },
                error: function() {
                    $('#indicadoresAtuaisList .list-group').html(
                        '<div class="text-center py-3 text-danger">Erro ao carregar indicadores</div>'
                    );
                }
            });
        }

        // Adicionar indicador à lista (apenas localmente)
        $('#btnAdicionarIndicadorProjeto').click(function() {
            const conteudo = $('#editProjetoIndicadorConteudo').val().trim();
            const descricao = $('#editProjetoIndicadorDescricao').val().trim();

            if (!conteudo) {
                showErrorAlert('Preencha o conteúdo do indicador');
                return;
            }

            const novoIndicador = {
                conteudo: conteudo,
                descricao: descricao
            };

            $('#indicadoresAtuaisList .list-group').append(renderizarIndicador(novoIndicador));

            // Limpar campos
            $('#editProjetoIndicadorConteudo, #editProjetoIndicadorDescricao').val('');

            // Atualizar contador
            const count = $('#indicadoresAtuaisList .list-group-item').length;
            $('#contadorIndicadoresAtuais').text(count);
        });

        // Remover indicador da lista (apenas localmente)
        $(document).on('click', '.btn-remover-indicador', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(this).closest('.list-group-item');
            const id = $item.data('id');

            if (id) {
                // Apenas move para a lista de remoção (não remove do banco ainda)
                $item.removeClass('list-group-item-danger')
                    .addClass('list-group-item-warning')
                    .find('.btn-remover-indicador')
                    .removeClass('btn-outline-danger')
                    .addClass('btn-outline-secondary')
                    .html('<i class="fas fa-undo"></i>')
                    .attr('title', 'Desfazer remoção');

                // Move o item para a lista de remoção
                $('#indicadoresRemoverList .list-group').append($item);

                // Atualiza contadores
                $('#contadorIndicadoresAtuais').text($('#indicadoresAtuaisList .list-group-item').length);
                $('#contadorIndicadoresRemover').text($('#indicadoresRemoverList .list-group-item').length);
            } else {
                // Se não tem ID, é novo - pode remover imediatamente
                $item.remove();
                $('#contadorIndicadoresAtuais').text($('#indicadoresAtuaisList .list-group-item').length);
            }
        });

        // Desfazer remoção de indicador
        $(document).on('click', '.btn-remover-indicador.btn-outline-secondary', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(this).closest('.list-group-item');
            $item.removeClass('list-group-item-warning')
                .find('.btn-remover-indicador')
                .removeClass('btn-outline-secondary')
                .addClass('btn-outline-danger')
                .html('<i class="fas fa-trash-alt"></i>')
                .attr('title', 'Remover indicador');

            // Move de volta para a lista atual
            $('#indicadoresAtuaisList .list-group').append($item);

            // Atualiza contadores
            $('#contadorIndicadoresAtuais').text($('#indicadoresAtuaisList .list-group-item').length);
            $('#contadorIndicadoresRemover').text($('#indicadoresRemoverList .list-group-item').length);
        });


        //--------------------------------------------
        // Parte referente à solicitações
        //--------------------------------------------

        // Função para carregar responsáveis na solicitação
        function carregarResponsaveisSolicitacao(projetoId) {
            $.ajax({
                url: `<?= site_url('projetos/responsaveis/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const $container = $('#responsaveisAtuaisListSolicitacao');
                    $container.empty();

                    if (response.success && response.data) {
                        if (response.data.length > 0) {
                            response.data.forEach(user => {
                                $container.append(`
                            <div class="list-group-item d-flex justify-content-between align-items-center"
                                 data-user-id="${user.usuario_id}">
                                <div>
                                    <strong>${user.name}</strong>
                                    ${user.email ? `<div class="text-muted small">${user.email}</div>` : ''}
                                </div>
                                <button class="btn btn-sm btn-outline-danger btn-remover-responsavel-solicitacao"
                                        title="Remover responsável">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </div>
                        `);
                            });
                            $('#contadorResponsaveisAtuaisSolicitacao').text(response.data.length);
                        } else {
                            $container.html('<div class="text-center py-3 text-muted">Nenhum responsável</div>');
                            $('#contadorResponsaveisAtuaisSolicitacao').text('0');
                        }
                    } else {
                        $container.html('<div class="text-center py-3 text-danger">Erro ao carregar responsáveis</div>');
                    }
                },
                error: function() {
                    $('#responsaveisAtuaisListSolicitacao').html('<div class="text-center py-3 text-danger">Erro ao carregar responsáveis</div>');
                }
            });
        }

        // Função para carregar usuários disponíveis na solicitação
        function carregarUsuariosDisponiveisSolicitacao(projetoId) {
            $.ajax({
                url: `<?= site_url('projetos/usuarios-disponiveis/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const $container = $('#usuariosDisponiveisListSolicitacao');
                    $container.empty();

                    if (response.success && response.data) {
                        if (response.data.length > 0) {
                            response.data.forEach(user => {
                                $container.append(`
                            <div class="list-group-item d-flex justify-content-between align-items-center"
                                 data-user-id="${user.id}">
                                <div>
                                    <strong>${user.name}</strong>
                                    ${user.email ? `<div class="text-muted small">${user.email}</div>` : ''}
                                </div>
                                <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel-solicitacao"
                                        title="Adicionar como responsável">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        `);
                            });
                        } else {
                            $container.html('<div class="text-center py-3 text-muted">Todos os usuários já são responsáveis</div>');
                        }
                        $('#contadorUsuariosDisponiveisSolicitacao').text(response.data.length);
                    } else {
                        $container.html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                    }
                },
                error: function() {
                    $('#usuariosDisponiveisListSolicitacao').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
            });
        }

        // Eventos para adicionar/remover responsáveis na solicitação
        $(document).on('click', '.btn-adicionar-responsavel-solicitacao', function() {
            const $item = $(this).closest('.list-group-item');
            const userId = $item.data('user-id');
            const userName = $item.find('strong').text();
            const userEmail = $item.find('.text-muted').text() || '';

            // Adiciona à lista de responsáveis
            $('#responsaveisAtuaisListSolicitacao').append(`
        <div class="list-group-item d-flex justify-content-between align-items-center"
             data-user-id="${userId}">
            <div>
                <strong>${userName}</strong>
                ${userEmail ? `<div class="text-muted small">${userEmail}</div>` : ''}
            </div>
            <button class="btn btn-sm btn-outline-danger btn-remover-responsavel-solicitacao"
                    title="Remover responsável">
                <i class="fas fa-user-minus"></i>
            </button>
        </div>
    `);

            // Remove da lista de disponíveis
            $item.remove();

            // Atualiza contadores
            $('#contadorResponsaveisAtuaisSolicitacao').text($('#responsaveisAtuaisListSolicitacao .list-group-item').length);
            $('#contadorUsuariosDisponiveisSolicitacao').text($('#usuariosDisponiveisListSolicitacao .list-group-item').length);
        });

        $(document).on('click', '.btn-remover-responsavel-solicitacao', function() {
            const $item = $(this).closest('.list-group-item');
            const userId = $item.data('user-id');
            const userName = $item.find('strong').text();
            const userEmail = $item.find('.text-muted').text() || '';

            // Adiciona de volta à lista de disponíveis
            $('#usuariosDisponiveisListSolicitacao').append(`
        <div class="list-group-item d-flex justify-content-between align-items-center"
             data-user-id="${userId}">
            <div>
                <strong>${userName}</strong>
                ${userEmail ? `<div class="text-muted small">${userEmail}</div>` : ''}
            </div>
            <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel-solicitacao"
                    title="Adicionar como responsável">
                <i class="fas fa-user-plus"></i>
            </button>
        </div>
    `);

            // Remove da lista de responsáveis
            $item.remove();

            // Atualiza contadores
            $('#contadorUsuariosDisponiveisSolicitacao').text($('#usuariosDisponiveisListSolicitacao .list-group-item').length);
            $('#contadorResponsaveisAtuaisSolicitacao').text($('#responsaveisAtuaisListSolicitacao .list-group-item').length);
        });

        // Busca de usuários na solicitação
        $('#buscaUsuarioResponsavelSolicitacao').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('#usuariosDisponiveisListSolicitacao .list-group-item').each(function() {
                const $item = $(this);
                const text = $item.text().toLowerCase();
                $item.toggle(text.includes(searchTerm));
            });
        });

        // Limpar busca na solicitação
        $('#btnLimparBuscaResponsaveisSolicitacao').click(function() {
            $('#buscaUsuarioResponsavelSolicitacao').val('');
            $('#usuariosDisponiveisListSolicitacao .list-group-item').show();
        });

        // Adicionar indicador na solicitação
        $('#btnAdicionarIndicadorProjetoSolicitacao').click(function() {
            const conteudo = $('#solicitarEdicaoIndicadorConteudo').val().trim();
            const descricao = $('#solicitarEdicaoIndicadorDescricao').val().trim();

            if (!conteudo) {
                showErrorAlert('Preencha o conteúdo do indicador');
                return;
            }

            const novoIndicador = {
                conteudo: conteudo,
                descricao: descricao
            };

            $('#indicadoresAtuaisListSolicitacao .list-group').append(renderizarIndicador(novoIndicador));

            // Limpar campos
            $('#solicitarEdicaoIndicadorConteudo, #solicitarEdicaoIndicadorDescricao').val('');

            // Atualizar contador
            const count = $('#indicadoresAtuaisListSolicitacao .list-group-item').length;
            $('#contadorIndicadoresAtuaisSolicitacao').text(count);
        });

        // Remover indicador na solicitação
        $(document).on('click', '.btn-remover-indicador-solicitacao', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(this).closest('.list-group-item');
            const id = $item.data('id');

            if (id) {
                // Apenas move para a lista de remoção (não remove do banco ainda)
                $item.removeClass('list-group-item-danger')
                    .addClass('list-group-item-warning')
                    .find('.btn-remover-indicador-solicitacao')
                    .removeClass('btn-outline-danger')
                    .addClass('btn-outline-secondary')
                    .html('<i class="fas fa-undo"></i>')
                    .attr('title', 'Desfazer remoção');

                // Move o item para a lista de remoção
                $('#indicadoresRemoverListSolicitacao .list-group').append($item);

                // Atualiza contadores
                $('#contadorIndicadoresAtuaisSolicitacao').text($('#indicadoresAtuaisListSolicitacao .list-group-item').length);
                $('#contadorIndicadoresRemoverSolicitacao').text($('#indicadoresRemoverListSolicitacao .list-group-item').length);
            } else {
                // Se não tem ID, é nova - pode remover imediatamente
                $item.remove();
                $('#contadorIndicadoresAtuaisSolicitacao').text($('#indicadoresAtuaisListSolicitacao .list-group-item').length);
            }
        });

        // Desfazer remoção de indicador na solicitação
        $(document).on('click', '.btn-remover-indicador-solicitacao.btn-outline-secondary', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $item = $(this).closest('.list-group-item');
            $item.removeClass('list-group-item-warning')
                .find('.btn-remover-indicador-solicitacao')
                .removeClass('btn-outline-secondary')
                .addClass('btn-outline-danger')
                .html('<i class="fas fa-trash-alt"></i>')
                .attr('title', 'Remover indicador');

            // Move de volta para a lista atual
            $('#indicadoresAtuaisListSolicitacao .list-group').append($item);

            // Atualiza contadores
            $('#contadorIndicadoresAtuaisSolicitacao').text($('#indicadoresAtuaisListSolicitacao .list-group-item').length);
            $('#contadorIndicadoresRemoverSolicitacao').text($('#indicadoresRemoverListSolicitacao .list-group-item').length);
        });

        // Evento quando o modal de solicitação de edição é aberto
        $('#solicitarEdicaoModal').on('shown.bs.modal', function() {
            const projetoId = $('#solicitarEdicaoId').val();
            if (!projetoId) return;

            // Carregar responsáveis
            carregarResponsaveisSolicitacao(projetoId);
            carregarUsuariosDisponiveisSolicitacao(projetoId);

            // Carregar evidências
            carregarEvidencias(
                projetoId,
                '#evidenciasProjetoAtuaisListSolicitacao .list-group',
                '#contadorEvidenciasProjetoAtuaisSolicitacao',
                true
            );

            // Carregar indicadores
            carregarIndicadores(
                projetoId,
                '#indicadoresAtuaisListSolicitacao .list-group',
                '#contadorIndicadoresAtuaisSolicitacao',
                true
            );

            // Limpar listas de remoção
            $('#evidenciasProjetoRemoverListSolicitacao .list-group').empty();
            $('#contadorEvidenciasProjetoRemoverSolicitacao').text('0');

            $('#indicadoresRemoverListSolicitacao .list-group').empty();
            $('#contadorIndicadoresRemoverSolicitacao').text('0');
        });

        // Função para carregar indicadores
        function carregarIndicadores(projetoId, containerSelector, contadorSelector, isSolicitacao = false) {
            $.ajax({
                url: `<?= site_url('projetos/listar-indicadores/') ?>${projetoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const $container = $(containerSelector);
                    $container.empty();

                    if (response.success && response.data) {
                        response.data.forEach(function(ind) {
                            $container.append(renderizarIndicador({
                                id: ind.id,
                                conteudo: ind.conteudo,
                                descricao: ind.descricao
                            }, isSolicitacao));
                        });
                        $(contadorSelector).text(response.data.length);
                    } else {
                        $container.html('<div class="text-center py-3 text-muted">Nenhum indicador encontrado</div>');
                    }
                },
                error: function() {
                    $(containerSelector).html('<div class="text-center py-3 text-danger">Erro ao carregar indicadores</div>');
                }
            });
        }

        // Função para renderizar indicador (com classe específica para solicitação)
        function renderizarIndicador(indicador, isSolicitacao = false) {
            const btnClass = isSolicitacao ? 'btn-remover-indicador-solicitacao' : 'btn-remover-indicador';

            return `
<div class="list-group-item ${!indicador.id ? 'indicador-nova' : ''}"
     data-id="${indicador.id || ''}"
     data-conteudo="${indicador.conteudo}"
     data-descricao="${indicador.descricao || ''}">
    <div class="position-relative">
        <button class="btn btn-sm btn-outline-danger ${btnClass} position-absolute"
                style="top: 0; right: 0;"
                title="Remover indicador">
            <i class="fas fa-trash-alt"></i>
        </button>

        <div style="padding-right: 30px;">
            <div class="mb-2">
                <strong>Indicador:</strong>
                <p class="mb-1">${indicador.conteudo}</p>
            </div>

            <div>
                <strong>Descrição:</strong>
                <p class="mb-1">${indicador.descricao || 'Sem descrição'}</p>
            </div>
        </div>
    </div>
</div>`;
        }

        $(document).on('click', '.btn-primary[title="Solicitar Edição"]', function() {
            var projetoCompletoId = $(this).data('id');
            var projetoId = projetoCompletoId.split('-')[0];

            $.ajax({
                url: '<?= site_url("projetos/dados-projeto/") ?>' + projetoId,
                type: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Preencher modal de solicitação de edição
                        $('#solicitarEdicaoId').val(response.data.id);
                        $('#solicitarEdicaoIdentificador').val(response.data.identificador);
                        $('#solicitarEdicaoNome').val(response.data.nome);
                        $('#solicitarEdicaoDescricao').val(response.data.descricao);
                        $('#solicitarEdicaoMetas').val(response.data.metas || '');
                        $('#solicitarEdicaoVinculado').val(response.data.projeto_vinculado);
                        $('#solicitarEdicaoEixo').val(response.data.id_eixo);
                        $('#solicitarEdicaoPriorizacao').val(response.data.priorizacao_gab);
                        $('#solicitarEdicaoStatus').val(response.data.status);

                        // Armazenar dados originais para comparação
                        window.projetoOriginalData = response.data;

                        // Abrir modal
                        $('#solicitarEdicaoModal').modal('show');
                    } else {
                        showErrorAlert(response.message || 'Erro ao carregar dados do projeto');
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert('Falha ao carregar projeto: ' + error);
                }
            });
        });

        function getResponsaveisParaAdicionarSolicitacao() {
            // Obter IDs dos responsáveis atuais no modal
            const responsaveisAtuaisIds = $('#responsaveisAtuaisListSolicitacao .list-group-item')
                .map(function() {
                    const id = $(this).data('user-id');
                    return id ? parseInt(id) : null;
                }).get().filter(Boolean);

            // Obter IDs dos responsáveis originais
            const responsaveisOriginais = window.projetoOriginalData.responsaveis || [];
            const responsaveisOriginaisIds = responsaveisOriginais.map(r => {
                return r.usuario_id || r.id || r;
            }).filter(Boolean).map(id => parseInt(id));

            // Adicionar apenas os que não estão na lista original
            return responsaveisAtuaisIds.filter(id =>
                !responsaveisOriginaisIds.includes(id)
            );
        }

        function getResponsaveisParaRemoverSolicitacao() {
            try {
                // Obter IDs dos responsáveis atuais no modal
                const responsaveisAtuaisIds = $('#responsaveisAtuaisListSolicitacao .list-group-item')
                    .map(function() {
                        const id = $(this).data('user-id');
                        return id ? parseInt(id) : null;
                    }).get().filter(Boolean);

                // Obter IDs dos responsáveis originais
                const responsaveisOriginais = window.projetoOriginalData.responsaveis || [];
                const responsaveisOriginaisIds = responsaveisOriginais.map(r => {
                    return r.usuario_id || r.id || r;
                }).filter(Boolean).map(id => parseInt(id));

                // Só remover os que não estão na lista atual
                return responsaveisOriginaisIds.filter(id =>
                    !responsaveisAtuaisIds.includes(id)
                );
            } catch (e) {
                console.error('Erro ao calcular responsáveis para remover:', e);
                return [];
            }
        }

        // Funções auxiliares para coletar dados do formulário
        function getResponsaveisAtuaisSolicitacao() {
            const responsaveis = [];
            $('#responsaveisAtuaisListSolicitacao .list-group-item').each(function() {
                const id = $(this).data('user-id');
                if (id) responsaveis.push(parseInt(id));
            });
            return responsaveis;
        }

        function getEvidenciasParaAdicionarSolicitacao() {
            const evidencias = [];
            $('#evidenciasProjetoAtuaisListSolicitacao .list-group-item').each(function() {
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

        function getEvidenciasParaRemoverSolicitacao() {
            const ids = [];
            $('#evidenciasProjetoRemoverListSolicitacao .list-group-item').each(function() {
                const id = $(this).data('id');
                if (id) ids.push(id);
            });
            return ids;
        }

        function getIndicadoresParaAdicionarSolicitacao() {
            const indicadores = [];
            $('#indicadoresAtuaisListSolicitacao .list-group-item').each(function() {
                if (!$(this).data('id')) { // Se não tem ID, é novo
                    indicadores.push({
                        conteudo: $(this).data('conteudo'),
                        descricao: $(this).data('descricao') || 'Sem descrição'
                    });
                }
            });
            return indicadores;
        }

        function getIndicadoresParaRemoverSolicitacao() {
            const ids = [];
            $('#indicadoresRemoverListSolicitacao .list-group-item').each(function() {
                const id = $(this).data('id');
                if (id) ids.push(id);
            });
            return ids;
        }



    });
</script>