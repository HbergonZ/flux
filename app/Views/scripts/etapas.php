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
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
            },
            "searching": false,
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
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
                        var id = row.id + '-' + row.nome.toLowerCase().replace(/\s+/g, '-');
                        var isAdmin = <?= auth()->user()->inGroup('admin') ? 'true' : 'false' ?>;

                        var buttons = `
                            <div class="d-inline-flex">
                                <a href="<?= site_url('etapas/') ?>${row.id}/acoes" class="btn btn-info btn-sm mx-1" style="width: 32px; height: 32px;" title="Visualizar Ações">
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
                    }
                }
            ],
            "data": <?= json_encode($etapas ?? []) ?>
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

        // Event listener para a troca de ordens
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
        $('#ordenarEtapasModal').on('shown.bs.modal', function() {
            $('.ordem-select').each(function() {
                $(this).data('original', $(this).val());
            });
        });

        // Carregar próxima ordem ao abrir o modal de adição
        $('#addEtapaModal').on('show.bs.modal', function() {
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
                        $('#dataTable tbody tr').each(function() {
                            var ordem = parseInt($(this).find('td:eq(0)').text()) || 0;
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
                    $('#dataTable tbody tr').each(function() {
                        var ordem = parseInt($(this).find('td:eq(0)').text()) || 0;
                        if (ordem > maxOrdem) {
                            maxOrdem = ordem;
                        }
                    });
                    $('#etapaOrdem').val(maxOrdem + 1);
                }
            });
        });

        // Cadastrar nova etapa (apenas admin)
        $('#formAddEtapa').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#addEtapaModal');
        });

        // Editar etapa - Abrir modal (apenas admin)
        $(document).on('click', '.btn-primary[title="Editar"]', function() {
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
        $(document).on('click', '.btn-primary[title="Solicitar Edição"]', function() {
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
        $('#solicitarEdicaoModal').on('shown.bs.modal', function() {
            $('#formSolicitarEdicao').on('input change', function() {
                checkForChanges();
            });
        });

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
        $('#formSolicitarEdicao').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#solicitarEdicaoModal', 'Solicitação de edição enviada com sucesso!');
        });

        // Solicitar exclusão de etapa - Abrir modal (para não-admins)
        $(document).on('click', '.btn-danger[title="Solicitar Exclusão"]', function() {
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

        // Atualizar etapa (apenas admin)
        $('#formEditEtapa').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#editEtapaModal');
        });

        // Excluir etapa - Abrir modal de confirmação (apenas admin)
        $(document).on('click', '.btn-danger[title="Excluir"]', function() {
            var etapaId = $(this).data('id').split('-')[0];
            var etapaName = $(this).closest('tr').find('td:nth-child(2)').text();

            $('#deleteEtapaId').val(etapaId);
            $('#etapaNameToDelete').text(etapaName);
            $('#deleteEtapaModal').modal('show');
        });

        // Confirmar exclusão (apenas admin)
        $('#formDeleteEtapa').submit(function(e) {
            e.preventDefault();
            submitForm($(this), '#deleteEtapaModal');
        });

        // Enviar formulário de ordenação
        $('#formOrdenarEtapas').submit(function(e) {
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

                        // Recarregar a página apenas se não for uma solicitação
                        if (!modalId || (modalId !== '#solicitarEdicaoModal' && modalId !== '#solicitarExclusaoModal' && modalId !== '#solicitarInclusaoModal')) {
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
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

        // Aplicar filtros na tabela
        function applyFilters() {
            const hasFilters = $('#formFiltros').find('input, select').toArray().some(el => $(el).val() !== '' && $(el).val() !== null);

            if (!hasFilters) {
                location.reload();
                return;
            }

            $.ajax({
                type: "POST",
                url: '<?= site_url("etapas/filtrar/$idProjeto") ?>',
                data: $('#formFiltros').serialize(),
                dataType: "json",
                beforeSend: function() {
                    $('#dataTable').css('opacity', '0.5');
                },
                success: function(response) {
                    if (response.success) {
                        dataTable.clear().rows.add(response.data).draw();
                    } else {
                        showErrorAlert('Erro ao filtrar etapas: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showErrorAlert('Erro na requisição: ' + error);
                },
                complete: function() {
                    $('#dataTable').css('opacity', '1');
                }
            });
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
    });
</script>