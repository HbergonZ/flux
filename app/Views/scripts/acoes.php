<!-- jQuery PRIMEIRO - versão mais recente -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Bootstrap -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script> -->

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Verde mais claro para "Finalizado com atraso" */
    .badge-verde-claro {
        background-color: #d4edda;
        /* Tom pastel de verde */
        color: #155724;
        /* Texto verde escuro */
        border: 1px solid #c3e6cb;
        /* Borda verde mais clara */
    }

    /* Verde padrão do Bootstrap (pode ser redefinido se quiser) */
    .badge-verde-normal {
        background-color: #28a745;
        color: white;
    }
</style>

<script>
    $(document).ready(function() {
        // Variáveis globais
        let dataTable;
        let acessoDireto = <?= isset($acessoDireto) && $acessoDireto ? 'true' : 'false' ?>;
        let etapaNome = '<?= isset($etapa) ? $etapa["nome"] : "" ?>';
        let formOriginalData = {};

        // Configuração do DataTables
        function initializeDataTable() {
            return $('#dataTable').DataTable({
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                language: {
                    "sEmptyTable": "Nenhum registro encontrado",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sInfoThousands": ".",
                    "sLengthMenu": "_MENU_ resultados por página",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing": "Processando...",
                    "sZeroRecords": "Nenhum registro encontrado",
                    "sSearch": "Pesquisar",
                    "oPaginate": {
                        "sNext": "Próximo",
                        "sPrevious": "Anterior",
                        "sFirst": "Primeiro",
                        "sLast": "Último"
                    },
                    "oAria": {
                        "sSortAscending": ": Ordenar colunas de forma ascendente",
                        "sSortDescending": ": Ordenar colunas de forma descendente"
                    }
                },
                searching: false,
                responsive: true,
                autoWidth: false,
                lengthMenu: [5, 10, 25, 50, 100],
                pageLength: 10,
                columns: [{
                        "data": "ordem",
                        "visible": false,
                        "searchable": false
                    },
                    {
                        "data": "nome",
                        "className": "text-wrap"
                    },
                    ...(!acessoDireto ? [{
                        "data": "etapa",
                        "defaultContent": etapaNome
                    }] : []),
                    {
                        "data": "responsaveis",
                        "className": "text-wrap",
                        "render": function(data, type, row) {
                            return data ? data : '';
                        }
                    },
                    {
                        "data": "entrega_estimada",
                        "className": "text-center align-middle",
                        "render": function(data) {
                            return data ? formatDate(data) : '';
                        }
                    },
                    {
                        "data": "data_inicio",
                        "className": "text-center align-middle",
                        "render": function(data) {
                            return data ? formatDate(data) : '';
                        }
                    },
                    {
                        "data": "data_fim",
                        "className": "text-center align-middle",
                        "render": function(data) {
                            return data ? formatDate(data) : '';
                        }
                    },
                    {
                        "data": "status",
                        "className": "text-center align-middle",
                        "render": function(data) {
                            if (!data) data = 'Não iniciado';
                            const badgeClass = {
                                'Finalizado': 'badge-success',
                                'Finalizado com atraso': 'badge-verde-claro',
                                'Em andamento': 'badge-warning',
                                'Paralisado': 'badge-dark',
                                'Atrasado': 'badge-danger',
                                'Não iniciado': 'badge-primary'
                            } [data] || 'badge-secondary';

                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    {
                        "data": null,
                        "className": "text-center align-middle",
                        "orderable": false,
                        "render": function(data, type, row) {
                            const id = row.id + '-' + row.nome.toLowerCase().replace(/\s+/g, '-');
                            const isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;

                            let buttons = '<div class="d-inline-flex">';

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

                            buttons += '</div>';
                            return buttons;
                        }
                    }
                ],
                "order": [
                    [0, 'asc']
                ],
                order: [
                    [0, 'asc']
                ],
                ajax: {
                    url: `<?= site_url("acoes/get-acoes/{$idOrigem}/{$tipoOrigem}") ?>`,
                    dataSrc: 'data'
                }
            });
        }

        // Inicializa a tabela
        dataTable = initializeDataTable();

        // Configuração do AJAX
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        });

        // Função para calcular a próxima ordem
        function calcularProximaOrdem() {
            $.ajax({
                url: `<?= site_url("acoes/proxima-ordem/$idOrigem/$tipoOrigem") ?>`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#acaoOrdem').val(response.proximaOrdem);
                        $('#solicitarInclusaoOrdem').val(response.proximaOrdem);
                    } else {
                        console.error('Erro ao calcular próxima ordem:', response.message);
                        // Fallback: calcular no cliente
                        var maxOrdem = 0;
                        dataTable.rows().every(function() {
                            var ordem = parseInt(this.data().ordem) || 0;
                            if (ordem > maxOrdem) {
                                maxOrdem = ordem;
                            }
                        });
                        $('#acaoOrdem').val(maxOrdem + 1);
                        $('#solicitarInclusaoOrdem').val(maxOrdem + 1);
                    }
                },
                error: function() {
                    console.error('Falha ao calcular próxima ordem via AJAX');
                    // Fallback: calcular no cliente
                    var maxOrdem = 0;
                    dataTable.rows().every(function() {
                        var ordem = parseInt(this.data().ordem) || 0;
                        if (ordem > maxOrdem) {
                            maxOrdem = ordem;
                        }
                    });
                    $('#acaoOrdem').val(maxOrdem + 1);
                    $('#solicitarInclusaoOrdem').val(maxOrdem + 1);
                }
            });
        }

        // Carregar próxima ordem ao abrir o modal de adição
        $('#addAcaoModal').on('show.bs.modal', function() {
            calcularProximaOrdem();
        });

        // Carregar próxima ordem ao abrir o modal de solicitação de inclusão
        $('#solicitarInclusaoModal').on('show.bs.modal', function() {
            calcularProximaOrdem();
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

        // Função para aplicar filtros
        function applyFilters() {
            const formData = $('#formFiltros').serializeArray();
            const hasFilters = formData.some(item => item.value !== '' && item.name !== 'csrf_test_name');

            if (!hasFilters) {
                dataTable.ajax.url(`<?= site_url("acoes/get-acoes/{$idOrigem}/{$tipoOrigem}") ?>`).load();
                return;
            }

            $.ajax({
                type: "POST",
                url: `<?= site_url("acoes/filtrar/{$idOrigem}/{$tipoOrigem}") ?>`,
                data: $('#formFiltros').serialize(),
                dataType: "json",
                beforeSend: function() {
                    $('#dataTable').css('opacity', '0.5');
                },
                success: function(response) {
                    if (response.success) {
                        dataTable.clear();
                        dataTable.rows.add(response.data);
                        dataTable.draw();
                    } else {
                        showErrorAlert(response.message || 'Erro ao filtrar ações');
                    }
                },
                error: function() {
                    showErrorAlert('Erro ao filtrar ações');
                },
                complete: function() {
                    $('#dataTable').css('opacity', '1');
                }
            });
        }

        // Atualizar tabela com dados filtrados
        function updateTableWithFilteredData(acoes) {
            // Destruir a tabela existente
            if ($.fn.DataTable.isDataTable('#dataTable')) {
                dataTable.destroy();
            }

            // Limpar o corpo da tabela
            $('#dataTable tbody').empty();

            if (acoes.length === 0) {
                const colCount = acessoDireto ? 9 : 10;
                $('#dataTable tbody').append(`
            <tr>
                <td colspan="${colCount}" class="text-center">Nenhuma ação encontrada com os filtros aplicados</td>
            </tr>
        `);
            } else {
                // Reconstruir a tabela com os dados filtrados
                dataTable = initializeDataTable(); // Usa a mesma função de inicialização

                // Atualizar os dados da tabela
                dataTable.clear();
                dataTable.rows.add(acoes.map(acao => ({
                    id: acao.id,
                    nome: acao.nome,
                    etapa: !acessoDireto ? (etapaNome || '') : '',
                    responsavel: acao.responsavel || '',
                    entrega_estimada: acao.entrega_estimada || null,
                    data_inicio: acao.data_inicio || null,
                    data_fim: acao.data_fim || null,
                    status: acao.status || 'Não iniciado',
                    ordem: acao.ordem || 0
                }))).draw();
            }
        }

        // Event listener para a troca de ordens no modal
        $(document).on('change', '.ordem-select', function() {
            const selectAtual = this;
            const novaOrdem = parseInt(selectAtual.value);
            const idAtual = selectAtual.name.match(/\[(.*?)\]/)[1];
            const ordemOriginal = parseInt($(selectAtual).data('original'));

            // Se a nova ordem for igual à original, não faz nada
            if (novaOrdem === ordemOriginal) return;

            // Encontrar o select que tinha a nova ordem
            let selectAlvo = null;
            $('.ordem-select').each(function() {
                if (this !== selectAtual && parseInt(this.value) === novaOrdem) {
                    selectAlvo = this;
                    return false; // sai do loop
                }
            });

            // Se encontrou um select com a ordem que queremos trocar
            if (selectAlvo) {
                // Troca a ordem do select alvo para a ordem original do select atual
                $(selectAlvo).val(ordemOriginal).data('original', ordemOriginal);
            }

            // Atualiza o data-original do select atual para a nova ordem
            $(selectAtual).data('original', novaOrdem);
        });

        // Inicialização quando o modal é aberto
        $('#ordenarAcoesModal').on('show.bs.modal', function() {
            const modal = $(this);
            modal.find('.modal-body').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Carregando ações...</p></div>');

            $.ajax({
                url: `<?= site_url("acoes/carregar-para-ordenacao/{$idOrigem}/{$tipoOrigem}") ?>`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Construir a tabela completa
                        const tableHtml = `
                    <div class="alert alert-info mb-3">
                        Selecione a nova posição para cada ação (a posição atual permanecerá visível como referência)
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ação</th>
                                <th style="width: 120px;">Ordem atual</th>
                                <th style="width: 120px;">Nova ordem</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${response.html}
                        </tbody>
                    </table>`;

                        modal.find('.modal-body').html(tableHtml);

                        // Configuração original quando o modal é aberto
                        $('.ordem-select').each(function() {
                            $(this).data('original', $(this).val());
                        });
                    } else {
                        modal.find('.modal-body').html('<div class="alert alert-danger">Erro ao carregar ações</div>');
                    }
                },
                error: function() {
                    modal.find('.modal-body').html('<div class="alert alert-danger">Erro ao carregar ações</div>');
                }
            });
        });

        // Enviar formulário de ordenação
        $('#formOrdenarAcoes').submit(function(e) {
            e.preventDefault();

            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#ordenarAcoesModal').modal('hide');
                        showSuccessAlert(response.message || 'Ordem atualizada com sucesso!');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showErrorAlert(response.message || 'Ocorreu um erro ao atualizar a ordem.');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    showErrorAlert('Erro na comunicação com o servidor.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });
        // Função para validar datas
        function isValidDate(dateString) {
            if (!dateString || dateString === '0000-00-00') return false;
            if (!/^\d{4}-\d{2}-\d{2}$/.test(dateString)) return false;
            const date = new Date(dateString);
            return !isNaN(date.getTime()) && date.toISOString().slice(0, 10) === dateString;
        }

        // Função para formatar data
        function formatDate(dateString) {
            if (!dateString || !isValidDate(dateString)) return '';

            // Ajuste para garantir que a data seja tratada corretamente
            const date = new Date(dateString);
            // Ajuste para o fuso horário local
            const adjustedDate = new Date(date.getTime() + date.getTimezoneOffset() * 60000);

            const day = String(adjustedDate.getDate()).padStart(2, '0');
            const month = String(adjustedDate.getMonth() + 1).padStart(2, '0');
            const year = adjustedDate.getFullYear();
            return `${day}/${month}/${year}`;
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


        //------------------------------------------------------------
        // SOLICITAR EXCLUSÃO
        //------------------------------------------------------------

        // Solicitar exclusão de ação - Abrir modal (para não-admins)
        $(document).on('click', '.btn-danger[title="Solicitar Exclusão"]', function() {
            var acaoId = $(this).data('id').split('-')[0];
            var acaoName = $(this).closest('tr').find('td:nth-child(2)').text();

            loadAcaoForRequest(acaoId, acaoName, '#solicitarExclusaoModal');
        });

        // Função para carregar dados para solicitação
        function loadAcaoForRequest(acaoId, acaoName, modalId) {
            const isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
            const endpoint = isAdmin ? 'editar' : 'dados-acao';

            $.ajax({
                url: `<?= site_url('acoes/') ?>${endpoint}/${acaoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        const acao = response.data;
                        const dadosAtuais = `Nome: ${acao.nome}\nResponsável: ${acao.responsavel}\nEquipe: ${acao.equipe}\nStatus: ${acao.status}\nEntrega Estimada: ${acao.entrega_estimada}\nData Início: ${acao.data_inicio}\nData Fim: ${acao.data_fim}\nEtapa: ${acao.id_etapa}\nProjeto: ${acao.id_projeto}`;

                        $('#solicitarExclusaoId').val(acao.id);
                        $('#acaoNameToRequestDelete').text(acaoName);
                        $('#solicitarExclusaoDadosAtuais').val(dadosAtuais);
                        $('#solicitarExclusaoModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar ação");
                    }
                },
                error: function() {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        }

        // Enviar solicitação de exclusão
        $('#formSolicitarExclusao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarExclusaoModal', 'Solicitação de exclusão enviada com sucesso!');
        });


        //------------------------------------------------------------
        // SOLICITAÇÕES
        //------------------------------------------------------------

        // Função genérica para enviar formulários
        function submitForm(form, modalId, successMessage = null) {
            // Verificar se já está processando
            if (form.data('submitting')) {
                return;
            }
            form.data('submitting', true);

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
                        if (response.message) {
                            showSuccessAlert(response.message);
                        } else if (successMessage) {
                            showSuccessAlert(successMessage);
                        }

                        if (modalId) {
                            // Limpar o formulário antes de fechar
                            form.trigger('reset');

                            // Limpar elementos específicos
                            $(modalId).find('.list-group').empty();
                            $(modalId).find('.badge-pill').text('0');

                            // Limpar variáveis globais
                            evidenciasAdicionadasAcao = [];
                            evidenciasRemovidasAcao = [];
                            responsaveisSelecionadosSolicitacao = [];

                            // Fechar o modal
                            $(modalId).modal('hide');
                        }

                        if (!modalId || (modalId !== '#solicitarEdicaoModal' &&
                                modalId !== '#solicitarExclusaoModal' &&
                                modalId !== '#solicitarInclusaoModal')) {
                            setTimeout(() => location.reload(), 1500);
                        }
                    } else {
                        showErrorAlert(response.message || 'Ocorreu um erro durante a operação.');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    showErrorAlert('Erro na comunicação com o servidor.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    form.data('submitting', false); // Resetar flag
                }
            });
        }


        //------------------------------------------------------------
        // SOLICITAR INCLUSÃO
        //------------------------------------------------------------

        // variáveis globais no início do script
        let responsaveisSelecionadosSolicitacao = [];
        let usuariosDisponiveisSolicitacao = [];

        // Função para carregar usuários disponíveis para solicitação
        function carregarUsuariosDisponiveisSolicitacao() {
            $.ajax({
                url: '<?= site_url("acoes/buscar-usuarios") ?>',
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#usuariosDisponiveisSolicitacao').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>');
                },
                success: function(response) {
                    if (response.success && response.data && response.data.length > 0) {
                        usuariosDisponiveisSolicitacao = response.data;
                        atualizarListaUsuariosDisponiveisSolicitacao();
                        $('#contadorUsuariosSolicitacao').text(response.data.length);
                    } else {
                        $('#usuariosDisponiveisSolicitacao').html('<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>');
                        $('#contadorUsuariosSolicitacao').text('0');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao carregar usuários:', error);
                    $('#usuariosDisponiveisSolicitacao').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                    $('#contadorUsuariosSolicitacao').text('0');
                }
            });
        }

        // Função para atualizar a lista de usuários disponíveis
        function atualizarListaUsuariosDisponiveisSolicitacao() {
            const lista = $('#usuariosDisponiveisSolicitacao');
            lista.empty();

            if (usuariosDisponiveisSolicitacao.length === 0) {
                lista.html('<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>');
                return;
            }

            let html = '';
            usuariosDisponiveisSolicitacao.forEach(usuario => {
                // Verifica se o usuário já está na lista de selecionados
                const jaSelecionado = responsaveisSelecionadosSolicitacao.some(r => r.id === usuario.id);

                if (!jaSelecionado) {
                    html += `
                <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                    <div>
                        <span class="font-weight-bold">${usuario.name}</span>
                        <small class="d-block text-muted">${usuario.email}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel-solicitacao" data-id="${usuario.id}">
                        <i class="fas fa-user-plus"></i>
                    </button>
                </div>
            `;
                }
            });

            if (html === '') {
                html = '<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>';
            }

            lista.html(html);
        }

        // Atualize a função que atualiza os responsáveis selecionados
        function atualizarResponsaveisSelecionadosSolicitacao() {
            const lista = $('#responsaveisSelecionadosSolicitacao');
            lista.empty();

            if (responsaveisSelecionadosSolicitacao.length === 0) {
                lista.html('<div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>');
                $('#contadorResponsaveisSolicitacao').text('0');
                $('#responsaveisSolicitacao').val(JSON.stringify({
                    responsaveis: {
                        adicionar: []
                    }
                }));
                return;
            }

            let html = '';
            const ids = [];

            responsaveisSelecionadosSolicitacao.forEach(usuario => {
                html += `
        <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
            <div>
                <span class="font-weight-bold">${usuario.name}</span>
                <small class="d-block text-muted">${usuario.email}</small>
            </div>
            <button class="btn btn-sm btn-outline-danger btn-remover-responsavel-solicitacao" data-id="${usuario.id}">
                <i class="fas fa-user-minus"></i>
            </button>
        </div>
    `;
                ids.push(usuario.id);
            });

            lista.html(html);
            $('#contadorResponsaveisSolicitacao').text(responsaveisSelecionadosSolicitacao.length);

            // Formata os dados no formato esperado
            const dadosResponsaveis = {
                responsaveis: {
                    adicionar: ids
                }
            };

            $('#responsaveisSolicitacao').val(JSON.stringify(dadosResponsaveis));
        }

        // Evento para adicionar responsável na solicitação
        $(document).on('click', '.btn-adicionar-responsavel-solicitacao', function() {
            const usuarioId = $(this).data('id');
            const usuario = usuariosDisponiveisSolicitacao.find(u => u.id == usuarioId);

            if (usuario && !responsaveisSelecionadosSolicitacao.some(u => u.id == usuarioId)) {
                responsaveisSelecionadosSolicitacao.push(usuario);
                atualizarResponsaveisSelecionadosSolicitacao();
                atualizarListaUsuariosDisponiveisSolicitacao();
            }
        });

        // Evento para remover responsável na solicitação
        $(document).on('click', '.btn-remover-responsavel-solicitacao', function() {
            const usuarioId = $(this).data('id');
            responsaveisSelecionadosSolicitacao = responsaveisSelecionadosSolicitacao.filter(u => u.id != usuarioId);
            atualizarResponsaveisSelecionadosSolicitacao();
            atualizarListaUsuariosDisponiveisSolicitacao();
        });

        // Buscar usuários ao digitar na solicitação
        $('#buscarUsuarioSolicitacao').on('input', function() {
            const termo = $(this).val().toLowerCase();
            if (termo === '') {
                atualizarListaUsuariosDisponiveisSolicitacao();
                return;
            }

            const resultados = usuariosDisponiveisSolicitacao.filter(usuario =>
                usuario.name.toLowerCase().includes(termo) ||
                usuario.email.toLowerCase().includes(termo)
            );

            const lista = $('#usuariosDisponiveisSolicitacao');
            lista.empty();

            if (resultados.length === 0) {
                lista.html('<div class="text-center py-3 text-muted">Nenhum resultado encontrado</div>');
                return;
            }

            let html = '';
            resultados.forEach(usuario => {
                const jaSelecionado = responsaveisSelecionadosSolicitacao.some(r => r.id === usuario.id);

                if (!jaSelecionado) {
                    html += `
                <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                    <div>
                        <span class="font-weight-bold">${usuario.name}</span>
                        <small class="d-block text-muted">${usuario.email}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel-solicitacao" data-id="${usuario.id}">
                        <i class="fas fa-user-plus"></i>
                    </button>
                </div>
            `;
                }
            });

            lista.html(html || '<div class="text-center py-3 text-muted">Nenhum resultado disponível</div>');
        });

        // Carregar usuários quando o modal de solicitação é aberto
        $('#solicitarInclusaoModal').on('show.bs.modal', function() {
            responsaveisSelecionadosSolicitacao = [];
            carregarUsuariosDisponiveisSolicitacao();
            atualizarResponsaveisSelecionadosSolicitacao();
        });

        // Enviar solicitação de inclusão
        $('#formSolicitarInclusao').submit(function(e) {
            e.preventDefault();

            // Coletar IDs dos responsáveis selecionados
            const responsaveisIds = responsaveisSelecionadosSolicitacao.map(u => u.id);

            // Atualizar o campo hidden com os dados no formato correto
            $('#responsaveisSolicitacao').val(JSON.stringify({
                responsaveis: {
                    adicionar: responsaveisIds
                }
            }));

            submitForm($(this), '#solicitarInclusaoModal', 'Solicitação de inclusão enviada com sucesso!');
        });

        //------------------------------------------------------------
        // SOLICITAR EDIÇÃO
        //------------------------------------------------------------

        // Variáveis globais para controle

        let evidenciasAdicionadasAcao = [];
        let evidenciasRemovidasAcao = [];

        // Função auxiliar para formatar data para input date
        function formatDateForInput(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        }

        // Solicitar edição de ação - Abrir modal (para não-admins)
        $(document).on('click', '.btn-primary[title="Solicitar Edição"]', function() {
            var acaoId = $(this).data('id').split('-')[0];
            $.ajax({
                url: `<?= site_url('acoes/dados-acao/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        carregarDadosParaSolicitacaoEdicao(response.data);
                        $('#solicitarEdicaoModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar ação");
                    }
                },
                error: function() {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        });

        // Função para carregar dados no modal de solicitação de edição
        function carregarDadosParaSolicitacaoEdicao(acao) {
            // Preencher campos básicos
            $('#solicitarEdicaoId').val(acao.id);
            $('#solicitarEdicaoNome').val(acao.nome);
            $('#solicitarEdicaoOrdem').val(acao.ordem);
            $('#solicitarEdicaoEntregaEstimada').val(acao.entrega_estimada ? formatDateForInput(acao.entrega_estimada) : '');
            $('#solicitarEdicaoDataInicio').val(acao.data_inicio ? formatDateForInput(acao.data_inicio) : '');
            $('#solicitarEdicaoDataFim').val(acao.data_fim ? formatDateForInput(acao.data_fim) : '');
            // Armazenar valores originais para comparação
            $('#solicitarEdicaoModal').data('original_nome', acao.nome);
            $('#solicitarEdicaoModal').data('original_ordem', acao.ordem);
            $('#solicitarEdicaoModal').data('original_entrega_estimada', acao.entrega_estimada);
            $('#solicitarEdicaoModal').data('original_data_inicio', acao.data_inicio);
            $('#solicitarEdicaoModal').data('original_data_fim', acao.data_fim);
            // Carregar responsáveis
            carregarResponsaveisParaSolicitacaoEdicao(acao.id);
            // Carregar evidências
            carregarEvidenciasParaSolicitacaoEdicao(acao.id);
            // Habilitar/desabilitar data fim conforme data início
            if (acao.data_inicio) {
                $('#solicitarEdicaoDataFim').prop('disabled', false);
            } else {
                $('#solicitarEdicaoDataFim').prop('disabled', true);
            }
        }

        // Carregar responsáveis para solicitação de edição
        function carregarResponsaveisParaSolicitacaoEdicao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/get-responsaveis/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Limpar listas
                        $('#responsaveisAtuaisEdicao').empty();
                        $('#responsaveisSelecionadosEdit').empty();
                        // Preencher responsáveis atuais
                        if (response.data && response.data.length > 0) {
                            let html = '<div class="list-group">';
                            response.data.forEach(usuario => {
                                html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                                <div>
                                    <span class="font-weight-bold">${usuario.name}</span>
                                    <small class="d-block text-muted">${usuario.email}</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger btn-remover-responsavel-solicitacao" data-id="${usuario.id}">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </div>
                        `;
                            });
                            html += '</div>';
                            $('#responsaveisAtuaisEdicao').html(html);
                            $('#contadorResponsaveisAtuaisEdicao').text(response.data.length);
                            $('#responsaveisSelecionadosEdit').html(html);
                        } else {
                            $('#responsaveisAtuaisEdicao').html('<div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>');
                            $('#responsaveisSelecionadosEdit').html('<div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>');
                            $('#contadorResponsaveisAtuaisEdicao').text('0');
                        }
                        carregarUsuariosDisponiveisParaSolicitacaoEdicao(acaoId);
                    }
                },
                error: function() {
                    showErrorAlert("Erro ao carregar responsáveis");
                }
            });
        }

        // Carregar usuários disponíveis para solicitação de edição
        function carregarUsuariosDisponiveisParaSolicitacaoEdicao(acaoId, termo = '') {
            $.ajax({
                url: `<?= site_url('acoes/get-usuarios-disponiveis/') ?>${acaoId}`,
                type: 'GET',
                data: {
                    term: termo
                },
                beforeSend: function() {
                    $('#usuariosDisponiveisEdicao').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
                },
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        if (response.data.length > 0) {
                            html = '<div class="list-group">';
                            response.data.forEach(usuario => {
                                html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                                <div>
                                    <span class="font-weight-bold">${usuario.name}</span>
                                    <small class="d-block text-muted">${usuario.email}</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel-solicitacao" data-id="${usuario.id}">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        `;
                            });
                            html += '</div>';
                        }
                        if (html === '') {
                            html = '<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>';
                        }
                        $('#usuariosDisponiveisEdicao').html(html);
                        $('#contadorUsuariosDisponiveisEdicao').text(response.data.length);
                    } else {
                        $('#usuariosDisponiveisEdicao').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                    }
                },
                error: function() {
                    $('#usuariosDisponiveisEdicao').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
            });
        }

        // Carregar evidências para solicitação de edição
        function carregarEvidenciasParaSolicitacaoEdicao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/listar-evidencias/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#evidenciasAtuaisListEdicao .list-group').empty();
                        $('#evidenciasRemoverListEdicao .list-group').empty();
                        if (response.evidencias && response.evidencias.length > 0) {
                            response.evidencias.forEach((evidencia, index) => {
                                let html = `
                        <div class="list-group-item d-flex justify-content-between align-items-start" data-id="${evidencia.id}">
                            <div class="flex-grow-1 w-100">
                                <div><strong>Evidência:</strong></div>
                                <div class="mt-1 mb-2">
                                    ${
                                        evidencia.tipo === 'texto'
                                        ? `<span>${evidencia.evidencia}</span>`
                                        : `<a href="${evidencia.evidencia}" target="_blank" class="btn btn-sm btn-outline-primary">Acessar</a>`
                                    }
                                </div>
                                <div><span class=" font-weight-bold small text-secondary">Descrição:</span><br>
                                    <span>${evidencia.descricao && evidencia.descricao.trim() ? evidencia.descricao : 'Sem descrição'}</span>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-marcar-remocao-evidencia-solicitacao" title="Remover evidência" data-id="${evidencia.id}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        `;
                                $('#evidenciasAtuaisListEdicao .list-group').append(html);
                            });
                            $('#contadorEvidenciasAtuaisEdicao').text(response.evidencias.length);
                        } else {
                            $('#evidenciasAtuaisListEdicao .list-group').html('<div class="text-center py-3 text-muted">Nenhuma evidência cadastrada</div>');
                            $('#contadorEvidenciasAtuaisEdicao').text('0');
                        }
                        $('#contadorEvidenciasRemoverEdicao').text('0');
                    }
                },
                error: function() {
                    showErrorAlert("Erro ao carregar evidências");
                }
            });
        }


        // Alternar entre tipos de evidência no modal de solicitação de edição
        $('input[name="evidencia_tipo_edicao"]').change(function() {
            if ($(this).val() === 'texto') {
                $('#solicitarEdicaoGrupoTexto').removeClass('d-none');
                $('#solicitarEdicaoGrupoLink').addClass('d-none');
            } else {
                $('#solicitarEdicaoGrupoTexto').addClass('d-none');
                $('#solicitarEdicaoGrupoLink').removeClass('d-none');
            }
        });

        // Adicionar evidência à lista no modal de solicitação de edição
        $('#btnAdicionarEvidenciaEdicao').click(function() {
            var tipo = $('input[name="evidencia_tipo_edicao"]:checked').val();
            var conteudo = tipo === 'texto' ?
                $('#solicitarEdicaoEvidenciaTexto').val().trim() :
                $('#solicitarEdicaoEvidenciaLink').val().trim();
            var descricao = $('#solicitarEdicaoEvidenciaDescricao').val().trim();
            if ((tipo === 'texto' && conteudo.length < 3) ||
                (tipo === 'link' && !isValidUrl(conteudo))) {
                showErrorAlert(tipo === 'texto' ?
                    'O texto da evidência deve ter pelo menos 3 caracteres' :
                    'Por favor, insira uma URL válida');
                return;
            }
            var novaEvidencia = {
                id: Date.now(), // ID temporário
                tipo: tipo,
                conteudo: conteudo,
                descricao: descricao,
                data: new Date().toLocaleString('pt-BR'),
                acao: 'incluir'
            };
            evidenciasAdicionadasAcao.push(novaEvidencia);
            atualizarListaEvidenciasAdicionadasSolicitacao();
            $('#solicitarEdicaoEvidenciaTexto, #solicitarEdicaoEvidenciaLink, #solicitarEdicaoEvidenciaDescricao').val('');
            $('#solicitarEdicaoGrupoTexto').removeClass('d-none');
            $('#solicitarEdicaoGrupoLink').addClass('d-none');
            $('input[name="evidencia_tipo_edicao"][value="texto"]').prop('checked', true);
        });

        // Função para atualizar a lista de evidências adicionadas na solicitação
        function atualizarListaEvidenciasAdicionadasSolicitacao() {
            // Remove items adicionados anteriormente para não acumular duplicados
            $('#evidenciasAtuaisListEdicao .nova-evidencia').remove();

            if (evidenciasAdicionadasAcao.length === 0) {
                $('#contadorEvidenciasAtuaisEdicao').text($('#evidenciasAtuaisListEdicao .list-group-item').length);
                return;
            }

            evidenciasAdicionadasAcao.forEach((evidencia, index) => {
                let html = `
            <div class="list-group-item nova-evidencia d-flex justify-content-between align-items-start" data-id="${evidencia.id}">
                <div class="flex-grow-1 w-100">
                    <div>
                        <strong>Evidência: <span class="badge badge-success ml-1">Nova</span></strong>
                    </div>
                    <div class="mt-1 mb-2">
                        ${
                            evidencia.tipo === 'texto'
                                ? `<span>${evidencia.conteudo}</span>`
                                : `<a href="${evidencia.conteudo}" target="_blank" class="btn btn-sm btn-outline-primary">Acessar</a>`
                        }
                    </div>
                    <div>
                        <span class="small text-secondary">Descrição:</span><br>
                        <span>${evidencia.descricao && evidencia.descricao.trim() ? evidencia.descricao : 'Sem descrição'}</span>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remover-evidencia-add-solicitacao" data-id="${evidencia.id}" title="Remover evidência">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
                $('#evidenciasAtuaisListEdicao .list-group').append(html);
            });

            // Atualiza o contador: atuais + novas
            let total = $('#evidenciasAtuaisListEdicao .list-group-item').length;
            $('#contadorEvidenciasAtuaisEdicao').text(total);
        }




        // Remover evidência da lista de adição na solicitação
        $(document).on('click', '.btn-remover-evidencia-add-solicitacao', function() {
            const id = $(this).data('id');
            evidenciasAdicionadasAcao = evidenciasAdicionadasAcao.filter(e => e.id != id);
            atualizarListaEvidenciasAdicionadasSolicitacao();
        });

        // Marcar evidência para remoção (move para lista removidas, deixa amarela e troca botão)
        $(document).on('click', '.btn-marcar-remocao-evidencia-solicitacao', function() {
            const $item = $(this).closest('.list-group-item');
            $item.addClass('list-group-item-warning');
            $(this)
                .removeClass('btn-outline-danger btn-marcar-remocao-evidencia-solicitacao')
                .addClass('btn-outline-secondary btn-desmarcar-remocao-evidencia-solicitacao')
                .attr('title', 'Desfazer remoção')
                .html('<i class="fas fa-undo"></i>');
            $('#evidenciasRemoverListEdicao .list-group').append($item);
            $('#contadorEvidenciasAtuaisEdicao').text($('#evidenciasAtuaisListEdicao .list-group-item').length);
            $('#contadorEvidenciasRemoverEdicao').text($('#evidenciasRemoverListEdicao .list-group-item').length);
            evidenciasRemovidasAcao.push($item.data('id'));
        });

        // Desfazer remoção (move para lista atuais, remove amarelo e volta botão)
        $(document).on('click', '.btn-desmarcar-remocao-evidencia-solicitacao', function() {
            const $item = $(this).closest('.list-group-item');
            $item.removeClass('list-group-item-warning');
            $(this)
                .removeClass('btn-outline-secondary btn-desmarcar-remocao-evidencia-solicitacao')
                .addClass('btn-outline-danger btn-marcar-remocao-evidencia-solicitacao')
                .attr('title', 'Remover evidência')
                .html('<i class="fas fa-trash-alt"></i>');
            $('#evidenciasAtuaisListEdicao .list-group').append($item);
            $('#contadorEvidenciasAtuaisEdicao').text($('#evidenciasAtuaisListEdicao .list-group-item').length);
            $('#contadorEvidenciasRemoverEdicao').text($('#evidenciasRemoverListEdicao .list-group-item').length);
            evidenciasRemovidasAcao = evidenciasRemovidasAcao.filter(e => e != $item.data('id'));
        });

        // Adicionar responsável na solicitação de edição
        $(document).on('click', '#usuariosDisponiveisEdicao .btn-adicionar-responsavel-solicitacao', function() {
            const usuarioId = $(this).data('id');
            const item = $(this).closest('.list-group-item').clone();
            item.find('button')
                .removeClass('btn-primary btn-adicionar-responsavel-solicitacao')
                .addClass('btn-outline-danger btn-remover-responsavel-solicitacao')
                .html('<i class="fas fa-user-minus"></i>');
            if ($('#responsaveisAtuaisEdicao').text().includes('Nenhum responsável selecionado')) {
                $('#responsaveisAtuaisEdicao').html('<div class="list-group"></div>');
            }
            $('#responsaveisAtuaisEdicao .list-group').append(item);
            $(this).closest('.list-group-item').remove();
            const countResponsaveis = parseInt($('#contadorResponsaveisAtuaisEdicao').text()) + 1;
            const countDisponiveis = parseInt($('#contadorUsuariosDisponiveisEdicao').text()) - 1;
            $('#contadorResponsaveisAtuaisEdicao').text(countResponsaveis);
            $('#contadorUsuariosDisponiveisEdicao').text(countDisponiveis);
            atualizarIdsResponsaveisSolicitacao();
        });

        // Remover responsável na solicitação de edição
        $(document).on('click', '#responsaveisAtuaisEdicao .btn-remover-responsavel-solicitacao', function() {
            const usuarioId = $(this).data('id');
            const item = $(this).closest('.list-group-item').clone();
            item.find('button')
                .removeClass('btn-danger btn-remover-responsavel-solicitacao')
                .addClass('btn-outline-primary btn-adicionar-responsavel-solicitacao')
                .html('<i class="fas fa-user-plus"></i>');
            $('#usuariosDisponiveisEdicao').append(item);
            $(this).closest('.list-group-item').remove();
            const countResponsaveis = parseInt($('#contadorResponsaveisAtuaisEdicao').text()) - 1;
            const countDisponiveis = parseInt($('#contadorUsuariosDisponiveisEdicao').text()) + 1;
            $('#contadorResponsaveisAtuaisEdicao').text(countResponsaveis);
            $('#contadorUsuariosDisponiveisEdicao').text(countDisponiveis);
            if (countResponsaveis === 0) {
                $('#responsaveisAtuaisEdicao').html('<div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>');
            }
        });

        // Buscar usuários no modal de solicitação de edição
        $('#buscarUsuarioEdicao').on('input', function() {
            const termo = $(this).val().toLowerCase().trim();
            const acaoId = $('#solicitarEdicaoId').val();
            carregarUsuariosDisponiveisParaSolicitacaoEdicao(acaoId, termo);
        });

        // Enviar formulário de solicitação de edição
        $('#formSolicitarEdicao').submit(function(e) {
            e.preventDefault();

            // Ajustar o formato das evidências antes de enviar
            const evidenciasParaEnviar = {
                adicionar: evidenciasAdicionadasAcao.map(ev => ({
                    tipo: ev.tipo,
                    evidencia: ev.conteudo, // Mapeia conteudo para evidencia
                    descricao: ev.descricao
                    // Remove id temporário e campo acao
                })),
                remover: evidenciasRemovidasAcao
            };

            // Preencher os campos hidden com as evidências no novo formato
            $('#evidenciasAdicionadasSolicitacao').val(JSON.stringify(evidenciasParaEnviar.adicionar));
            $('#evidenciasRemovidasSolicitacao').val(JSON.stringify(evidenciasParaEnviar.remover));

            // Coletar IDs dos responsáveis atuais (originalmente na ação)
            const responsaveisOriginais = [];
            $('#responsaveisAtuaisEdicao .list-group-item').each(function() {
                responsaveisOriginais.push($(this).data('id'));
            });

            // Coletar IDs dos responsáveis selecionados (após edição)
            const responsaveisSelecionados = [];
            $('#responsaveisSelecionadosEdit .list-group-item').each(function() {
                responsaveisSelecionados.push($(this).data('id'));
            });

            // Calcular diferenças
            const adicionar = responsaveisOriginais.filter(id => !responsaveisSelecionados.includes(id));
            const remover = responsaveisSelecionados.filter(id => !responsaveisOriginais.includes(id));
            const dadosResponsaveis = {
                responsaveis: {
                    adicionar: adicionar,
                    remover: remover
                }
            };
            $('#responsaveisSolicitacaoEdicao').val(JSON.stringify(dadosResponsaveis));

            const alteracoesCampos = verificarAlteracoesCampos();
            const temAlteracoesEvidencias = evidenciasAdicionadasAcao.length > 0 || evidenciasRemovidasAcao.length > 0;
            const temAlteracoesResponsaveis = adicionar.length > 0 || remover.length > 0;
            const temAlteracoes = Object.keys(alteracoesCampos).length > 0 ||
                temAlteracoesResponsaveis ||
                temAlteracoesEvidencias;

            if (!temAlteracoes) {
                $('#alertNenhumaAlteracao').removeClass('d-none');
                return;
            }

            const alteracoes = {};
            if (Object.keys(alteracoesCampos).length > 0) {
                Object.assign(alteracoes, alteracoesCampos);
            }
            if (temAlteracoesResponsaveis) {
                alteracoes.responsaveis = dadosResponsaveis.responsaveis;
            }
            if (temAlteracoesEvidencias) {
                alteracoes.evidencias = evidenciasParaEnviar; // Usa o novo formato de evidências
            }

            if ($('#dadosAlteradosSolicitacao').length === 0) {
                $('#formSolicitarEdicao').append('<input type="hidden" name="dados_alterados" id="dadosAlteradosSolicitacao">');
            }
            $('#dadosAlteradosSolicitacao').val(JSON.stringify(alteracoes));

            submitForm($(this), '#solicitarEdicaoModal', 'Solicitação de edição enviada com sucesso!');
        });

        // Função para verificar se houve alterações nos campos
        function verificarAlteracoesCampos() {
            const camposEditaveis = ['nome', 'entrega_estimada', 'data_inicio', 'data_fim', 'ordem'];
            const alteracoes = {};
            const normalizeDate = function(date) {
                if (!date) return null;
                if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                    return date;
                }
                return date.split('T')[0];
            };

            camposEditaveis.forEach(campo => {
                const valorOriginal = $('#solicitarEdicaoModal').data('original_' + campo);
                const valorAtual = $('#solicitarEdicao' + campo.charAt(0).toUpperCase() + campo.slice(1)).val();
                const valorOriginalNormalizado = campo.includes('data') || campo.includes('entrega') ?
                    normalizeDate(valorOriginal) : valorOriginal;
                const valorAtualNormalizado = campo.includes('data') || campo.includes('entrega') ?
                    normalizeDate(valorAtual) : valorAtual;

                if (String(valorOriginalNormalizado) !== String(valorAtualNormalizado)) {
                    alteracoes[campo] = {
                        de: valorOriginal,
                        para: valorAtual
                    };
                }
            });

            // Adicionar evidências às alterações se houver
            if (evidenciasAdicionadasAcao.length > 0) {
                alteracoes.evidencias = {
                    adicionar: evidenciasAdicionadasAcao
                };
            }

            if (evidenciasRemovidasAcao.length > 0) {
                if (!alteracoes.evidencias) {
                    alteracoes.evidencias = {};
                }
                alteracoes.evidencias.remover = evidenciasRemovidasAcao;
            }

            return alteracoes;
        }

        //------------------------------------------------------------
        // EDITAR AÇÃO (admin)
        //------------------------------------------------------------

        // Abrir modal de edição (para admins)
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            var acaoId = $(this).data('id').split('-')[0];
            carregarDadosParaEdicao(acaoId);
        });

        // Função para carregar dados no modal de edição
        function carregarDadosParaEdicao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/editar/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        const acao = response.data;

                        // Preencher campos básicos
                        $('#editAcaoId').val(acao.id);
                        $('#editAcaoNome').val(acao.nome);
                        $('#editAcaoOrdem').val(acao.ordem);
                        $('#editAcaoEntregaEstimada').val(acao.entrega_estimada ? formatDateForInput(acao.entrega_estimada) : '');
                        $('#editAcaoDataInicio').val(acao.data_inicio ? formatDateForInput(acao.data_inicio) : '');
                        $('#editAcaoDataFim').val(acao.data_fim ? formatDateForInput(acao.data_fim) : '');

                        // Habilitar/desabilitar data fim conforme data início
                        if (acao.data_inicio) {
                            $('#editAcaoDataFim').prop('disabled', false);
                        } else {
                            $('#editAcaoDataFim').prop('disabled', true);
                        }

                        // Carregar responsáveis
                        carregarResponsaveisParaEdicao(acao.id);

                        // Carregar evidências
                        carregarEvidenciasParaEdicao(acao.id);

                        // Abrir modal
                        $('#editAcaoModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar ação");
                    }
                },
                error: function() {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        }

        // Carregar responsáveis para edição
        function carregarResponsaveisParaEdicao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/get-responsaveis/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Limpar listas
                        $('#responsaveisSelecionadosEdit').empty();

                        // Preencher responsáveis atuais
                        if (response.data && response.data.length > 0) {
                            let html = '<div class="list-group">';
                            response.data.forEach(usuario => {
                                html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                                <div>
                                    <span class="font-weight-bold">${usuario.name}</span>
                                    <small class="d-block text-muted">${usuario.email}</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger btn-remover-responsavel" data-id="${usuario.id}">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </div>
                        `;
                            });
                            html += '</div>';
                            $('#responsaveisSelecionadosEdit').html(html);
                            $('#contadorResponsaveisEdit').text(response.data.length);
                        } else {
                            $('#responsaveisSelecionadosEdit').html('<div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>');
                            $('#contadorResponsaveisEdit').text('0');
                        }

                        // Carregar usuários disponíveis
                        carregarUsuariosDisponiveisParaEdicao(acaoId);
                    }
                },
                error: function() {
                    showErrorAlert("Erro ao carregar responsáveis");
                }
            });
        }

        // Carregar usuários disponíveis para edição
        function carregarUsuariosDisponiveisParaEdicao(acaoId, termo = '') {
            $.ajax({
                url: `<?= site_url('acoes/get-usuarios-disponiveis/') ?>${acaoId}`,
                type: 'GET',
                data: {
                    term: termo
                },
                beforeSend: function() {
                    $('#usuariosDisponiveisEdit').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
                },
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        if (response.data.length > 0) {
                            html = '<div class="list-group">';
                            response.data.forEach(usuario => {
                                html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                                <div>
                                    <span class="font-weight-bold">${usuario.name}</span>
                                    <small class="d-block text-muted">${usuario.email}</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary btn-adicionar-responsavel" data-id="${usuario.id}">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                        `;
                            });
                            html += '</div>';
                        }
                        if (html === '') {
                            html = '<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>';
                        }
                        $('#usuariosDisponiveisEdit').html(html);
                        $('#contadorUsuariosEdit').text(response.data.length);
                    } else {
                        $('#usuariosDisponiveisEdit').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                    }
                },
                error: function() {
                    $('#usuariosDisponiveisEdit').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
            });
        }

        // Carregar evidências para edição
        function carregarEvidenciasParaEdicao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/listar-evidencias/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#evidenciasAtuaisList .list-group').empty();
                        $('#evidenciasRemoverList .list-group').empty();

                        if (response.evidencias && response.evidencias.length > 0) {
                            response.evidencias.forEach((evidencia, index) => {
                                let html = `
                            <div class="list-group-item d-flex justify-content-between align-items-start" data-id="${evidencia.id}">
                                <div class="flex-grow-1 w-100">
                                    <div><strong>Evidência:</strong></div>
                                    <div class="mt-1 mb-2">
                                        ${evidencia.tipo === 'texto'
                                            ? `<span>${evidencia.evidencia}</span>`
                                            : `<a href="${evidencia.evidencia}" target="_blank" class="btn btn-sm btn-outline-primary">Acessar</a>`
                                        }
                                    </div>
                                    <div><span class="font-weight-bold small text-secondary">Descrição:</span><br>
                                        <span>${evidencia.descricao && evidencia.descricao.trim() ? evidencia.descricao : 'Sem descrição'}</span>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-marcar-remocao-evidencia" title="Remover evidência" data-id="${evidencia.id}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                                $('#evidenciasAtuaisList .list-group').append(html);
                            });
                            $('#contadorEvidenciasAtuais').text(response.evidencias.length);
                        } else {
                            $('#evidenciasAtuaisList .list-group').html('<div class="text-center py-3 text-muted">Nenhuma evidência cadastrada</div>');
                            $('#contadorEvidenciasAtuais').text('0');
                        }
                        $('#contadorEvidenciasRemover').text('0');
                    }
                },
                error: function() {
                    showErrorAlert("Erro ao carregar evidências");
                }
            });
        }

        // Adicionar responsável na edição
        $(document).on('click', '#usuariosDisponiveisEdit .btn-adicionar-responsavel', function() {
            const usuarioId = $(this).data('id');
            const item = $(this).closest('.list-group-item').clone();

            item.find('button')
                .removeClass('btn-outline-primary btn-adicionar-responsavel')
                .addClass('btn-outline-danger btn-remover-responsavel')
                .html('<i class="fas fa-user-minus"></i>');

            if ($('#responsaveisSelecionadosEdit').text().includes('Nenhum responsável selecionado')) {
                $('#responsaveisSelecionadosEdit').html('<div class="list-group"></div>');
            }

            $('#responsaveisSelecionadosEdit .list-group').append(item);
            $(this).closest('.list-group-item').remove();

            const countResponsaveis = parseInt($('#contadorResponsaveisEdit').text()) + 1;
            const countDisponiveis = parseInt($('#contadorUsuariosEdit').text()) - 1;

            $('#contadorResponsaveisEdit').text(countResponsaveis);
            $('#contadorUsuariosEdit').text(countDisponiveis);

            atualizarIdsResponsaveis();
        });

        // Remover responsável na edição
        $(document).on('click', '#responsaveisSelecionadosEdit .btn-remover-responsavel', function() {
            const usuarioId = $(this).data('id');
            const item = $(this).closest('.list-group-item').clone();

            item.find('button')
                .removeClass('btn-outline-danger btn-remover-responsavel')
                .addClass('btn-outline-primary btn-adicionar-responsavel')
                .html('<i class="fas fa-user-plus"></i>');

            $('#usuariosDisponiveisEdit').append(item);
            $(this).closest('.list-group-item').remove();

            const countResponsaveis = parseInt($('#contadorResponsaveisEdit').text()) - 1;
            const countDisponiveis = parseInt($('#contadorUsuariosEdit').text()) + 1;

            $('#contadorResponsaveisEdit').text(countResponsaveis);
            $('#contadorUsuariosEdit').text(countDisponiveis);

            if (countResponsaveis === 0) {
                $('#responsaveisSelecionadosEdit').html('<div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>');
            }

            atualizarIdsResponsaveis();
        });

        // Buscar usuários no modal de edição
        $('#buscarUsuarioEdit').on('input', function() {
            const termo = $(this).val().toLowerCase().trim();
            const acaoId = $('#editAcaoId').val();
            carregarUsuariosDisponiveisParaEdicao(acaoId, termo);
        });

        // Atualizar lista de IDs dos responsáveis
        function atualizarIdsResponsaveis() {
            const ids = [];
            $('#responsaveisSelecionadosEdit .list-group-item').each(function() {
                ids.push($(this).data('id'));
            });
            $('#responsaveisIdsEdit').val(ids.join(','));
        }

        // Alternar entre tipos de evidência no modal de edição
        $('input[name="evidencia_tipo"]').change(function() {
            if ($(this).val() === 'texto') {
                $('#editAcaoGrupoTexto').removeClass('d-none');
                $('#editAcaoGrupoLink').addClass('d-none');
            } else {
                $('#editAcaoGrupoTexto').addClass('d-none');
                $('#editAcaoGrupoLink').removeClass('d-none');
            }
        });

        // Adicionar evidência à lista no modal de edição
        $('#btnAdicionarEvidencia').click(function() {
            const tipo = $('input[name="evidencia_tipo"]:checked').val();
            const conteudo = tipo === 'texto' ?
                $('#editAcaoEvidenciaTexto').val().trim() :
                $('#editAcaoEvidenciaLink').val().trim();
            const descricao = $('#editAcaoEvidenciaDescricao').val().trim();

            if ((tipo === 'texto' && conteudo.length < 3) ||
                (tipo === 'link' && !isValidUrl(conteudo))) {
                showErrorAlert(tipo === 'texto' ?
                    'O texto da evidência deve ter pelo menos 3 caracteres' :
                    'Por favor, insira uma URL válida');
                return;
            }

            const novaEvidencia = {
                id: Date.now(), // ID temporário
                tipo: tipo,
                conteudo: conteudo,
                descricao: descricao,
                data: new Date().toLocaleString('pt-BR'),
                acao: 'incluir'
            };

            evidenciasAdicionadasAcao.push(novaEvidencia);
            atualizarListaEvidenciasAdicionadas();

            // Limpar campos
            $('#editAcaoEvidenciaTexto, #editAcaoEvidenciaLink, #editAcaoEvidenciaDescricao').val('');
            $('#editAcaoGrupoTexto').removeClass('d-none');
            $('#editAcaoGrupoLink').addClass('d-none');
            $('input[name="evidencia_tipo"][value="texto"]').prop('checked', true);
        });

        // Atualizar lista de evidências adicionadas
        function atualizarListaEvidenciasAdicionadas() {
            // Remove items adicionados anteriormente para não acumular duplicados
            $('#evidenciasAtuaisList .nova-evidencia').remove();

            if (evidenciasAdicionadasAcao.length === 0) {
                $('#contadorEvidenciasAtuais').text($('#evidenciasAtuaisList .list-group-item').length);
                return;
            }

            evidenciasAdicionadasAcao.forEach((evidencia, index) => {
                let html = `
            <div class="list-group-item nova-evidencia d-flex justify-content-between align-items-start" data-id="${evidencia.id}">
                <div class="flex-grow-1 w-100">
                    <div>
                        <strong>Evidência: <span class="badge badge-success ml-1">Nova</span></strong>
                    </div>
                    <div class="mt-1 mb-2">
                        ${evidencia.tipo === 'texto'
                            ? `<span>${evidencia.conteudo}</span>`
                            : `<a href="${evidencia.conteudo}" target="_blank" class="btn btn-sm btn-outline-primary">Acessar</a>`
                        }
                    </div>
                    <div>
                        <span class="small text-secondary">Descrição:</span><br>
                        <span>${evidencia.descricao && evidencia.descricao.trim() ? evidencia.descricao : 'Sem descrição'}</span>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remover-evidencia-add" data-id="${evidencia.id}" title="Remover evidência">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;
                $('#evidenciasAtuaisList .list-group').append(html);
            });

            // Atualiza o contador: atuais + novas
            let total = $('#evidenciasAtuaisList .list-group-item').length;
            $('#contadorEvidenciasAtuais').text(total);
        }

        // Remover evidência da lista de adição
        $(document).on('click', '.btn-remover-evidencia-add', function() {
            const id = $(this).data('id');
            evidenciasAdicionadasAcao = evidenciasAdicionadasAcao.filter(e => e.id != id);
            atualizarListaEvidenciasAdicionadas();
        });

        // Marcar evidência para remoção (move para lista removidas, deixa amarela e troca botão)
        $(document).on('click', '.btn-marcar-remocao-evidencia', function() {
            const $item = $(this).closest('.list-group-item');
            $item.addClass('list-group-item-warning');
            $(this)
                .removeClass('btn-outline-danger btn-marcar-remocao-evidencia')
                .addClass('btn-outline-secondary btn-desmarcar-remocao-evidencia')
                .attr('title', 'Desfazer remoção')
                .html('<i class="fas fa-undo"></i>');

            $('#evidenciasRemoverList .list-group').append($item);
            $('#contadorEvidenciasAtuais').text($('#evidenciasAtuaisList .list-group-item').length);
            $('#contadorEvidenciasRemover').text($('#evidenciasRemoverList .list-group-item').length);

            evidenciasRemovidasAcao.push($item.data('id'));
        });

        // Desfazer remoção (move para lista atuais, remove amarelo e volta botão)
        $(document).on('click', '.btn-desmarcar-remocao-evidencia', function() {
            const $item = $(this).closest('.list-group-item');
            $item.removeClass('list-group-item-warning');
            $(this)
                .removeClass('btn-outline-secondary btn-desmarcar-remocao-evidencia')
                .addClass('btn-outline-danger btn-marcar-remocao-evidencia')
                .attr('title', 'Remover evidência')
                .html('<i class="fas fa-trash-alt"></i>');

            $('#evidenciasAtuaisList .list-group').append($item);
            $('#contadorEvidenciasAtuais').text($('#evidenciasAtuaisList .list-group-item').length);
            $('#contadorEvidenciasRemover').text($('#evidenciasRemoverList .list-group-item').length);

            evidenciasRemovidasAcao = evidenciasRemovidasAcao.filter(e => e != $item.data('id'));
        });

        // Enviar formulário de edição
        $('#formEditAcao').submit(function(e) {
            e.preventDefault();

            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            // Coletar IDs dos responsáveis selecionados
            const responsaveisIds = [];
            $('#responsaveisSelecionadosEdit .list-group-item').each(function() {
                responsaveisIds.push($(this).data('id'));
            });
            $('#responsaveisIdsEdit').val(responsaveisIds.join(','));

            // Formatar evidências para envio
            const evidenciasParaEnviar = {
                adicionar: evidenciasAdicionadasAcao.map(ev => ({
                    tipo: ev.tipo,
                    evidencia: ev.conteudo,
                    descricao: ev.descricao
                })),
                remover: evidenciasRemovidasAcao
            };

            $('#evidenciasAdicionadasSolicitacao').val(JSON.stringify(evidenciasParaEnviar.adicionar));
            $('#evidenciasRemovidasSolicitacao').val(JSON.stringify(evidenciasParaEnviar.remover));

            // Enviar via AJAX
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#editAcaoModal').modal('hide');
                        showSuccessAlert(response.message || 'Ação atualizada com sucesso!');
                        dataTable.ajax.reload(null, false);
                    } else {
                        showErrorAlert(response.message || 'Ocorreu um erro ao atualizar a ação.');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    showErrorAlert('Erro na comunicação com o servidor.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        // Habilitar data fim quando data início for preenchida
        $('#editAcaoDataInicio').change(function() {
            if ($(this).val()) {
                $('#editAcaoDataFim').prop('disabled', false);
            } else {
                $('#editAcaoDataFim').val('').prop('disabled', true);
            }
        });

        // Limpar variáveis quando o modal é fechado
        $('#editAcaoModal').on('hidden.bs.modal', function() {
            evidenciasAdicionadasAcao = [];
            evidenciasRemovidasAcao = [];
        });


    });
</script>