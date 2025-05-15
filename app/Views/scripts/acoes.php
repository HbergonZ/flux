<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Variáveis globais
        let dataTable;
        let acessoDireto = <?= isset($acessoDireto) && $acessoDireto ? 'true' : 'false' ?>;
        let etapaNome = '<?= isset($etapa) ? $etapa["nome"] : "" ?>';
        let formOriginalData = {};

        // Inicializa o DataTable
        function initializeDataTable() {
            return $('#dataTable').DataTable({
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
                "pageLength": 10,
                "order": [
                    [0, 'asc']
                ]
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

        // Handler para o formulário de inclusão
        $('#formAddAcao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#addAcaoModal', 'Ação cadastrada com sucesso!');
        });

        // Carregar próxima ordem ao abrir o modal de adição
        $('#addAcaoModal').on('show.bs.modal', function() {
            calcularProximaOrdem();
        });

        // Função para calcular a próxima ordem
        function calcularProximaOrdem() {
            var maxOrdem = 0;

            $('#dataTable tbody tr').each(function() {
                var ordemText = $(this).find('td:first').text().trim();
                var ordem = parseInt(ordemText) || 0;

                if (ordem > maxOrdem) {
                    maxOrdem = ordem;
                }
            });

            $('#acaoOrdem').val(maxOrdem + 1);
            $('#solicitarInclusaoOrdem').val(maxOrdem + 1);
        }

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

                        $(`#${prefix}Id`).val(acao.id_acao);
                        $(`#${prefix}Nome`).val(acao.nome);
                        $(`#${prefix}Responsavel`).val(acao.responsavel);
                        $(`#${prefix}Equipe`).val(acao.equipe);
                        $(`#${prefix}Status`).val(acao.status || 'Não iniciado');
                        $(`#${prefix}TempoEstimado`).val(acao.tempo_estimado_dias);

                        // Tratamento de datas
                        const setDateValue = (field, value) => {
                            if (value) {
                                const datePart = value.split(' ')[0];
                                $(`#${prefix}${field}`).val(isValidDate(datePart) ? datePart : '');
                            } else {
                                $(`#${prefix}${field}`).val('');
                            }
                        };

                        setDateValue('InicioEstimado', acao.inicio_estimado);
                        setDateValue('FimEstimado', acao.fim_estimado);
                        setDateValue('DataInicio', acao.data_inicio);
                        setDateValue('DataFim', acao.data_fim);

                        $(`#${prefix}Ordem`).val(acao.ordem);
                        $(modalId).modal('show');

                        if (modalId === '#solicitarEdicaoModal') {
                            // Armazena os dados originais para comparação
                            formOriginalData = {
                                nome: acao.nome,
                                responsavel: acao.responsavel,
                                equipe: acao.equipe,
                                status: acao.status || 'Não iniciado',
                                tempo_estimado_dias: acao.tempo_estimado_dias,
                                inicio_estimado: acao.inicio_estimado ? acao.inicio_estimado.split(' ')[0] : '',
                                fim_estimado: acao.fim_estimado ? acao.fim_estimado.split(' ')[0] : '',
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

        // Verificar alterações no modal de edição
        $('#solicitarEdicaoModal').on('shown.bs.modal', function() {
            $('#formSolicitarEdicao').on('input change', checkForChanges);
        });

        function checkForChanges() {
            let hasChanges = false;
            const form = $('#formSolicitarEdicao');

            ['nome', 'responsavel', 'equipe', 'status', 'tempo_estimado_dias',
                'inicio_estimado', 'fim_estimado', 'data_inicio', 'data_fim', 'ordem'
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

        // Enviar solicitação de edição
        $('#formSolicitarEdicao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarEdicaoModal', 'Solicitação de edição enviada com sucesso!');
        });

        // Solicitar exclusão de ação - Abrir modal (para não-admins)
        $(document).on('click', '.btn-danger[title="Solicitar Exclusão"]', function() {
            var acaoId = $(this).data('id').split('-')[0];
            var acaoName = $(this).closest('tr').find('td:nth-child(2)').text();

            loadAcaoForRequest(acaoId, acaoName, '#solicitarExclusaoModal');
        });

        // Excluir ação - Abrir modal de confirmação (apenas admin)
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var acaoId = $(this).data('id').split('-')[0];
            var acaoName = $(this).closest('tr').find('td:nth-child(2)').text();

            $('#deleteAcaoId').val(acaoId);
            $('#acaoNameToDelete').text(acaoName);
            $('#deleteAcaoModal').modal('show');
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
                        const dadosAtuais = `Nome: ${acao.nome}\nResponsável: ${acao.responsavel}\nEquipe: ${acao.equipe}\nStatus: ${acao.status}\nEtapa: ${acao.id_etapa}\nProjeto: ${acao.id_projeto}`;

                        $('#solicitarExclusaoId').val(acao.id_acao);
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
            submitForm($(this), '#deleteAcaoModal');
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
            const hasFilters = $('#formFiltros').find('input, select').toArray().some(el => {
                const $el = $(el);
                return ($el.val() !== '' && $el.val() !== null) ||
                    ($el.is('select') && $el.val() !== '');
            });

            if (!hasFilters) {
                location.reload();
                return;
            }

            $.ajax({
                type: "POST",
                url: `<?= site_url("acoes/filtrar/$idOrigem/$tipoOrigem") ?>`,
                data: $('#formFiltros').serialize(),
                dataType: "json",
                beforeSend: function() {
                    $('#dataTable').css('opacity', '0.5');
                },
                success: function(response) {
                    if (response.success) {
                        updateTableWithFilteredData(response.data);
                    } else {
                        showErrorAlert('Erro ao filtrar ações: ' + response.message);
                    }
                },
                error: function(xhr) {
                    showErrorAlert('Erro na requisição.');
                },
                complete: function() {
                    $('#dataTable').css('opacity', '1');
                }
            });
        }

        // Atualizar tabela com dados filtrados
        function updateTableWithFilteredData(acoes) {
            dataTable.destroy();
            $('#dataTable tbody').empty();

            if (acoes.length === 0) {
                const colCount = acessoDireto ? 7 : 8;
                $('#dataTable tbody').append(`
                    <tr>
                        <td colspan="${colCount}" class="text-center">Nenhuma ação encontrada com os filtros aplicados</td>
                    </tr>
                `);
            } else {
                $.each(acoes, function(index, acao) {
                    const id = acao.id_acao + '-' + acao.nome.toLowerCase().replace(/\s+/g, '-');
                    const isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;

                    // Determina a classe do badge de status
                    let statusBadge = 'badge-secondary';
                    switch (acao.status) {
                        case 'Finalizado':
                            statusBadge = 'badge-success';
                            break;
                        case 'Em andamento':
                            statusBadge = 'badge-primary';
                            break;
                        case 'Paralisado':
                            statusBadge = 'badge-danger';
                            break;
                    }

                    // Monta a linha da tabela
                    const row = `
                    <tr>
                        <td class="text-center">${acao.ordem || ''}</td>
                        <td class="text-wrap">${acao.nome}</td>
                        ${(!acessoDireto) ? `<td>${etapaNome}</td>` : ''}
                        <td>${acao.responsavel || ''}</td>
                        <td class="text-center">
                            <span class="badge ${statusBadge}">
                                ${acao.status || 'Não iniciado'}
                            </span>
                        </td>
                        <td class="text-center">${acao.data_inicio ? formatDate(acao.data_inicio) : ''}</td>
                        <td class="text-center">${acao.data_fim ? formatDate(acao.data_fim) : ''}</td>
                        <td class="text-center">
                            <div class="d-inline-flex">
                                ${isAdmin ? `
                                    <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Excluir">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                ` : `
                                    <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Solicitar Edição">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${id}" title="Solicitar Exclusão">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                `}
                            </div>
                        </td>
                    </tr>`;

                    $('#dataTable tbody').append(row);
                });
            }

            // Re-inicializa o DataTable
            dataTable = initializeDataTable();

            // Recalcula a ordem se o modal de adição estiver aberto
            if ($('#addAcaoModal').hasClass('show')) {
                calcularProximaOrdem();
            }
        }

        // Função genérica para enviar formulários
        function submitForm(form, modalId, successMessage = null) {
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            // Limpar datas inválidas antes do envio
            form.find('input[type="date"]').each(function() {
                if (this.value && !isValidDate(this.value)) {
                    this.value = '';
                }
            });

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
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
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
    });
</script>