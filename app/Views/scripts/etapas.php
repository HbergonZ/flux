<!-- Scripts do DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<!-- SweetAlert2 para mensagens -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Inicializa o DataTable
        var dataTable = $('#dataTable').DataTable({
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Portuguese-Brasil.json",
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "Nenhum registro encontrado",
                "info": "Mostrando página _PAGE_ de _PAGES_",
                "infoEmpty": "Nenhum registro disponível",
                "infoFiltered": "(filtrado de _MAX_ registros totais)",
                "search": "Pesquisar:",
                "paginate": {
                    "first": "Primeira",
                    "last": "Última",
                    "next": "Próxima",
                    "previous": "Anterior"
                }
            },
            "responsive": {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            "columnDefs": [{
                    className: 'control',
                    orderable: false,
                    targets: -1
                },
                {
                    responsivePriority: 1,
                    targets: 0
                },
                {
                    responsivePriority: 2,
                    targets: 1
                },
                {
                    responsivePriority: 3,
                    targets: 3
                },
                {
                    responsivePriority: 4,
                    targets: 9
                },
                {
                    responsivePriority: 5,
                    targets: 10
                },
                {
                    className: 'text-center',
                    targets: [0, 2, 4, 5, 6, 7, 8, 9, 10]
                }
            ],
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
            "searching": false
        });

        // Configuração do AJAX
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
            }
        });

        // Função para formatar datas corretamente
        function formatarData(dataString) {
            if (!dataString) return '';
            // Se já estiver no formato dd/mm/yyyy, retorna sem alteração
            if (dataString.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                return dataString;
            }
            // Converte de yyyy-mm-dd para dd/mm/yyyy
            var parts = dataString.split(' ')[0].split('-');
            if (parts.length === 3) {
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }
            return dataString;
        }

        // Função para aplicar filtros
        $('#formFiltros').submit(function(e) {
            e.preventDefault();

            // Verifica se há filtros aplicados
            var hasFilters = false;
            $(this).find('input, select').each(function() {
                if ($(this).val() !== '' && $(this).val() !== null) {
                    hasFilters = true;
                    return false;
                }
            });

            // Se não houver filtros, apenas recarrega a página
            if (!hasFilters) {
                location.reload();
                return;
            }

            var idProjeto = $('input[name="id_projeto"]').val();

            $.ajax({
                type: "POST",
                url: '<?= site_url('visao-projeto/filtrar/') ?>' + idProjeto,
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Limpa a tabela
                        dataTable.clear().draw();

                        // Adiciona os novos registros
                        $.each(response.data, function(index, etapa) {
                            var id = etapa.id_etapa + '-' + etapa.id_acao;
                            var badge_class = getBadgeClass(etapa.status);

                            // Formata as datas corretamente
                            var dataInicio = formatarData(etapa.data_inicio_formatada || etapa.data_inicio);
                            var dataFim = formatarData(etapa.data_fim_formatada || etapa.data_fim);

                            dataTable.row.add([
                                'ETP-' + etapa.id_etapa,
                                etapa.etapa,
                                'ACT-' + etapa.id_acao,
                                etapa.acao,
                                etapa.coordenacao,
                                etapa.responsavel,
                                etapa.tempo_estimado_dias + ' dias',
                                dataInicio,
                                dataFim,
                                '<span class="badge ' + badge_class + '">' + etapa.status + '</span>',
                                getActionButtons(id)
                            ]).draw(false);
                        });
                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao filtrar etapas', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Erro', 'Erro na requisição: ' + error, 'error');
                    console.error('Erro completo:', xhr.responseText);
                }
            });
        });

        // Função para limpar filtros
        $('#btnLimparFiltros').click(function() {
            $('#formFiltros')[0].reset();
            $('#formFiltros').submit();
        });

        // Função auxiliar para determinar a classe do badge
        function getBadgeClass(status) {
            switch (status) {
                case 'Em andamento':
                    return 'badge-primary';
                case 'Finalizado':
                    return 'badge-success';
                case 'Paralisado':
                    return 'badge-warning';
                case 'Não iniciado':
                    return 'badge-secondary';
                default:
                    return 'badge-light';
            }
        }

        // Função auxiliar para gerar botões de ação
        function getActionButtons(id) {
            return '<div class="d-flex justify-content-center">' +
                '<button type="button" class="btn btn-info btn-sm mx-1 btn-detalhes" style="width: 32px; height: 32px;" data-id="' + id + '" title="Detalhes">' +
                '<i class="fas fa-info-circle"></i>' +
                '</button>' +
                '<button type="button" class="btn btn-primary btn-sm mx-1 btn-editar" style="width: 32px; height: 32px;" data-id="' + id + '" title="Solicitar Edição">' +
                '<i class="fas fa-edit"></i>' +
                '</button>' +
                '<button type="button" class="btn btn-danger btn-sm mx-1 btn-excluir" style="width: 32px; height: 32px;" data-id="' + id + '" title="Excluir">' +
                '<i class="fas fa-trash-alt"></i>' +
                '</button>' +
                '</div>';
        }

        // Evento delegado para funcionar em qualquer situação
        $(document).on('click', '.btn-primary[title="Solicitar Edição"], .btn-editar', function() {
            var $btn = $(this);
            var ids = $btn.data('id').split('-');
            var idEtapa = ids[0];
            var idAcao = ids[1];

            // Mostra loading no modal
            var $modal = $('#solicitarEdicaoModal');
            $modal.find('.modal-body').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Carregando...</span>
            </div>
            <p class="mt-2">Carregando dados da etapa...</p>
        </div>
    `);
            $modal.modal('show');

            console.log('Acessando endpoint:', '<?= site_url('visao-projeto/dados-etapa/') ?>' + idEtapa + '/' + idAcao);

            $.ajax({
                type: "GET",
                url: '<?= site_url('visao-projeto/dados-etapa/') ?>' + idEtapa + '/' + idAcao,
                dataType: "json",
                success: function(response) {
                    console.log('Resposta recebida:', response);

                    if (response.success) {
                        var etapa = response.data;

                        // Preenche o modal com os dados atuais
                        $modal.find('.modal-body').html(`
                    <input type="hidden" name="id_etapa" id="edit_id_etapa" value="${etapa.id_etapa}">
                    <input type="hidden" name="id_acao" id="edit_id_acao" value="${etapa.id_acao}">
                    <input type="hidden" name="id_projeto" id="edit_id_projeto" value="${etapa.id_projeto}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_etapa">Etapa</label>
                                <input type="text" class="form-control" id="edit_etapa" name="etapa" value="${etapa.etapa || ''}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_acao">Ação</label>
                                <input type="text" class="form-control" id="edit_acao" name="acao" value="${etapa.acao || ''}" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Restante do formulário -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_coordenacao">Coordenação</label>
                                <input type="text" class="form-control" id="edit_coordenacao" name="coordenacao" value="${etapa.coordenacao || ''}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_responsavel">Responsável</label>
                                <input type="text" class="form-control" id="edit_responsavel" name="responsavel" value="${etapa.responsavel || ''}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="Em andamento" ${etapa.status === 'Em andamento' ? 'selected' : ''}>Em andamento</option>
                                    <option value="Finalizado" ${etapa.status === 'Finalizado' ? 'selected' : ''}>Finalizado</option>
                                    <option value="Paralisado" ${etapa.status === 'Paralisado' ? 'selected' : ''}>Paralisado</option>
                                    <option value="Não iniciado" ${etapa.status === 'Não iniciado' ? 'selected' : ''}>Não iniciado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_tempo_estimado">Tempo Estimado (dias)</label>
                                <input type="number" class="form-control" id="edit_tempo_estimado" name="tempo_estimado_dias" value="${etapa.tempo_estimado_dias || ''}" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_data_inicio">Data Início</label>
                                <input type="date" class="form-control" id="edit_data_inicio" name="data_inicio" value="${etapa.data_inicio ? etapa.data_inicio.split(' ')[0] : ''}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_data_fim">Data Fim</label>
                                <input type="date" class="form-control" id="edit_data_fim" name="data_fim" value="${etapa.data_fim ? etapa.data_fim.split(' ')[0] : ''}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_justificativa">Justificativa para as alterações <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_justificativa" name="justificativa" rows="3" required placeholder="Descreva detalhadamente o motivo das alterações propostas"></textarea>
                        <small class="form-text text-muted">Mínimo 10 caracteres</small>
                    </div>
                `);
                    } else {
                        $modal.modal('hide');
                        Swal.fire('Erro', response.message || 'Erro ao carregar dados da etapa', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $modal.modal('hide');
                    Swal.fire('Erro', 'Erro ao carregar dados: ' + error, 'error');
                    console.error('Detalhes do erro:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                }
            });
        });

        // Envio do formulário de solicitação de edição
        $(document).on('submit', '#formSolicitarEdicao', function(e) {
            e.preventDefault();

            var justificativa = $('#edit_justificativa').val().trim();
            if (justificativa.length < 10) {
                Swal.fire('Atenção', 'A justificativa deve ter pelo menos 10 caracteres', 'warning');
                return;
            }

            var $btn = $('#btnSubmitSolicitacao');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');

            var dados = {
                id_etapa: $('#edit_id_etapa').val(),
                id_acao: $('#edit_id_acao').val(),
                id_projeto: $('#edit_id_projeto').val(),
                dados_atuais: JSON.stringify({
                    etapa: $('#edit_etapa').val(),
                    acao: $('#edit_acao').val(),
                    coordenacao: $('#edit_coordenacao').val(),
                    responsavel: $('#edit_responsavel').val(),
                    status: $('#edit_status').val(),
                    tempo_estimado_dias: $('#edit_tempo_estimado').val(),
                    data_inicio: $('#edit_data_inicio').val(),
                    data_fim: $('#edit_data_fim').val()
                }),
                dados_alterados: JSON.stringify({
                    coordenacao: $('#edit_coordenacao').val(),
                    responsavel: $('#edit_responsavel').val(),
                    status: $('#edit_status').val(),
                    tempo_estimado_dias: $('#edit_tempo_estimado').val(),
                    data_inicio: $('#edit_data_inicio').val(),
                    data_fim: $('#edit_data_fim').val()
                }),
                justificativa: justificativa,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            };

            $.ajax({
                type: "POST",
                url: '<?= site_url('visao-projeto/solicitar-edicao') ?>',
                data: dados,
                dataType: "json",
                success: function(response) {
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Enviar Solicitação');

                    if (response.success) {
                        Swal.fire({
                            title: 'Sucesso!',
                            text: 'Solicitação enviada para aprovação',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                        $('#solicitarEdicaoModal').modal('hide');
                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao enviar solicitação', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Enviar Solicitação');
                    Swal.fire('Erro', 'Erro na comunicação com o servidor', 'error');
                    console.error('Detalhes do erro:', xhr.responseText);
                }
            });
        });

        // Evento para fechar o modal
        $('#solicitarEdicaoModal').on('hidden.bs.modal', function() {
            $(this).find('form')[0].reset();
        });
    });
</script>