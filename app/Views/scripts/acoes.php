<!-- jQuery PRIMEIRO - versão mais recente -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Bootstrap -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                                'Em andamento': 'badge-primary',
                                'Paralisado': 'badge-dark',
                                'Atrasado': 'badge-danger',
                                'Não iniciado': 'badge-secondary'
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

        // Handler para o formulário de inclusão
        $('#formAddAcao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#addAcaoModal', 'Ação cadastrada com sucesso!');
        });

        // Editar ação - Abrir modal (apenas admin)
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
            var acaoId = $(this).data('id').split('-')[0];
            loadAcaoData(acaoId, '#editAcaoModal', 'editar');
        });

        // Solicitar edição de ação - Abrir modal (para não-admins)
        $(document).on('click', '.btn-primary[title="Solicitar Edição"]', function() {
            var acaoId = $(this).data('id').split('-')[0];
            loadAcaoData(acaoId, '#solicitarEdicaoModal', 'dados-acao');
        });

        // Controlar habilitação do campo data fim baseado na data início
        $('#editAcaoDataInicio').on('change', function() {
            if ($(this).val()) {
                $('#editAcaoDataFim').prop('disabled', false);
            } else {
                $('#editAcaoDataFim').val('').prop('disabled', true);
            }
        });

        // Função para carregar dados da ação
        function loadAcaoData(acaoId, modalId, endpoint) {
            $.ajax({
                url: `<?= site_url('acoes/') ?>${endpoint}/${acaoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        const acao = response.data;
                        const prefix = modalId.replace('#', '').replace('Modal', '');

                        $(`#${prefix}Id`).val(acao.id);
                        $(`#${prefix}Nome`).val(acao.nome);
                        $(`#${prefix}Responsavel`).val(acao.responsavel);

                        // Tratamento de datas
                        const setDateValue = (field, value) => {
                            if (value) {
                                const datePart = value.split(' ')[0];
                                $(`#${prefix}${field}`).val(isValidDate(datePart) ? datePart : '');
                            } else {
                                $(`#${prefix}${field}`).val('');
                            }
                        };

                        setDateValue('EntregaEstimada', acao.entrega_estimada);
                        setDateValue('DataInicio', acao.data_inicio);
                        setDateValue('DataFim', acao.data_fim);

                        // Habilitar data fim se data início estiver preenchida
                        if (acao.data_inicio) {
                            $('#editAcaoDataFim').prop('disabled', false);
                        } else {
                            $('#editAcaoDataFim').prop('disabled', true);
                        }

                        $(`#${prefix}Ordem`).val(acao.ordem);
                        $(modalId).modal('show');

                        if (modalId === '#solicitarEdicaoModal') {
                            // Armazena os dados originais para comparação
                            formOriginalData = {
                                nome: acao.nome,
                                responsavel: acao.responsavel,
                                status: acao.status || 'Não iniciado',
                                tempo_estimado_dias: acao.tempo_estimado_dias,
                                entrega_estimada: acao.entrega_estimada ? acao.entrega_estimada.split(' ')[0] : '',
                                data_inicio: acao.data_inicio ? acao.data_inicio.split(' ')[0] : '',
                                data_fim: acao.data_fim ? acao.data_fim.split(' ')[0] : '',
                                ordem: acao.ordem
                            };

                            $('#alertNenhumaAlteracao').addClass('d-none');
                        }
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar ação");
                    }
                },
                error: function() {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
        }

        function checkForChanges() {
            let hasChanges = false;
            const form = $('#formSolicitarEdicao');

            ['nome', 'responsavel', 'status', 'tempo_estimado_dias',
                'entrega_estimada', 'data_inicio', 'data_fim', 'ordem'
            ].forEach(field => {
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

        // Solicitar exclusão de ação - Abrir modal (para não-admins)
        $(document).on('click', '.btn-danger[title="Solicitar Exclusão"]', function() {
            var acaoId = $(this).data('id').split('-')[0];
            var acaoName = $(this).closest('tr').find('td:nth-child(2)').text();

            loadAcaoForRequest(acaoId, acaoName, '#solicitarExclusaoModal');
        });

        // Excluir ação - Abrir modal de confirmação (apenas admin)
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var acaoId = $(this).data('id').split('-')[0];

            $.ajax({
                url: `<?= site_url('acoes/dados-acao/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    // Mostrar loading se necessário
                },
                success: function(response) {
                    if (response.success && response.data) {
                        $('#deleteAcaoId').val(acaoId);
                        $('#acaoNameToDelete').text(response.data.nome);
                        $('#deleteAcaoModal').modal('show');
                    } else {
                        showErrorAlert(response.message || "Erro ao carregar ação");
                    }
                },
                error: function() {
                    showErrorAlert("Falha na comunicação com o servidor.");
                }
            });
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

        // Enviar solicitação de inclusão
        $('#formSolicitarInclusao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarInclusaoModal', 'Solicitação de inclusão enviada com sucesso!');
        });

        // Atualizar ação (apenas admin)
        $('#formEditAcao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#editAcaoModal');
        });

        // Confirmar exclusão (apenas admin)
        $('#formDeleteAcao').submit(function(e) {
            e.preventDefault();

            // Validação adicional para exclusão
            const acaoId = $('#deleteAcaoId').val();
            if (!acaoId) {
                showErrorAlert('ID da ação não encontrado');
                return;
            }

            submitForm($(this), '#deleteAcaoModal', 'Ação excluída com sucesso!');
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
                        // Exibir alerta de sucesso sempre que houver uma mensagem
                        if (response.message) {
                            showSuccessAlert(response.message);
                        } else if (successMessage) {
                            showSuccessAlert(successMessage);
                        }

                        if (modalId) {
                            $(modalId).modal('hide');
                        }

                        // Recarregar apenas se não for uma solicitação
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
                }
            });
        }

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

        // Debug - verifique se a requisição está sendo feita
        $('#selectUsuarioEquipe').on('select2:open', function() {
            console.log('Select2 aberto, acaoIdEquipe:', acaoIdEquipe);
        });

        $('#selectUsuarioEquipe').on('select2:select', function(e) {
            console.log('Usuário selecionado:', e.params.data);
        });

        // Adicione este código no seu script
        $('#editAcaoModal').on('hidden.bs.modal', function() {
            $(this).removeAttr('aria-hidden');
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });

        // Função para filtrar os usuários no select
        function filtrarUsuarios(termo) {
            const select = document.getElementById('selectUsuarioEquipe');
            const options = select.options;

            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                const text = option.text.toLowerCase();
                const matches = text.includes(termo.toLowerCase());

                option.style.display = matches ? '' : 'none';
                if (matches && termo !== '') {
                    option.style.backgroundColor = '#ffff99'; // Destacar resultados
                } else {
                    option.style.backgroundColor = '';
                }
            }
        }

        // Event listener para o campo de busca
        $('#buscaUsuario').on('input', function() {
            filtrarUsuarios($(this).val());
        });

        // Atualize a função carregarUsuariosDisponiveis para manter uma cópia dos usuários
        let todosUsuarios = [];

        function carregarUsuariosDisponiveis(acaoId, listaElement, contadorElement, termo = '') {
            $.ajax({
                url: `<?= site_url('acoes/buscar-usuarios') ?>`,
                type: 'GET',
                data: {
                    acao_id: acaoId,
                    term: termo
                },
                dataType: 'json',
                beforeSend: function() {
                    listaElement.html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let html = '';
                        const responsaveisAtuais = acaoId ? responsaveisSelecionadosEdit : responsaveisSelecionadosAdd;

                        response.data.forEach(usuario => {
                            // Filtra usuários que já foram selecionados
                            if (!responsaveisAtuais.some(r => r.id === usuario.id)) {
                                html += `
                            <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                                <div>
                                    <span class="font-weight-bold">${usuario.username}</span>
                                    <small class="d-block text-muted">${usuario.email}</small>
                                </div>
                                <button class="btn btn-sm btn-primary btn-adicionar-responsavel" data-id="${usuario.id}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        `;
                            }
                        });

                        if (html === '') {
                            html = '<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>';
                        }

                        listaElement.html(html);
                        contadorElement.text(response.data.length);
                    } else {
                        listaElement.html('<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>');
                        contadorElement.text('0');
                    }
                },
                error: function() {
                    listaElement.html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
            });
        }

        // Limpar busca quando o modal é fechado
        $('#equipeAcaoModal').on('hidden.bs.modal', function() {
            $('#buscaUsuario').val('');
        });

        // Gerenciar evidências
        $(document).on('click', '#btnGerenciarEvidencias', function(e) {
            e.preventDefault();
            const acaoId = $('#editAcaoId').val();

            $('#editAcaoModal').modal('hide').on('hidden.bs.modal', function() {
                $(this).off('hidden.bs.modal');

                // Carrega o modal
                $.ajax({
                    url: `<?= site_url('acoes/gerenciar-evidencias/') ?>${acaoId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#evidenciasAcaoModal').remove();
                            $('body').append(response.html);
                            const evidenciasModal = $('#evidenciasAcaoModal');

                            evidenciasModal.modal('show');

                            // Atualiza a lista imediatamente ao abrir
                            atualizarListaEvidencias(acaoId);

                            evidenciasModal.on('hidden.bs.modal', function() {
                                $(this).remove();
                                $('#editAcaoModal').modal('show');
                            });
                        } else {
                            showErrorAlert(response.message);
                            $('#editAcaoModal').modal('show');
                        }
                    },
                    error: function() {
                        showErrorAlert('Erro ao carregar evidências');
                    }
                });
            });
        });

        $('#editAcaoModal').on('hide.bs.modal', function() {
            $(this).off('hidden.bs.modal');
        });

        // Atualize a parte do change do tipo
        $(document).on('change', 'input[name="tipo"]', function() {
            const tipo = $(this).val();
            if (tipo === 'link') {
                $('#grupoTexto').addClass('d-none');
                $('#evidenciaTexto').prop('required', false);
                $('#grupoLink').removeClass('d-none');
                $('#evidenciaLink').prop('required', true);
            } else {
                $('#grupoTexto').removeClass('d-none');
                $('#evidenciaTexto').prop('required', true);
                $('#grupoLink').addClass('d-none');
                $('#evidenciaLink').prop('required', false);
            }
        });

        //submit do formulário
        // No seu código JavaScript, substitua a parte do submit do formulário por:
        $('body').on('submit', '#formAdicionarEvidencia', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            const acaoId = form.find('input[name="acao_id"]').val();

            submitBtn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...');

            $.ajax({
                url: `<?= site_url('acoes/adicionar-evidencia/') ?>${acaoId}`,
                type: 'POST',
                data: form.serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Reset do formulário mantendo o tipo selecionado
                        const tipoSelecionado = form.find('input[name="tipo"]:checked').val();
                        form.trigger('reset');
                        form.find(`input[name="tipo"][value="${tipoSelecionado}"]`).prop('checked', true);

                        // Atualizar a lista de evidências
                        adicionarEvidenciaNaLista(response.evidencia);

                        // Mostrar mensagem de sucesso
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message,
                            confirmButtonText: 'Entendi'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Falha na comunicação com o servidor',
                        confirmButtonText: 'Entendi'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        function adicionarEvidenciaNaLista(evidencia) {
            const lista = $('#listaEvidencias .list-group');

            // Se não houver list-group (lista vazia), cria a estrutura
            if (lista.length === 0) {
                $('#listaEvidencias').html('<div class="list-group"></div>');
            }

            // Formata a data
            const dataEvidencia = new Date(evidencia.created_at);
            const dataFormatada = dataEvidencia.toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).replace(',', '');

            // Conta quantas evidências existem agora para a numeração correta
            const countEvidencias = $('.list-group-item').length + 1;

            // Cria o HTML para a nova evidência
            const html = `
        <div class="list-group-item mb-2" data-id="${evidencia.id}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Evidência #${countEvidencias}</strong>
                        <small class="text-muted">${dataFormatada}</small>
                    </div>
                    ${evidencia.tipo === 'texto' ?
                        `<div class="bg-light p-3 rounded mb-2">${evidencia.evidencia.replace(/\n/g, '<br>')}</div>` :
                        `<div class="mb-2">
                            <a href="${evidencia.evidencia}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt mr-2"></i>Abrir Link
                            </a>
                            <small class="d-block text-muted mt-1">${evidencia.evidencia}</small>
                        </div>`
                    }
                    ${evidencia.descricao ?
                        `<div class="mt-2">
                            <small class="text-muted d-block"><strong>Descrição:</strong></small>
                            <div class="bg-light p-2 rounded">${evidencia.descricao.replace(/\n/g, '<br>')}</div>
                        </div>` : ''
                    }
                </div>
                <button class="btn btn-sm btn-danger ml-2 btn-remover-evidencia" data-id="${evidencia.id}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    `;

            // Adiciona no início da lista (para manter a ordem mais recente primeiro)
            $('.list-group').prepend(html);

            // Atualiza o contador
            atualizarContadorEvidencias();
        }


        function setupEvidenciasModal(acaoId) {
            $('#evidenciasAcaoModal').on('hidden.bs.modal', function() {
                $(this).remove();
                $('#editAcaoModal').modal('show');
            });
        }

        // Remover evidência
        $(document).on('click', '.btn-remover-evidencia', function() {
            const evidenciaId = $(this).data('id');
            const $evidenciaItem = $(this).closest('.list-group-item');

            Swal.fire({
                title: 'Remover evidência?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, remover!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `<?= site_url('acoes/remover-evidencia/') ?>${evidenciaId}`,
                        type: 'POST',
                        data: {
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: (response) => {
                            if (response.success) {
                                // Remove o elemento da lista
                                $evidenciaItem.remove();

                                // Atualiza a numeração e contador
                                atualizarContadorEvidencias();

                                Swal.fire(
                                    'Removido!',
                                    'A evidência foi removida com sucesso.',
                                    'success'
                                );
                            } else {
                                Swal.fire(
                                    'Erro!',
                                    response.message || 'Ocorreu um erro ao remover a evidência.',
                                    'error'
                                );
                            }
                        },
                        error: () => {
                            Swal.fire(
                                'Erro!',
                                'Falha na comunicação com o servidor.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        function setupEvidenciasModal(acaoId) {
            $('#evidenciasAcaoModal').on('hidden.bs.modal', function() {
                $(this).remove();
                $('#editAcaoModal').modal('show');
            });
        }

        function atualizarListaEvidencias(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/listar-evidencias/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#listaEvidencias').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>');
                },
                success: function(response) {
                    if (response.success && response.evidencias && response.evidencias.length > 0) {
                        let html = '<div class="list-group">';

                        response.evidencias.forEach(evidencia => {
                            // Formata a data sem segundos
                            const dataEvidencia = new Date(evidencia.created_at);
                            const dataFormatada = dataEvidencia.toLocaleString('pt-BR', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: false
                            }).replace(',', '');

                            html += `
                        <div class="list-group-item mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>Evidência #${evidencia.ordem}</strong>
                                        <small class="text-muted">${dataFormatada}</small>
                                    </div>
                                    ${evidencia.tipo === 'texto' ?
                                        `<div class="bg-light p-3 rounded mb-2">${evidencia.evidencia.replace(/\n/g, '<br>')}</div>` :
                                        `<div class="mb-2">
                                            <a href="${evidencia.evidencia}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-external-link-alt mr-2"></i>Abrir Link
                                            </a>
                                            <small class="d-block text-muted mt-1">${evidencia.evidencia}</small>
                                        </div>`
                                    }
                                    ${evidencia.descricao ?
                                        `<div class="mt-2">
                                            <small class="text-muted d-block"><strong>Descrição:</strong></small>
                                            <div class="bg-light p-2 rounded">${evidencia.descricao.replace(/\n/g, '<br>')}</div>
                                        </div>` : ''
                                    }
                                </div>
                                <button class="btn btn-sm btn-danger ml-2 btn-remover-evidencia" data-id="${evidencia.id}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    `;
                        });

                        html += '</div>';
                        $('#listaEvidencias').html(html);
                        $('.badge-pill').text(response.evidencias.length);
                    } else {
                        $('#listaEvidencias').html(`
                    <div class="alert alert-info text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p class="mb-0">Nenhuma evidência cadastrada ainda.</p>
                    </div>
                `);
                        $('.badge-pill').text('0');
                    }
                },
                error: function() {
                    $('#listaEvidencias').html(`
                <div class="alert alert-danger text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <p class="mb-0">Erro ao carregar evidências</p>
                </div>
            `);
                }
            });
        }

        function atualizarContadorEvidencias() {
            const count = $('.list-group-item').length;
            $('.badge-pill').text(count);

            // Atualiza a numeração de cada evidência
            $('.list-group-item').each(function(index) {
                const newNumber = count - index;
                $(this).find('strong').text(`Evidência #${newNumber}`);
            });

            // Se não houver mais evidências, mostra mensagem
            if (count === 0) {
                $('#listaEvidencias').html(`
            <div class="alert alert-info text-center py-4">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <p class="mb-0">Nenhuma evidência cadastrada ainda.</p>
            </div>
        `);
            }
        }

        // Código do evento de abertura do modal de evidências
        $('body').on('shown.bs.modal', '#evidenciasAcaoModal', function() {
            // Garante que os campos estejam configurados corretamente na abertura
            const tipoSelecionado = $('input[name="tipo"]:checked').val();

            if (tipoSelecionado === 'link') {
                $('#grupoTexto').addClass('d-none');
                $('#evidenciaTexto').prop('required', false);
                $('#grupoLink').removeClass('d-none');
                $('#evidenciaLink').prop('required', true);
            } else {
                $('#grupoTexto').removeClass('d-none');
                $('#evidenciaTexto').prop('required', true);
                $('#grupoLink').addClass('d-none');
                $('#evidenciaLink').prop('required', false);
            }

            // Foca no primeiro campo visível
            if (tipoSelecionado === 'link') {
                $('#evidenciaLink').focus();
            } else {
                $('#evidenciaTexto').focus();
            }
        });

        // PARTE DE EVIDÊNCIAS --------------------------

        // Variáveis para controle de evidências
        let evidenciasAtuais = []; // Evidências carregadas do banco
        let evidenciasAdicionadas = []; // Novas evidências a serem incluídas
        let evidenciasRemovidas = []; // Evidências marcadas para remoção

        // Função para carregar evidências da ação
        function carregarEvidenciasAcao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/listar-evidencias/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.evidencias) {
                        evidenciasAtuais = response.evidencias;
                        atualizarListaEvidenciasAtuais();
                        atualizarContadoresEvidencias();
                    }
                },
                error: function() {
                    $('#evidenciasAtuaisList .list-group').html(
                        '<div class="text-center py-3 text-danger">Erro ao carregar evidências</div>'
                    );
                }
            });
        }

        function atualizarListaEvidenciasAtuais() {
            const lista = $('#evidenciasAtuaisList .list-group');
            lista.empty();

            // Filtra evidências atuais (não removidas) e adiciona as novas
            const evidenciasMostrar = [
                ...evidenciasAtuais.filter(ev =>
                    !evidenciasRemovidas.some(r => r.id === ev.id)
                ),
                ...evidenciasAdicionadas
            ];

            if (evidenciasMostrar.length > 0) {
                evidenciasMostrar.forEach((evidencia, index) => {
                    const isNova = evidencia.acao === 'incluir';
                    const dataFormatada = isNova ?
                        evidencia.data :
                        formatarData(evidencia.created_at);

                    const item = `
            <div class="list-group-item py-2 d-flex justify-content-between align-items-start"
                 data-id="${evidencia.id}" data-nova="${isNova}">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong>${isNova ? 'Nova Evidência' : 'Evidência'} #${index + 1}</strong>
                        <small class="text-muted mr-2">${dataFormatada}</small>
                    </div>
                    ${evidencia.tipo === 'texto' ?
                        `<div class="bg-light p-2 rounded mb-1 small text-break">${evidencia.conteudo || evidencia.evidencia}</div>` :
                        `<div class="mb-1">
                            <a href="${evidencia.conteudo || evidencia.evidencia}" target="_blank"
                               class="btn btn-sm btn-outline-primary btn-xs">
                                <i class="fas fa-external-link-alt mr-1"></i>Abrir Link
                            </a>
                        </div>`
                    }
                    ${evidencia.descricao ?
                        `<div class="mt-1">
                            <small class="text-muted d-block"><strong>Descrição:</strong></small>
                            <div class="bg-light p-1 rounded small text-break">${evidencia.descricao}</div>
                        </div>` : ''
                    }
                </div>
                <button class="btn btn-sm ${isNova ? 'btn-outline-danger btn-remover-evidencia-solicitada' : 'btn-outline-secondary btn-mover-remover'} ml-2"
                        data-id="${evidencia.id}"
                        title="${isNova ? 'Remover evidência' : 'Marcar para remoção'}">
                    <i class="fas ${isNova ? 'fa-trash-alt' : 'fa-times'}"></i>
                </button>
            </div>
        `;
                    lista.append(item);
                });
            } else {
                lista.append('<div class="text-center py-3 text-muted">Nenhuma evidência disponível</div>');
            }
        }

        // Função para atualizar a lista de evidências a remover
        function atualizarListaEvidenciasRemover() {
            const lista = $('#evidenciasRemoverList .list-group');
            lista.empty();

            if (evidenciasRemovidas.length > 0) {
                evidenciasRemovidas.forEach(evidencia => {
                    const item = `
            <div class="list-group-item py-2 d-flex justify-content-between align-items-start" data-id="${evidencia.id}">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between mb-1">
                        <strong>${evidencia.nome || 'Evidência'}</strong>
                        <small class="text-muted mr-2">${formatarData(evidencia.created_at)}</small>
                    </div>
                    ${evidencia.tipo === 'texto' ?
                        `<div class="bg-light p-2 rounded mb-1 small text-break">${evidencia.evidencia.substring(0, 50)}${evidencia.evidencia.length > 50 ? '...' : ''}</div>` :
                        `<div class="mb-1">
                            <a href="${evidencia.evidencia}" target="_blank"
                               class="btn btn-sm btn-outline-primary btn-xs">
                                <i class="fas fa-external-link-alt mr-1"></i>Abrir Link
                            </a>
                        </div>`
                    }
                </div>
                <button class="btn btn-sm btn-outline-success btn-desfazer-remocao ml-2"
                        data-id="${evidencia.id}"
                        title="Desfazer remoção">
                    <i class="fas fa-undo"></i>
                </button>
            </div>
        `;
                    lista.append(item);
                });
            } else {
                lista.append('<div class="text-center py-3 text-muted">Nenhuma evidência marcada para remoção</div>');
            }
        }

        // Função para atualizar a lista de evidências adicionadas
        function atualizarListaEvidenciasAdicionadas() {
            const lista = $('#evidenciasAdicionadasList');
            lista.empty();

            if (evidenciasAdicionadas.length === 0) {
                lista.append('<div class="text-center py-3"><i class="fas fa-info-circle"></i> Nenhuma evidência será adicionada</div>');
            } else {
                evidenciasAdicionadas.forEach((evidencia, index) => {
                    const item = `
                <div class="list-group-item py-2" data-id="${evidencia.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong>Nova Evidência #${index + 1}</strong>
                                <small class="text-muted">${evidencia.data}</small>
                            </div>
                            ${evidencia.tipo === 'texto'
                                ? `<div class="bg-light p-2 rounded mb-1">${evidencia.conteudo.substring(0, 50)}${evidencia.conteudo.length > 50 ? '...' : ''}</div>`
                                : `<div class="mb-1"><small class="text-truncate d-block">${evidencia.conteudo}</small></div>`}
                            ${evidencia.descricao ? `<small class="text-muted">${evidencia.descricao.substring(0, 30)}${evidencia.descricao.length > 30 ? '...' : ''}</small>` : ''}
                        </div>
                        <button class="btn btn-sm btn-outline-danger btn-remover-evidencia-solicitada" data-id="${evidencia.id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
                    lista.append(item);
                });
            }
        }

        // Função para atualizar todos os contadores
        function atualizarContadoresEvidencias() {
            const totalAtuais = (evidenciasAtuais.length - evidenciasRemovidas.length) + evidenciasAdicionadas.length;
            $('#contadorEvidenciasAtuais').text(totalAtuais > 0 ? totalAtuais : 0);
            $('#contadorEvidenciasRemover').text(evidenciasRemovidas.length);
            $('#contadorEvidenciasAdicionadas').text(evidenciasAdicionadas.length);
        }

        // Função auxiliar para formatar data
        function formatarData(dataString) {
            if (!dataString) return '';
            const data = new Date(dataString);
            return data.toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).replace(',', '');
        }

        // Adicionar evidência à lista de solicitação
        $('#btnAdicionarEvidencia').click(function() {
            const tipo = $('input[name="evidencia_tipo"]:checked').val();
            const conteudo = tipo === 'texto' ?
                $('#solicitarEdicaoEvidenciaTexto').val().trim() :
                $('#solicitarEdicaoEvidenciaLink').val().trim();
            const descricao = $('#solicitarEdicaoEvidenciaDescricao').val().trim();

            if ((tipo === 'texto' && conteudo.length < 3) || (tipo === 'link' && !isValidUrl(conteudo))) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: tipo === 'texto' ?
                        'O texto da evidência deve ter pelo menos 3 caracteres' : 'Por favor, insira uma URL válida',
                    confirmButtonText: 'Entendi'
                });
                return;
            }

            const novaEvidencia = {
                id: Date.now(), // ID temporário
                tipo: tipo,
                conteudo: conteudo,
                descricao: descricao,
                data: new Date().toLocaleString('pt-BR'),
                acao: 'incluir' // Marcamos como inclusão para a solicitação
            };

            evidenciasAdicionadas.push(novaEvidencia);
            atualizarListaEvidenciasAtuais();
            atualizarContadoresEvidencias();

            // Limpar campos
            $('#solicitarEdicaoEvidenciaTexto, #solicitarEdicaoEvidenciaLink, #solicitarEdicaoEvidenciaDescricao').val('');
            $('#solicitarEdicaoGrupoTexto').removeClass('d-none');
            $('#solicitarEdicaoGrupoLink').addClass('d-none');
            $('input[name="evidencia_tipo"][value="texto"]').prop('checked', true);

            checkForChanges();
        });

        // Remover evidência (nova)
        $(document).on('click', '.btn-remover-evidencia-solicitada', function() {
            const id = $(this).data('id');
            evidenciasAdicionadas = evidenciasAdicionadas.filter(e => e.id != id);
            atualizarListaEvidenciasAtuais();
            atualizarContadoresEvidencias();
            checkForChanges();
        });

        // Marcar evidência existente para remoção
        $(document).on('click', '.btn-mover-remover', function() {
            const evidenciaId = $(this).data('id');
            const evidencia = evidenciasAtuais.find(e => e.id == evidenciaId);

            if (evidencia) {
                // Verifica se já não está marcada para remoção
                if (!evidenciasRemovidas.some(e => e.id == evidenciaId)) {
                    evidenciasRemovidas.push(evidencia);
                    atualizarListaEvidenciasAtuais();
                    atualizarListaEvidenciasRemover();
                    atualizarContadoresEvidencias();
                    checkForChanges();
                }
            }
        });

        // Função para validar URL
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        // Evento para desfazer remoção de evidência
        $(document).on('click', '.btn-desfazer-remocao', function() {
            const evidenciaId = $(this).data('id');

            // Remove da lista de removidas
            evidenciasRemovidas = evidenciasRemovidas.filter(e => e.id != evidenciaId);

            // Atualiza ambas as listas
            atualizarListaEvidenciasAtuais();
            atualizarListaEvidenciasRemover();
            atualizarContadoresEvidencias();
            checkForChanges();
        });

        // Evento para remover evidência da lista de adição
        $(document).on('click', '.btn-remover-evidencia-solicitada', function() {
            const id = $(this).data('id');
            evidenciasAdicionadas = evidenciasAdicionadas.filter(e => e.id != id);
            atualizarListaEvidenciasAdicionadas();
            atualizarContadoresEvidencias();
            checkForChanges();
        });

        // Verificar alterações no formulário
        function checkForChanges() {
            let hasChanges = false;
            const form = $('#formSolicitarEdicao');

            // Verifica campos regulares
            ['nome', 'responsavel', 'status', 'tempo_estimado_dias',
                'entrega_estimada', 'data_inicio', 'data_fim'
            ].forEach(field => {
                const currentValue = form.find(`[name="${field}"]`).val();
                if (formOriginalData[field] != currentValue) {
                    hasChanges = true;
                }
            });

            // Verifica alterações na equipe
            const adicionarMembros = $('#adicionarMembroInput').val();
            const removerMembros = $('#removerMembroInput').val();
            if (adicionarMembros || removerMembros) {
                hasChanges = true;
            }

            // Verifica se há evidências adicionadas ou removidas
            if (evidenciasAdicionadas.length > 0 || evidenciasRemovidas.length > 0) {
                hasChanges = true;
            }

            if (hasChanges) {
                $('#alertNenhumaAlteracao').addClass('d-none');
                $('#btnEnviarSolicitacao').prop('disabled', false);
            } else {
                $('#alertNenhumaAlteracao').removeClass('d-none');
                $('#btnEnviarSolicitacao').prop('disabled', true);
            }
        }

        function verificarAlteracoesValidas() {
            // Verifica campos regulares
            const camposAlterados = ['nome', 'responsavel', 'tempo_estimado_dias',
                    'entrega_estimada', 'data_inicio', 'data_fim', 'status', 'ordem'
                ]
                .some(campo => {
                    const valorAtual = formOriginalData[campo] ?? null;
                    const valorNovo = $(`[name="${campo}"]`).val();
                    return (valorAtual != valorNovo);
                });

            // Verifica alterações na equipe
            const equipeAlterada = $('#adicionarMembroInput').val() || $('#removerMembroInput').val();

            // Verifica evidências (modificado para verificar diretamente as arrays)
            const evidenciasAlteradas = (evidenciasAdicionadas.length > 0 || evidenciasRemovidas.length > 0);

            return camposAlterados || equipeAlterada || evidenciasAlteradas;
        }

        // Modificar o submit do formulário
        $('#formSolicitarEdicao').submit(function(e) {
            e.preventDefault();

            // Verificar se há alterações válidas - versão mais robusta
            const hasChanges = verificarAlteracoesValidas() ||
                (evidenciasAdicionadas.length > 0) ||
                (evidenciasRemovidas.length > 0);

            if (!hasChanges) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Nenhuma alteração válida foi detectada. Modifique pelo menos um campo para enviar a solicitação.',
                    confirmButtonText: 'Entendi'
                });
                return;
            }

            const formData = new FormData(this);

            // Adicionar as evidências ao FormData de forma mais explícita
            if (evidenciasAdicionadas.length > 0) {
                formData.append('has_evidencias_adicionadas', '1');
                formData.append('evidencias_adicionadas', JSON.stringify(evidenciasAdicionadas));
            }
            if (evidenciasRemovidas.length > 0) {
                formData.append('has_evidencias_removidas', '1');
                formData.append('evidencias_removidas', JSON.stringify(evidenciasRemovidas.map(e => e.id)));
            }

            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#solicitarEdicaoModal').modal('hide');
                        showSuccessAlert(response.message || 'Solicitação enviada com sucesso!');
                        // Limpar as listas após envio
                        evidenciasAdicionadas = [];
                        evidenciasRemovidas = [];
                    } else {
                        showErrorAlert(response.message || 'Ocorreu um erro ao enviar a solicitação.');
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

        // Variáveis globais para controle
        let responsaveisSelecionadosAdd = [];
        let usuariosDisponiveisAdd = [];

        // Função para carregar usuários disponíveis
        function carregarUsuariosDisponiveisAdd() {
            $.ajax({
                url: '<?= site_url("acoes/buscar-usuarios") ?>',
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#usuariosDisponiveisAdd').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        usuariosDisponiveisAdd = response.data;
                        atualizarListaUsuariosDisponiveisAdd();
                    } else {
                        $('#usuariosDisponiveisAdd').html('<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>');
                    }
                    $('#contadorUsuariosAdd').text(response.data.length || 0);
                },
                error: function() {
                    $('#usuariosDisponiveisAdd').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
            });
        }

        // Função para atualizar a lista de usuários disponíveis
        function atualizarListaUsuariosDisponiveisAdd() {
            const lista = $('#usuariosDisponiveisAdd');
            lista.empty();

            if (usuariosDisponiveisAdd.length === 0) {
                lista.html('<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>');
                return;
            }

            let html = '';
            usuariosDisponiveisAdd.forEach(usuario => {
                // Verifica se o usuário já está na lista de selecionados
                const jaSelecionado = responsaveisSelecionadosAdd.some(r => r.id === usuario.id);

                if (!jaSelecionado) {
                    html += `
                <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                    <div>
                        <span class="font-weight-bold">${usuario.username}</span>
                        <small class="d-block text-muted">${usuario.email}</small>
                    </div>
                    <button class="btn btn-sm btn-primary btn-adicionar-responsavel" data-id="${usuario.id}">
                        <i class="fas fa-plus"></i>
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

        // Função para atualizar a lista de responsáveis selecionados
        function atualizarResponsaveisSelecionadosAdd() {
            const lista = $('#responsaveisSelecionadosAdd');
            lista.empty();

            if (responsaveisSelecionadosAdd.length === 0) {
                lista.html('<div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>');
                $('#contadorResponsaveisAdd').text('0');
                return;
            }

            let html = '';
            responsaveisSelecionadosAdd.forEach(usuario => {
                html += `
            <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                <div>
                    <span class="font-weight-bold">${usuario.username}</span>
                    <small class="d-block text-muted">${usuario.email}</small>
                </div>
                <button class="btn btn-sm btn-danger btn-remover-responsavel" data-id="${usuario.id}">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        `;
            });

            lista.html(html);
            $('#contadorResponsaveisAdd').text(responsaveisSelecionadosAdd.length);

            // Atualiza o campo hidden com os IDs dos responsáveis
            const ids = responsaveisSelecionadosAdd.map(u => u.id).join(',');
            $('#responsaveisIdsAdd').val(ids);
        }

        // Evento para adicionar responsável
        $(document).on('click', '.btn-adicionar-responsavel', function() {
            const usuarioId = $(this).data('id');
            const usuario = usuariosDisponiveisAdd.find(u => u.id == usuarioId);

            if (usuario && !responsaveisSelecionadosAdd.some(u => u.id == usuarioId)) {
                responsaveisSelecionadosAdd.push(usuario);
                atualizarResponsaveisSelecionadosAdd();
                atualizarListaUsuariosDisponiveisAdd();
            }
        });

        // Evento para remover responsável
        $(document).on('click', '.btn-remover-responsavel', function() {
            const usuarioId = $(this).data('id');
            responsaveisSelecionadosAdd = responsaveisSelecionadosAdd.filter(u => u.id != usuarioId);
            atualizarResponsaveisSelecionadosAdd();
            atualizarListaUsuariosDisponiveisAdd();
        });

        // Buscar usuários ao digitar
        $('#buscarUsuarioAdd').on('input', function() {
            const termo = $(this).val().toLowerCase();
            if (termo === '') {
                atualizarListaUsuariosDisponiveisAdd();
                return;
            }

            const resultados = usuariosDisponiveisAdd.filter(usuario =>
                usuario.username.toLowerCase().includes(termo) ||
                usuario.email.toLowerCase().includes(termo)
            );

            const lista = $('#usuariosDisponiveisAdd');
            lista.empty();

            if (resultados.length === 0) {
                lista.html('<div class="text-center py-3 text-muted">Nenhum resultado encontrado</div>');
                return;
            }

            let html = '';
            resultados.forEach(usuario => {
                const jaSelecionado = responsaveisSelecionadosAdd.some(r => r.id === usuario.id);

                if (!jaSelecionado) {
                    html += `
                <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                    <div>
                        <span class="font-weight-bold">${usuario.username}</span>
                        <small class="d-block text-muted">${usuario.email}</small>
                    </div>
                    <button class="btn btn-sm btn-primary btn-adicionar-responsavel" data-id="${usuario.id}">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            `;
                }
            });

            lista.html(html || '<div class="text-center py-3 text-muted">Nenhum resultado disponível</div>');
        });

        // Carregar usuários quando o modal é aberto
        $('#addAcaoModal').on('show.bs.modal', function() {
            responsaveisSelecionadosAdd = [];
            carregarUsuariosDisponiveisAdd();
            atualizarResponsaveisSelecionadosAdd();
        });

        // Adicione estas variáveis globais no início do script
        let evidenciasAdicionadasAcao = [];
        let evidenciasRemovidasAcao = [];

        // Evento quando o modal de adição de ação é aberto
        $('#addAcaoModal').on('shown.bs.modal', function() {
            // Limpar arrays de evidências
            evidenciasAdicionadasAcao = [];
            evidenciasRemovidasAcao = [];

            // Limpar listas
            $('#evidenciasAtuaisListAdd .list-group').empty();
            $('#evidenciasRemoverListAdd .list-group').empty();

            // Atualizar contadores
            $('#contadorEvidenciasAtuaisAdd').text('0');
            $('#contadorEvidenciasRemoverAdd').text('0');
        });

        // Alternar entre tipos de evidência (texto/link) no modal de adição
        $('input[name="evidencia_tipo_add"]').change(function() {
            if ($(this).val() === 'texto') {
                $('#grupoTextoAdd').removeClass('d-none');
                $('#grupoLinkAdd').addClass('d-none');
            } else {
                $('#grupoTextoAdd').addClass('d-none');
                $('#grupoLinkAdd').removeClass('d-none');
            }
        });

        // Adicionar evidência à lista (apenas localmente) no modal de adição
        $('#btnAdicionarEvidenciaAdd').click(function() {
            var tipo = $('input[name="evidencia_tipo_add"]:checked').val();
            var conteudo = tipo === 'texto' ?
                $('#evidenciaTextoAdd').val().trim() :
                $('#evidenciaLinkAdd').val().trim();
            var descricao = $('#evidenciaDescricaoAdd').val().trim();

            if ((tipo === 'texto' && conteudo.length < 3) ||
                (tipo === 'link' && !isValidUrl(conteudo))) {
                showErrorAlert(tipo === 'texto' ?
                    'O texto da evidência deve ter pelo menos 3 caracteres' :
                    'Por favor, insira uma URL válida');
                return;
            }

            // Criar objeto de evidência
            var novaEvidencia = {
                id: Date.now(), // ID temporário
                tipo: tipo,
                conteudo: conteudo,
                descricao: descricao,
                data: new Date().toLocaleString('pt-BR'),
                acao: 'incluir' // Marcamos como inclusão para a solicitação
            };

            // Adicionar ao array e à lista
            evidenciasAdicionadasAcao.push(novaEvidencia);
            atualizarListaEvidenciasAdicionadasAdd();

            // Limpar campos
            $('#evidenciaTextoAdd, #evidenciaLinkAdd, #evidenciaDescricaoAdd').val('');
            $('#grupoTextoAdd').removeClass('d-none');
            $('#grupoLinkAdd').addClass('d-none');
            $('input[name="evidencia_tipo_add"][value="texto"]').prop('checked', true);
        });

        // Função para atualizar a lista de evidências adicionadas
        function atualizarListaEvidenciasAdicionadasAdd() {
            const lista = $('#evidenciasAtuaisListAdd');
            lista.empty();

            if (evidenciasAdicionadasAcao.length === 0) {
                lista.html('<div class="text-center py-3 text-muted">Nenhuma evidência será adicionada</div>');
                $('#contadorEvidenciasAtuaisAdd').text('0');
                return;
            }

            let html = '<div class="list-group">';

            evidenciasAdicionadasAcao.forEach((evidencia, index) => {
                html += `
        <div class="list-group-item" data-id="${evidencia.id}">
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Nova Evidência #${index + 1}</strong>
                        <small class="text-muted">${evidencia.data}</small>
                    </div>
                    ${evidencia.tipo === 'texto' ?
                        `<div class="bg-light p-2 rounded mb-2">${evidencia.conteudo.substring(0, 50)}${evidencia.conteudo.length > 50 ? '...' : ''}</div>` :
                        `<div class="mb-2"><small class="text-truncate d-block">${evidencia.conteudo}</small></div>`
                    }
                    ${evidencia.descricao ?
                        `<small class="text-muted">${evidencia.descricao.substring(0, 30)}${evidencia.descricao.length > 30 ? '...' : ''}</small>` :
                        ''
                    }
                </div>
                <button class="btn btn-sm btn-danger ml-2 btn-remover-evidencia-add" data-id="${evidencia.id}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>`;
            });

            html += '</div>';
            lista.html(html);
            $('#contadorEvidenciasAtuaisAdd').text(evidenciasAdicionadasAcao.length);
        }

        // Remover evidência da lista de adição
        $(document).on('click', '.btn-remover-evidencia-add', function() {
            const id = $(this).data('id');
            evidenciasAdicionadasAcao = evidenciasAdicionadasAcao.filter(e => e.id != id);
            atualizarListaEvidenciasAdicionadasAdd();
        });

        // Função para validar URL
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        // Modificar o submit do formulário de adição para incluir evidências
        $('#formAddAcao').submit(function(e) {
            e.preventDefault();

            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            // Criar FormData para enviar arquivos se necessário
            const formData = new FormData(this);

            // Adicionar evidências ao FormData
            if (evidenciasAdicionadasAcao.length > 0) {
                formData.append('evidencias_adicionadas', JSON.stringify(evidenciasAdicionadasAcao));
            }

            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#addAcaoModal').modal('hide');
                        showSuccessAlert(response.message || 'Ação cadastrada com sucesso!');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showErrorAlert(response.message || 'Ocorreu um erro ao cadastrar a ação.');
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

        // Gerenciamento de Responsáveis no Modal de Edição
        function carregarResponsaveisAcao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/responsaveis/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#responsaveisAtuaisListAcao').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(usuario => {
                            html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                        <div>
                            <span class="font-weight-bold">${usuario.username}</span>
                            <small class="d-block text-muted">${usuario.email}</small>
                        </div>
                        <button class="btn btn-sm btn-danger btn-remover-responsavel-edit" data-id="${usuario.id}">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                    `;
                        });
                        $('#responsaveisAtuaisListAcao').html(html);
                        $('#contadorResponsaveisAtuaisAcao').text(response.data.length);
                    } else {
                        $('#responsaveisAtuaisListAcao').html('<div class="text-center py-3 text-muted">Nenhum responsável cadastrado</div>');
                        $('#contadorResponsaveisAtuaisAcao').text('0');
                    }
                },
                error: function() {
                    $('#responsaveisAtuaisListAcao').html('<div class="text-center py-3 text-danger">Erro ao carregar responsáveis</div>');
                }
            });
        }

        // Atualize a função carregarUsuariosDisponiveisAcao
        function carregarUsuariosDisponiveisAcao(acaoId, termo = '') {
            $.ajax({
                url: `<?= site_url('acoes/get-usuarios-disponiveis/') ?>${acaoId}`,
                type: 'GET',
                data: {
                    term: termo
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#usuariosDisponiveisListAcao').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(usuario => {
                            html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                        <div>
                            <span class="font-weight-bold">${usuario.username}</span>
                            <small class="d-block text-muted">${usuario.email}</small>
                        </div>
                        <button class="btn btn-sm btn-primary btn-adicionar-responsavel-edit" data-id="${usuario.id}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    `;
                        });
                        $('#usuariosDisponiveisListAcao').html(html);
                        $('#contadorUsuariosDisponiveisAcao').text(response.data.length);
                    } else {
                        $('#usuariosDisponiveisListAcao').html('<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>');
                        $('#contadorUsuariosDisponiveisAcao').text('0');
                    }
                },
                error: function() {
                    $('#usuariosDisponiveisListAcao').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
            });
        }

        // Eventos para o modal de edição
        $(document).on('shown.bs.modal', '#editAcaoModal', function() {
            const acaoId = $('#editAcaoId').val();
            if (acaoId) {
                carregarResponsaveisAcao(acaoId);
                carregarUsuariosDisponiveisAcao(acaoId);
            }
        });

        // Busca de usuários no modal de edição
        $('#buscaUsuarioResponsavelAcao').on('input', function() {
            const acaoId = $('#editAcaoId').val();
            const termo = $(this).val();
            if (acaoId) {
                carregarUsuariosDisponiveisAcao(acaoId, termo);
            }
        });

        // Limpar busca
        $('#btnLimparBuscaResponsaveisAcao').click(function() {
            $('#buscaUsuarioResponsavelAcao').val('');
            const acaoId = $('#editAcaoId').val();
            if (acaoId) {
                carregarUsuariosDisponiveisAcao(acaoId);
            }
        });

        // Adicionar responsável no modal de edição
        $(document).on('click', '.btn-adicionar-responsavel-edit', function() {
            const acaoId = $('#editAcaoId').val();
            const usuarioId = $(this).data('id');

            if (acaoId && usuarioId) {
                $.ajax({
                    url: '<?= site_url("acoes/adicionar-responsavel") ?>',
                    type: 'POST',
                    data: {
                        acao_id: acaoId,
                        usuario_id: usuarioId,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    },
                    success: function(response) {
                        if (response.success) {
                            carregarResponsaveisAcao(acaoId);
                            carregarUsuariosDisponiveisAcao(acaoId);
                        } else {
                            showErrorAlert(response.message);
                        }
                    },
                    error: function() {
                        showErrorAlert('Erro na comunicação com o servidor');
                    },
                    complete: function() {
                        $(this).prop('disabled', false).html('<i class="fas fa-plus"></i>');
                    }
                });
            }
        });

        // Remover responsável no modal de edição
        $(document).on('click', '.btn-remover-responsavel-edit', function() {
            const acaoId = $('#editAcaoId').val();
            const usuarioId = $(this).data('id');

            if (acaoId && usuarioId) {
                $.ajax({
                    url: '<?= site_url("acoes/remover-responsavel") ?>',
                    type: 'POST',
                    data: {
                        acao_id: acaoId,
                        usuario_id: usuarioId,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    },
                    success: function(response) {
                        if (response.success) {
                            carregarResponsaveisAcao(acaoId);
                            carregarUsuariosDisponiveisAcao(acaoId);
                        } else {
                            showErrorAlert(response.message);
                        }
                    },
                    error: function() {
                        showErrorAlert('Erro na comunicação com o servidor');
                    },
                    complete: function() {
                        $(this).prop('disabled', false).html('<i class="fas fa-minus"></i>');
                    }
                });
            }
        });

        // Gerenciamento de Evidências no Modal de Edição
        function carregarEvidenciasAcao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/listar-evidencias/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#evidenciasAtuaisList').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
                },
                success: function(response) {
                    if (response.success && response.evidencias.length > 0) {
                        let html = '';
                        response.evidencias.forEach((evidencia, index) => {
                            const dataFormatada = new Date(evidencia.created_at).toLocaleString('pt-BR', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }).replace(',', '');

                            html += `
                    <div class="list-group-item mb-2" data-id="${evidencia.id}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Evidência #${index + 1}</strong>
                                    <small class="text-muted">${dataFormatada}</small>
                                </div>
                                ${evidencia.tipo === 'texto' ?
                                    `<div class="bg-light p-3 rounded mb-2">${evidencia.evidencia.replace(/\n/g, '<br>')}</div>` :
                                    `<div class="mb-2">
                                        <a href="${evidencia.evidencia}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt mr-2"></i>Abrir Link
                                        </a>
                                        <small class="d-block text-muted mt-1">${evidencia.evidencia}</small>
                                    </div>`
                                }
                                ${evidencia.descricao ?
                                    `<div class="mt-2">
                                        <small class="text-muted d-block"><strong>Descrição:</strong></small>
                                        <div class="bg-light p-2 rounded">${evidencia.descricao.replace(/\n/g, '<br>')}</div>
                                    </div>` : ''
                                }
                            </div>
                            <button class="btn btn-sm btn-danger ml-2 btn-remover-evidencia-edit" data-id="${evidencia.id}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    `;
                        });

                        $('#evidenciasAtuaisList').html(html);
                        $('#contadorEvidenciasAtuais').text(response.evidencias.length);
                    } else {
                        $('#evidenciasAtuaisList').html('<div class="text-center py-3 text-muted">Nenhuma evidência cadastrada</div>');
                        $('#contadorEvidenciasAtuais').text('0');
                    }
                },
                error: function() {
                    $('#evidenciasAtuaisList').html('<div class="text-center py-3 text-danger">Erro ao carregar evidências</div>');
                }
            });
        }

        // Adicionar evidência no modal de edição
        $('#btnAdicionarEvidencia').click(function() {
            const acaoId = $('#editAcaoId').val();
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

            $.ajax({
                url: `<?= site_url('acoes/adicionar-evidencia/') ?>${acaoId}`,
                type: 'POST',
                data: {
                    tipo: tipo,
                    evidencia_texto: tipo === 'texto' ? conteudo : '',
                    evidencia_link: tipo === 'link' ? conteudo : '',
                    descricao: descricao,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                beforeSend: function() {
                    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processando...');
                },
                success: function(response) {
                    if (response.success) {
                        // Limpar campos
                        $('#editAcaoEvidenciaTexto, #editAcaoEvidenciaLink, #editAcaoEvidenciaDescricao').val('');

                        // Recarregar lista de evidências
                        carregarEvidenciasAcao(acaoId);

                        showSuccessAlert('Evidência adicionada com sucesso!');
                    } else {
                        showErrorAlert(response.message);
                    }
                },
                error: function() {
                    showErrorAlert('Erro na comunicação com o servidor');
                },
                complete: function() {
                    $(this).prop('disabled', false).html('<i class="fas fa-plus mr-2"></i> Adicionar à Lista');
                }
            });
        });

        // Remover evidência no modal de edição
        $(document).on('click', '.btn-remover-evidencia-edit', function() {
            const evidenciaId = $(this).data('id');
            const acaoId = $('#editAcaoId').val();

            Swal.fire({
                title: 'Remover evidência?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, remover!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `<?= site_url('acoes/remover-evidencia/') ?>${evidenciaId}`,
                        type: 'POST',
                        data: {
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                carregarEvidenciasAcao(acaoId);
                                showSuccessAlert('Evidência removida com sucesso!');
                            } else {
                                showErrorAlert(response.message);
                            }
                        },
                        error: function() {
                            showErrorAlert('Erro na comunicação com o servidor');
                        }
                    });
                }
            });
        });

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

        // Carregar evidências quando o modal de edição é aberto
        $(document).on('shown.bs.modal', '#editAcaoModal', function() {
            const acaoId = $('#editAcaoId').val();
            if (acaoId) {
                carregarEvidenciasAcao(acaoId);
            }
        });

        // Variáveis globais para controle
        let responsaveisSelecionadosEdit = [];
        let usuariosDisponiveisEdit = [];

        // Carregar responsáveis quando o modal de edição é aberto
        $('#editAcaoModal').on('show.bs.modal', function() {
            const acaoId = $('#editAcaoId').val();
            if (acaoId) {
                carregarResponsaveisAcao(acaoId);
                carregarUsuariosDisponiveisEdit(acaoId);
            }
        });

        // Função para carregar responsáveis atuais
        function carregarResponsaveisAcao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/responsaveis/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#responsaveisSelecionadosEdit').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
                },
                success: function(response) {
                    if (response.success && response.data) {
                        responsaveisSelecionadosEdit = response.data;
                        atualizarResponsaveisSelecionadosEdit();
                    } else {
                        $('#responsaveisSelecionadosEdit').html('<div class="text-center py-3 text-muted">Nenhum responsável cadastrado</div>');
                    }
                },
                error: function() {
                    $('#responsaveisSelecionadosEdit').html('<div class="text-center py-3 text-danger">Erro ao carregar responsáveis</div>');
                }
            });
        }

        // Função para carregar usuários disponíveis
        function carregarUsuariosDisponiveisEdit(acaoId, termo = '') {
            $.ajax({
                url: `<?= site_url('acoes/usuarios-disponiveis/') ?>${acaoId}`,
                type: 'GET',
                data: {
                    term: termo
                },
                beforeSend: function() {
                    $('#usuariosDisponiveisEdit').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
                },
                success: function(response) {
                    if (response.success && response.data) {
                        usuariosDisponiveisEdit = response.data;
                        atualizarListaUsuariosDisponiveisEdit();
                    } else {
                        $('#usuariosDisponiveisEdit').html('<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>');
                    }
                },
                error: function() {
                    $('#usuariosDisponiveisEdit').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
            });
        }

        // Função para atualizar a lista de usuários disponíveis
        function atualizarListaUsuariosDisponiveisEdit() {
            const lista = $('#usuariosDisponiveisEdit');
            lista.empty();

            if (usuariosDisponiveisEdit.length === 0) {
                lista.html('<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>');
                return;
            }

            let html = '';
            usuariosDisponiveisEdit.forEach(usuario => {
                // Verifica se o usuário já está na lista de selecionados
                const jaSelecionado = responsaveisSelecionadosEdit.some(r => r.id === usuario.id);

                if (!jaSelecionado) {
                    html += `
            <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
                <div>
                    <span class="font-weight-bold">${usuario.username}</span>
                    <small class="d-block text-muted">${usuario.email}</small>
                </div>
                <button class="btn btn-sm btn-primary btn-adicionar-responsavel-edit" data-id="${usuario.id}">
                    <i class="fas fa-plus"></i>
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

        // Função para atualizar a lista de responsáveis selecionados
        function atualizarResponsaveisSelecionadosEdit() {
            const lista = $('#responsaveisSelecionadosEdit');
            lista.empty();

            if (responsaveisSelecionadosEdit.length === 0) {
                lista.html('<div class="text-center py-3 text-muted">Nenhum responsável selecionado</div>');
                return;
            }

            let html = '';
            responsaveisSelecionadosEdit.forEach(usuario => {
                html += `
        <div class="list-group-item d-flex justify-content-between align-items-center" data-id="${usuario.id}">
            <div>
                <span class="font-weight-bold">${usuario.username}</span>
                <small class="d-block text-muted">${usuario.email}</small>
            </div>
            <button class="btn btn-sm btn-danger btn-remover-responsavel-edit" data-id="${usuario.id}">
                <i class="fas fa-minus"></i>
            </button>
        </div>
        `;
            });

            lista.html(html);

            // Atualiza o campo hidden com os IDs dos responsáveis
            const ids = responsaveisSelecionadosEdit.map(u => u.id).join(',');
            $('#responsaveisIdsEdit').val(ids);
        }

        // Evento para adicionar responsável
        $(document).on('click', '.btn-adicionar-responsavel-edit', function() {
            const usuarioId = $(this).data('id');
            const usuario = usuariosDisponiveisEdit.find(u => u.id == usuarioId);

            if (usuario && !responsaveisSelecionadosEdit.some(u => u.id == usuarioId)) {
                responsaveisSelecionadosEdit.push(usuario);
                atualizarResponsaveisSelecionadosEdit();
                atualizarListaUsuariosDisponiveisEdit();
            }
        });

        // Evento para remover responsável
        $(document).on('click', '.btn-remover-responsavel-edit', function() {
            const usuarioId = $(this).data('id');
            responsaveisSelecionadosEdit = responsaveisSelecionadosEdit.filter(u => u.id != usuarioId);
            atualizarResponsaveisSelecionadosEdit();
            atualizarListaUsuariosDisponiveisEdit();
        });

        // Buscar usuários ao digitar
        $('#buscarUsuarioEdit').on('input', function() {
            const acaoId = $('#editAcaoId').val();
            const termo = $(this).val();
            if (acaoId) {
                carregarUsuariosDisponiveisEdit(acaoId, termo);
            }
        });

    });
</script>