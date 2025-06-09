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
        let acaoIdEquipe = null;

        // Configuração do DataTables
        function initializeDataTable() {
            return $('#dataTable').DataTable({
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
                },
                "searching": false,
                "responsive": true,
                "autoWidth": false,
                "lengthMenu": [5, 10, 25, 50, 100],
                "pageLength": 10,
                "columns": [{
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
                        "data": "responsavel",
                        "defaultContent": ""
                    },
                    {
                        "data": "id",
                        "render": function(data, type, row) {
                            if (type === 'display') {
                                let equipe = 'Carregando...';
                                $.ajax({
                                    url: `<?= site_url('acoes/get-equipe-formatada/') ?>${data}`,
                                    type: 'GET',
                                    async: false,
                                    success: function(response) {
                                        equipe = response.success ? response.equipe : 'Erro ao carregar';
                                    },
                                    error: function() {
                                        equipe = 'Erro ao carregar';
                                    }
                                });
                                return equipe;
                            }
                            return '';
                        }
                    },
                    {
                        "data": "entrega_estimada",
                        "className": "text-center",
                        "render": function(data) {
                            return data ? formatDate(data) : '';
                        }
                    },
                    {
                        "data": "data_inicio",
                        "className": "text-center",
                        "render": function(data) {
                            return data ? formatDate(data) : '';
                        }
                    },
                    {
                        "data": "data_fim",
                        "className": "text-center",
                        "render": function(data) {
                            return data ? formatDate(data) : '';
                        }
                    },
                    {
                        "data": "status",
                        "className": "text-center",
                        "render": function(data) {
                            if (!data) data = 'Não iniciado';
                            const badgeClass = {
                                'Finalizado': 'badge-success',
                                'Em andamento': 'badge-primary',
                                'Paralisado': 'badge-danger',
                                'Não iniciado': 'badge-secondary'
                            } [data] || 'badge-secondary';

                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    {
                        "data": null,
                        "className": "text-center",
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
                "data": <?= json_encode(array_map(function ($acao) use ($acessoDireto, $etapa) {
                            return [
                                'id' => $acao['id'],
                                'nome' => $acao['nome'],
                                'etapa' => !$acessoDireto ? ($etapa['nome'] ?? '') : '',
                                'responsavel' => $acao['responsavel'] ?? '',
                                'equipe' => $acao['equipe'] ?? '',
                                'entrega_estimada' => $acao['entrega_estimada'] ?? null,
                                'data_inicio' => $acao['data_inicio'] ?? null,
                                'data_fim' => $acao['data_fim'] ?? null,
                                'status' => $acao['status'] ?? 'Não iniciado',
                                'ordem' => $acao['ordem'] ?? 0
                            ];
                        }, $acoes ?? [])) ?>
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

                        setDateValue('EntregaEstimada', acao.entrega_estimada);
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

        // Verificar alterações no modal de edição
        $('#solicitarEdicaoModal').on('shown.bs.modal', function() {
            const acaoId = $('#solicitarEdicaoId').val();
            if (acaoId) {
                carregarEquipeParaSolicitacao(acaoId);
                carregarUsuariosDisponiveisParaSolicitacao(acaoId);
            }

            // Limpa a busca
            $('#buscaUsuarioEquipe').val('').trigger('input');
        });

        function checkForChanges() {
            let hasChanges = false;
            const form = $('#formSolicitarEdicao');

            ['nome', 'responsavel', 'equipe', 'status', 'tempo_estimado_dias',
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
            // Destruir a tabela existente
            if ($.fn.DataTable.isDataTable('#dataTable')) {
                dataTable.destroy();
            }

            // Limpar o corpo da tabela
            $('#dataTable tbody').empty();

            if (acoes.length === 0) {
                const colCount = acessoDireto ? 9 : 10; // Ajustado para coluna oculta
                $('#dataTable tbody').append(`
                            <tr>
                                <td colspan="${colCount}" class="text-center">Nenhuma ação encontrada com os filtros aplicados</td>
                            </tr>
                        `);
            } else {
                // Reconstruir a tabela com os dados filtrados
                dataTable = initializeDataTable();
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

        // Inicializar ordens originais quando o modal é aberto
        $('#ordenarAcoesModal').on('shown.bs.modal', function() {
            $('.ordem-select').each(function() {
                $(this).data('original', $(this).val());
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

        // Configuração do modal de equipe com Select2
        $('#equipeAcaoModal').on('shown.bs.modal', function() {

            // Destrua qualquer instância existente
            if ($('#selectUsuarioEquipe').hasClass('select2-hidden-accessible')) {
                $('#selectUsuarioEquipe').select2('destroy');
            }

            // Inicialize o Select2
            $('#selectUsuarioEquipe').select2({
                placeholder: "Digite para buscar usuários...",
                minimumInputLength: 2,
                width: '100%',
                ajax: {
                    url: '<?= site_url("acoes/buscar-usuarios") ?>',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        console.log('Buscando usuários com termo:', params.term);
                        return {
                            term: params.term,
                            acao_id: acaoIdEquipe,
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        };
                    },
                    processResults: function(data, params) {
                        console.log('Resultados recebidos:', data);
                        return {
                            results: data.results || []
                        };
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Erro na busca:', textStatus, errorThrown);
                    }
                }
            }).on('select2:open', function() {
                console.log('Select2 aberto');
            });
        });

        // Botão "Ver Equipe"
        $(document).on('click', '#btnVerEquipe', function(e) {
            e.preventDefault();
            acaoIdEquipe = $('#editAcaoId').val();

            if (!acaoIdEquipe) {
                showErrorAlert('ID da ação não encontrado');
                return;
            }

            $('#editAcaoModal').modal('hide').on('hidden.bs.modal', function() {
                $(this).off('hidden.bs.modal');
                $('#equipeAcaoModal').modal('show');
                carregarEquipeAcao(acaoIdEquipe);
                carregarUsuariosDisponiveis(acaoIdEquipe);
            });
        });

        // Carregar usuários disponíveis para o select
        function carregarUsuariosDisponiveis(acaoId) {
            $.ajax({
                url: '<?= site_url("acoes/buscar-usuarios") ?>',
                type: 'GET',
                data: {
                    acao_id: acaoId
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#selectUsuarioEquipe').html('<option value="">Carregando usuários...</option>');
                },
                success: function(response) {
                    console.log('Resposta completa:', response); // Adicione este log
                    if (response.success && response.data && response.data.length > 0) {
                        let options = '<option value="">Selecione um usuário</option>';
                        response.data.forEach(usuario => {
                            options += `<option value="${usuario.id}">${usuario.username} (${usuario.email})</option>`;
                        });
                        $('#selectUsuarioEquipe').html(options);
                    } else {
                        console.log('Resposta sem dados:', response.message); // Adicione este log
                        $('#selectUsuarioEquipe').html('<option value="">Nenhum usuário disponível</option>');
                        if (response.message) {
                            showErrorAlert(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Erro completo:', xhr.responseText); // Adicione este log
                    $('#selectUsuarioEquipe').html('<option value="">Erro ao carregar usuários</option>');
                    showErrorAlert('Erro ao carregar lista de usuários: ' + xhr.statusText);
                }
            });
        }

        // Função para carregar a equipe
        function carregarEquipeAcao(acaoId) {
            $.ajax({
                url: `<?= site_url("acoes/get-equipe/") ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#tabelaEquipeAcao tbody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando...</td></tr>');
                },
                success: function(response) {
                    const tbody = $('#tabelaEquipeAcao tbody');
                    tbody.empty();

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(membro => {
                            tbody.append(`
                                    <tr data-usuario-id="${membro.id}">
                                        <td>${membro.username}</td>
                                        <td>${membro.email}</td>
                                        <td class="text-center">
                                            <button class="btn btn-danger btn-sm btn-remover-equipe" data-usuario-id="${membro.id}">
                                                <i class="fas fa-trash-alt"></i> Remover
                                            </button>
                                        </td>
                                    </tr>
                                `);
                        });
                    } else {
                        tbody.append('<tr><td colspan="3" class="text-center">Nenhum membro na equipe</td></tr>');
                    }
                },
                error: function(xhr) {
                    console.error('Erro ao carregar equipe:', xhr.responseText);
                    $('#tabelaEquipeAcao tbody').html('<tr><td colspan="3" class="text-center text-danger">Erro ao carregar equipe</td></tr>');
                }
            });
        }

        // Adicionar usuário à equipe
        $('#btnAdicionarUsuarioEquipe').click(function() {
            const usuarioId = $('#selectUsuarioEquipe').val();
            if (!usuarioId || !acaoIdEquipe) {
                showErrorAlert('Selecione um usuário válido');
                return;
            }

            $.ajax({
                url: '<?= site_url("acoes/adicionar-membro-equipe") ?>',
                type: 'POST',
                data: {
                    acao_id: acaoIdEquipe,
                    usuario_id: usuarioId,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#btnAdicionarUsuarioEquipe').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        $('#selectUsuarioEquipe').val(null).trigger('change');
                        carregarEquipeAcao(acaoIdEquipe);
                        carregarUsuariosDisponiveis(acaoIdEquipe);
                        showSuccessAlert(response.message);
                    } else {
                        showErrorAlert(response.message);
                    }
                },
                error: function() {
                    showErrorAlert('Erro na comunicação com o servidor');
                },
                complete: function() {
                    $('#btnAdicionarUsuarioEquipe').prop('disabled', false);
                }


            });
        });

        // Remover usuário da equipe
        $(document).on('click', '.btn-remover-equipe', function() {
            const usuarioId = $(this).data('usuario-id');
            if (!usuarioId || !acaoIdEquipe) return;

            Swal.fire({
                title: 'Remover usuário da equipe?',
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
                        url: '<?= site_url("acoes/remover-membro-equipe") ?>',
                        type: 'POST',
                        data: {
                            acao_id: acaoIdEquipe,
                            usuario_id: usuarioId,
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                carregarEquipeAcao(acaoIdEquipe);
                                carregarUsuariosDisponiveis(acaoIdEquipe); // Adicione esta linha
                                showSuccessAlert(response.message);
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

        // Função genérica para enviar formulários
        function submitForm(form, modalId, successMessage = null) {
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            // Verificação adicional para formulário de exclusão
            if (form.attr('id') === 'formDeleteAcao') {
                const acaoId = form.find('input[name="id"]').val();
                if (!acaoId) {
                    showErrorAlert('ID da ação não especificado');
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    return;
                }
            }

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

        function carregarUsuariosDisponiveis(acaoId) {
            $.ajax({
                url: '<?= site_url("acoes/buscar-usuarios") ?>',
                type: 'GET',
                data: {
                    acao_id: acaoId
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#selectUsuarioEquipe').html('<option value="">Carregando usuários...</option>');
                },
                success: function(response) {
                    todosUsuarios = response.data || [];

                    if (todosUsuarios.length > 0) {
                        let options = '';
                        todosUsuarios.forEach(usuario => {
                            options += `<option value="${usuario.id}">${usuario.username} (${usuario.email})</option>`;
                        });
                        $('#selectUsuarioEquipe').html(options);
                        $('#buscaUsuario').val('').trigger('input'); // Limpar filtro
                    } else {
                        $('#selectUsuarioEquipe').html('<option value="">Nenhum usuário disponível</option>');
                    }
                },
                error: function() {
                    $('#selectUsuarioEquipe').html('<option value="">Erro ao carregar usuários</option>');
                    showErrorAlert('Erro ao carregar lista de usuários');
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
            const acaoId = form.find('input[name="acao_id"]').val();
            const submitBtn = form.find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            const tipoSelecionado = form.find('input[name="tipo"]:checked').val(); // Captura o tipo selecionado

            submitBtn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm" role="status"></span> Enviando...');

            $.ajax({
                url: `<?= site_url('acoes/adicionar-evidencia/') ?>${acaoId}`,
                type: 'POST',
                data: form.serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Reset mais inteligente que mantém o tipo selecionado
                        form.trigger('reset');
                        form.find(`input[name="tipo"][value="${tipoSelecionado}"]`).prop('checked', true);

                        // Atualiza a visibilidade dos campos baseado no tipo selecionado
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

                        // Atualiza a lista diretamente com a resposta
                        adicionarEvidenciaNaLista(response.evidencia, response.totalEvidencias);

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

        function adicionarEvidenciaNaLista(evidencia, totalEvidencias) {
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
        // Adicione esta função no seu arquivo JavaScript (scripts/acoes.php)
        function carregarEquipeParaSolicitacao(acaoId) {
            $.ajax({
                url: `<?= site_url('acoes/get-equipe/') ?>${acaoId}`,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $('#equipeAtualList').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>');
                },
                success: function(response) {
                    const equipeAtualList = $('#equipeAtualList');
                    equipeAtualList.empty();

                    if (response.data && response.data.length > 0) {
                        response.data.forEach(membro => {
                            equipeAtualList.append(`
                        <div class="list-group-item py-2 d-flex justify-content-between align-items-center" data-usuario-id="${membro.id}">
                            <div>
                                <span class="font-weight-bold">${membro.username}</span>
                                <small class="d-block text-muted">${membro.email}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger btn-remover-equipe-solicitacao" data-usuario-id="${membro.id}">
                                <i class="fas fa-user-minus"></i>
                            </button>
                        </div>
                    `);
                        });
                    } else {
                        equipeAtualList.html('<div class="text-center py-3 text-muted">Nenhum membro na equipe</div>');
                    }
                },
                error: function() {
                    $('#equipeAtualList').html('<div class="text-center py-3 text-danger">Erro ao carregar equipe</div>');
                }
            });
        }

        function carregarUsuariosDisponiveisParaSolicitacao(acaoId) {
            $.ajax({
                url: '<?= site_url('acoes/buscar-usuarios') ?>',
                type: 'GET',
                data: {
                    acao_id: acaoId
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#usuariosDisponiveisList').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>');
                },
                success: function(response) {
                    const usuariosList = $('#usuariosDisponiveisList');
                    usuariosList.empty();

                    if (response.success && response.results && response.results.length > 0) {
                        response.results.forEach(usuario => {
                            usuariosList.append(`
                        <div class="list-group-item py-2 d-flex justify-content-between align-items-center" data-usuario-id="${usuario.id}">
                            <div>
                                <span class="font-weight-bold">${usuario.username}</span>
                                <small class="d-block text-muted">${usuario.email}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-success btn-adicionar-equipe-solicitacao" data-usuario-id="${usuario.id}">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                    `);
                        });
                    } else {
                        usuariosList.html('<div class="text-center py-3 text-muted">Nenhum usuário disponível</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição:', status, error);
                    $('#usuariosDisponiveisList').html('<div class="text-center py-3 text-danger">Erro ao carregar usuários</div>');
                }
            });
        }

        // Adicione este evento para carregar os dados quando o modal for aberto
        $('#solicitarEdicaoModal').on('shown.bs.modal', function() {
            const acaoId = $('#solicitarEdicaoId').val();
            if (acaoId) {
                carregarEquipeParaSolicitacao(acaoId);
                carregarUsuariosDisponiveisParaSolicitacao(acaoId);
            }
        });

        // Adicione esta função para filtrar usuários
        $('#buscaUsuarioEquipe').on('input', function() {
            const termo = $(this).val().toLowerCase();
            if (termo.length < 2) {
                // Mostra todos se o termo for muito curto
                $('#usuariosDisponiveisList .list-group-item').show();
                return;
            }

            $('#usuariosDisponiveisList .list-group-item').each(function() {
                const texto = $(this).text().toLowerCase();
                $(this).toggle(texto.includes(termo));
            });
        });

        // Adicione estes eventos para os botões de adicionar/remover
        $(document).on('click', '.btn-adicionar-equipe-solicitacao', function() {
            const usuarioId = $(this).data('usuario-id');
            const usuarioItem = $(this).closest('.list-group-item');
            const usuarioHtml = usuarioItem.html();

            // Move para a lista de membros atuais
            $('#equipeAtualList').append(usuarioItem);
            $(this).removeClass('btn-outline-success btn-adicionar-equipe-solicitacao')
                .addClass('btn-outline-danger btn-remover-equipe-solicitacao')
                .html('<i class="fas fa-user-minus"></i>');

            // Atualiza o campo hidden com os usuários a adicionar
            const adicionarAtual = $('#adicionarMembroInput').val();
            const adicionarArray = adicionarAtual ? adicionarAtual.split(',') : [];
            if (!adicionarArray.includes(usuarioId.toString())) {
                adicionarArray.push(usuarioId);
                $('#adicionarMembroInput').val(adicionarArray.join(','));
            }

            // Remove do campo de remoção se estava lá
            const removerAtual = $('#removerMembroInput').val();
            if (removerAtual) {
                const removerArray = removerAtual.split(',');
                const index = removerArray.indexOf(usuarioId.toString());
                if (index > -1) {
                    removerArray.splice(index, 1);
                    $('#removerMembroInput').val(removerArray.join(','));
                }
            }

            checkForChanges();
        });

        $(document).on('click', '.btn-remover-equipe-solicitacao', function() {
            const usuarioId = $(this).data('usuario-id');
            const usuarioItem = $(this).closest('.list-group-item');
            const usuarioHtml = usuarioItem.html();

            // Move para a lista de usuários disponíveis
            $('#usuariosDisponiveisList').append(usuarioItem);
            $(this).removeClass('btn-outline-danger btn-remover-equipe-solicitacao')
                .addClass('btn-outline-success btn-adicionar-equipe-solicitacao')
                .html('<i class="fas fa-user-plus"></i>');

            // Atualiza o campo hidden com os usuários a remover
            const removerAtual = $('#removerMembroInput').val();
            const removerArray = removerAtual ? removerAtual.split(',') : [];
            if (!removerArray.includes(usuarioId.toString())) {
                removerArray.push(usuarioId);
                $('#removerMembroInput').val(removerArray.join(','));
            }

            // Remove do campo de adição se estava lá
            const adicionarAtual = $('#adicionarMembroInput').val();
            if (adicionarAtual) {
                const adicionarArray = adicionarAtual.split(',');
                const index = adicionarArray.indexOf(usuarioId.toString());
                if (index > -1) {
                    adicionarArray.splice(index, 1);
                    $('#adicionarMembroInput').val(adicionarArray.join(','));
                }
            }

            checkForChanges();
        });
    });
</script>