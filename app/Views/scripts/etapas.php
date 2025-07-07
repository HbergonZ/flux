<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializa o DataTable com configuração mais robusta
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
            "searching": false,
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
            "serverSide": false,
            "ajax": {
                "url": `<?= site_url("etapas/carregar-etapas/{$idProjeto}") ?>`,
                "type": "GET",
                "dataSrc": function(json) {
                    return json.data || [];
                }
            },
            "columns": [{
                    "data": "ordem",
                    "className": "text-center"
                },
                {
                    "data": "nome",
                    "className": "text-wrap"
                },
                {
                    "data": "data_criacao",
                    "className": "text-center",
                    "render": function(data) {
                        return data ? formatDate(data) : '-';
                    }
                },
                {
                    "data": "data_atualizacao",
                    "className": "text-center",
                    "render": function(data) {
                        return data ? formatDate(data) : '-';
                    }
                },
                {
                    "data": null,
                    "className": "text-center",
                    "orderable": false,
                    "render": function(data, type, row) {
                        return `
                            <div class="progress-container" data-etapa-id="${row.id}" title="Carregando progresso...">
                                <div class="progress progress-sm">
                                    <div class="progress-bar progress-bar-striped bg-secondary"
                                        role="progressbar" style="width: 0%"
                                        aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="progress-text">0%</small>
                            </div>`;
                    }
                },
                {
                    "data": null,
                    "className": "text-center",
                    "orderable": false,
                    "render": function(data, type, row) {
                        const isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
                        let buttons = `
                            <div class="d-inline-flex">
                                <a href="<?= site_url('etapas/') ?>${row.id}/acoes" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar Ações">
                                    <i class="fas fa-th-list"></i>
                                </a>`;

                        if (isAdmin) {
                            buttons += `
                                <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${row.id_slug}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${row.id_slug}" title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>`;
                        } else {
                            buttons += `
                                <button type="button" class="btn btn-primary btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${row.id_slug}" title="Solicitar Edição">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm mx-1" style="width: 32px; height: 32px;" data-id="${row.id_slug}" title="Solicitar Exclusão">
                                    <i class="fas fa-trash-alt"></i>
                                </button>`;
                        }

                        buttons += `</div>`;
                        return buttons;
                    }
                }
            ],
            "drawCallback": function(settings) {
                carregarProgressoEtapas();
                // Reatribui os eventos dos botões após o desenho da tabela
                atribuirEventosBotoes();
            }
        });

        // Configuração do AJAX
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        });

        // Armazenar dados originais do formulário
        let formOriginalData = {};

        // Atribuir eventos aos botões
        function atribuirEventosBotoes() {
            // Event listener para a troca de ordens
            $(document).off('change', '.ordem-select').on('change', '.ordem-select', function() {
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
            $('#ordenarEtapasModal').off('shown.bs.modal').on('shown.bs.modal', function() {
                $('.ordem-select').each(function() {
                    $(this).data('original', $(this).val());
                });
            });

            // Carregar próxima ordem ao abrir o modal de adição
            $('#addEtapaModal').off('show.bs.modal').on('show.bs.modal', function() {
                $.ajax({
                    url: '<?= site_url("etapas/proxima-ordem/$idProjeto") ?>',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#etapaOrdem').val(response.proximaOrdem);
                        } else {
                            // Fallback: calcular no cliente
                            var maxOrdem = 0;
                            dataTable.rows().every(function() {
                                var ordem = parseInt(this.data().ordem) || 0;
                                if (ordem > maxOrdem) {
                                    maxOrdem = ordem;
                                }
                            });
                            $('#etapaOrdem').val(maxOrdem + 1);
                        }
                    },
                    error: function() {
                        // Fallback: calcular no cliente
                        var maxOrdem = 0;
                        dataTable.rows().every(function() {
                            var ordem = parseInt(this.data().ordem) || 0;
                            if (ordem > maxOrdem) {
                                maxOrdem = ordem;
                            }
                        });
                        $('#etapaOrdem').val(maxOrdem + 1);
                    }
                });
            });

            // Cadastrar nova etapa (apenas admin)
            $('#formAddEtapa').off('submit').on('submit', function(e) {
                e.preventDefault();
                submitForm($(this), '#addEtapaModal');
            });

            // Editar etapa - Abrir modal (apenas admin)
            $(document).off('click', '.btn-primary[title="Editar"]').on('click', '.btn-primary[title="Editar"]', function() {
                var etapaId = $(this).data('id').split('-')[0];

                $.ajax({
                    url: '<?= site_url('etapas/editar/') ?>' + etapaId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            $('#editEtapaId').val(response.data.id);
                            $('#editEtapaNome').val(response.data.nome);
                            $('#editEtapaOrdem').val(response.data.ordem);

                            // Armazena os valores originais para comparação
                            formOriginalData = {
                                nome: response.data.nome,
                                ordem: response.data.ordem
                            };

                            $('#editEtapaModal').modal('show');
                        } else {
                            showErrorAlert(response.message || "Erro ao carregar etapa");
                        }
                    },
                    error: function(xhr, status, error) {
                        showErrorAlert("Falha na comunicação com o servidor.");
                    }
                });
            });

            // Solicitar edição de etapa - Abrir modal (para não-admins)
            $(document).off('click', '.btn-primary[title="Solicitar Edição"]').on('click', '.btn-primary[title="Solicitar Edição"]', function() {
                var etapaId = $(this).data('id').split('-')[0];
                var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
                var url = isAdmin ? '<?= site_url('etapas/editar/') ?>' : '<?= site_url('etapas/dados-etapa/') ?>';

                $.ajax({
                    url: url + etapaId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            var etapa = response.data;

                            // Preenche os campos do formulário
                            $('#solicitarEdicaoId').val(etapa.id);
                            $('#solicitarEdicaoNome').val(etapa.nome);
                            $('#solicitarEdicaoOrdem').val(etapa.ordem);

                            // Armazena os valores originais para comparação
                            formOriginalData = {
                                nome: etapa.nome,
                                ordem: etapa.ordem
                            };

                            $('#solicitarEdicaoModal').modal('show');
                            $('#alertNenhumaAlteracao').addClass('d-none');
                        } else {
                            showErrorAlert(response.message || "Erro ao carregar etapa");
                        }
                    },
                    error: function(xhr, status, error) {
                        showErrorAlert("Falha na comunicação com o servidor.");
                    }
                });
            });

            // Verificar alterações em tempo real no modal de edição
            $('#solicitarEdicaoModal').off('shown.bs.modal').on('shown.bs.modal', function() {
                $('#formSolicitarEdicao').off('input change').on('input change', function() {
                    checkForChanges();
                });
            });

            // Solicitar exclusão de etapa - Abrir modal (para não-admins)
            $(document).off('click', '.btn-danger[title="Solicitar Exclusão"]').on('click', '.btn-danger[title="Solicitar Exclusão"]', function() {
                var etapaId = $(this).data('id').split('-')[0];
                var etapaName = $(this).closest('tr').find('td:nth-child(2)').text();
                var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;
                var url = isAdmin ? '<?= site_url('etapas/editar/') ?>' : '<?= site_url('etapas/dados-etapa/') ?>';

                $.ajax({
                    url: url + etapaId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            var etapa = response.data;
                            var dadosAtuais = `Nome: ${etapa.nome}\nOrdem: ${etapa.ordem}\nProjeto: ${etapa.id_projeto}`;

                            $('#solicitarExclusaoId').val(etapa.id);
                            $('#etapaNameToRequestDelete').text(etapaName);
                            $('#solicitarExclusaoDadosAtuais').val(dadosAtuais);
                            $('#solicitarExclusaoModal').modal('show');
                        } else {
                            showErrorAlert(response.message || "Erro ao carregar etapa");
                        }
                    },
                    error: function(xhr, status, error) {
                        showErrorAlert("Falha na comunicação com o servidor.");
                    }
                });
            });

            // Modal de confirmação de exclusão com verificação de relacionamentos
            $(document).off('click', '.btn-danger[title="Excluir"]').on('click', '.btn-danger[title="Excluir"]', function() {
                var btn = $(this);
                var etapaId = btn.data('id').split('-')[0];
                var etapaName = btn.closest('tr').find('td:nth-child(2)').text();

                // Verificar se há relacionamentos
                $.ajax({
                    url: '<?= site_url('etapas/verificar-relacionamentos/') ?>' + etapaId,
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                    },
                    success: function(response) {
                        if (response.success) {
                            var totalAcoes = response.contagem.acoes;

                            if (totalAcoes > 0) {
                                // Exibir alerta de confirmação com detalhes
                                Swal.fire({
                                    title: 'Atenção! Exclusão em Cascata',
                                    html: `Você está prestes a excluir a etapa <strong>${etapaName}</strong> e <strong>TODAS</strong> as suas ações vinculadas:<br><br>
                                       <ul class="text-left">
                                           <li>Ações: ${response.contagem.acoes}</li>
                                       </ul>
                                       <p class="text-danger mt-2"><strong>Esta ação é irreversível!</strong></p>`,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Sim, excluir tudo!',
                                    cancelButtonText: 'Cancelar',
                                    width: '600px'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#deleteEtapaId').val(etapaId);
                                        $('#etapaNameToDelete').text(etapaName);
                                        $('#deleteEtapaModal').modal('show');
                                    }
                                });
                            } else {
                                // Exibir confirmação simples se não houver relacionamentos
                                Swal.fire({
                                    title: 'Confirmar Exclusão',
                                    html: `Tem certeza que deseja excluir a etapa <strong>${etapaName}</strong>?`,
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Sim, excluir!',
                                    cancelButtonText: 'Cancelar'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $('#deleteEtapaId').val(etapaId);
                                        $('#etapaNameToDelete').text(etapaName);
                                        $('#deleteEtapaModal').modal('show');
                                    }
                                });
                            }
                        } else {
                            Swal.fire('Erro', response.message || 'Erro ao verificar relacionamentos', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Erro', 'Falha na comunicação com o servidor', 'error');
                    }
                });
            });
        }

        // Chamar a função para atribuir eventos pela primeira vez
        atribuirEventosBotoes();

        function checkForChanges() {
            let hasChanges = false;
            const form = $('#formSolicitarEdicao');

            ['nome', 'ordem'].forEach(field => {
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
        $('#formSolicitarEdicao').off('submit').on('submit', function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarEdicaoModal', 'Solicitação de edição enviada com sucesso!');
        });

        // Enviar solicitação de exclusão
        $('#formSolicitarExclusao').off('submit').on('submit', function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarExclusaoModal', 'Solicitação de exclusão enviada com sucesso!');
        });

        // Enviar solicitação de inclusão
        $('#formSolicitarInclusao').off('submit').on('submit', function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarInclusaoModal', 'Solicitação de inclusão enviada com sucesso!');
        });

        // Atualizar etapa (apenas admin)
        $('#formEditEtapa').off('submit').on('submit', function(e) {
            e.preventDefault();
            submitForm($(this), '#editEtapaModal');
        });

        // Processar exclusão após confirmação
        $('#formDeleteEtapa').off('submit').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnText = submitBtn.html();

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Excluindo...');

            $.ajax({
                type: "POST",
                url: form.attr('action'),
                data: form.serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#deleteEtapaModal').modal('hide');

                        // Mostrar mensagem de sucesso com detalhes
                        var msg = response.message;
                        if (response.contagem) {
                            msg += `<br><small>Ações excluídas: ${response.contagem.acoes}</small>`;
                        }

                        Swal.fire({
                            title: 'Sucesso!',
                            html: msg,
                            icon: 'success',
                            timer: 3000,
                            timerProgressBar: true,
                            willClose: () => {
                                dataTable.ajax.reload();
                            }
                        });
                    } else {
                        Swal.fire('Erro', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Erro', 'Falha na comunicação com o servidor', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        });

        // Enviar formulário de ordenação
        $('#formOrdenarEtapas').off('submit').on('submit', function(e) {
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
                        $('#ordenarEtapasModal').modal('hide');
                        showSuccessAlert(response.message || 'Ordem atualizada com sucesso!');
                        dataTable.ajax.reload();
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

        // Aplicar filtros
        $('#formFiltros').off('submit').on('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });

        // Limpar filtros
        $('#btnLimparFiltros').off('click').on('click', function() {
            $('#formFiltros')[0].reset();
            applyFilters();
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
                    console.log('Resposta recebida:', response);
                    if (response.success) {
                        if (modalId) {
                            $(modalId).modal('hide');
                            form[0].reset();
                        }
                        showSuccessAlert(successMessage || response.message || 'Operação realizada com sucesso!');

                        // Recarregar a tabela após operações
                        dataTable.ajax.reload();
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

        // Aplicar filtros na tabela
        function applyFilters() {
            const hasFilters = $('#formFiltros').find('input, select').toArray().some(el => $(el).val() !== '' && $(el).val() !== null);

            if (!hasFilters) {
                dataTable.ajax.url(`<?= site_url("etapas/carregar-etapas/{$idProjeto}") ?>`).load();
                return;
            }

            dataTable.ajax.url(`<?= site_url("etapas/filtrar/{$idProjeto}") ?>?${$('#formFiltros').serialize()}`).load();
        }

        // Função para formatar data
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR');
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

        function carregarProgressoEtapas() {
            $('.progress-container[data-etapa-id]').each(function() {
                const container = $(this);
                const etapaId = container.data('etapa-id');
                const progressBar = container.find('.progress-bar');
                const progressText = container.find('.progress-text');

                // Adiciona um loader temporário
                progressBar.css('width', '0%');
                progressText.text('0%');
                container.attr('title', 'Carregando progresso...');

                $.ajax({
                    url: `<?= site_url('etapas/progresso/') ?>${etapaId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const percentual = response.percentual || 0;

                            // Remove qualquer transição/animação
                            progressBar.css('transition', 'none');

                            // Define o valor diretamente
                            progressBar.css('width', percentual + '%');
                            progressBar.attr('aria-valuenow', percentual);
                            progressText.text(percentual + '%');
                            container.attr('title', response.texto || `${response.acoes_finalizadas} de ${response.total_acoes} ações finalizadas`);

                            // Define a classe de cor
                            progressBar.removeClass('bg-success bg-warning bg-danger bg-secondary')
                                .addClass(response.class || 'bg-secondary');
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

        // Chamar a função para carregar o progresso inicial
        carregarProgressoEtapas();
    });
</script>