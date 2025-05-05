<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Histórico de Solicitações</h1>
    </div>

    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Solicitações Avaliadas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="text-center">
                        <tr>
                            <th>Tipo Solicitação</th>
                            <th>Nível</th>
                            <th>Nome</th>
                            <th>Solicitante</th>
                            <th>Data Solicitação</th>
                            <th>Data Avaliação</th>
                            <th>Avaliador</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao) : ?>
                            <tr>
                                <td class="text-center"><?= ucfirst($solicitacao['tipo']) ?></td>
                                <td class="text-center"><?= $solicitacao['nivel'] ?></td>
                                <td><?= $solicitacao['nome'] ?></td>
                                <td class="text-center"><?= $solicitacao['solicitante'] ?></td>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></td>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($solicitacao['data_avaliacao'])) ?></td>
                                <td class="text-center"><?= $solicitacao['avaliador_username'] ?></td>
                                <td class="text-center align-middle">
                                    <?php if (strtolower($solicitacao['status']) == 'aprovada') : ?>
                                        <span class="badge badge-success"><?= ucfirst($solicitacao['status']) ?></span>
                                    <?php elseif (strtolower($solicitacao['status']) == 'rejeitada') : ?>
                                        <span class="badge badge-danger"><?= ucfirst($solicitacao['status']) ?></span>
                                    <?php else : ?>
                                        <span class="badge badge-warning"><?= ucfirst($solicitacao['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm visualizar-btn"
                                        data-id="<?= $solicitacao['id'] ?>"
                                        title="Visualizar Solicitação">
                                        <i class="fas fa-eye"></i> Visualizar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Visualização -->
<div class="modal fade" id="visualizarModal" tabindex="-1" role="dialog" aria-labelledby="visualizarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="visualizarModalLabel">Detalhes da Solicitação</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalLoading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-2">Carregando dados da solicitação...</p>
                </div>

                <div id="modalContent" style="display:none;">
                    <!-- Seção de Dados Técnicos -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">Dados da Solicitação</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold">Dados Atuais no Momento da Solicitação</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered" id="tabelaDadosAtuais"></table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold">Alterações Solicitadas</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered" id="tabelaDadosAlterados"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção do Solicitante -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">Informações do Solicitante</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Solicitante:</label>
                                        <p id="nomeSolicitante">-</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Data da Solicitação:</label>
                                        <p id="dataSolicitacao">-</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Justificativa:</label>
                                        <div class="p-3 bg-light rounded border" id="justificativaSolicitacao">
                                            <em class="text-muted">Nenhuma justificativa fornecida.</em>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção do Avaliador -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">Informações da Avaliação</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Status:</label>
                                        <p id="statusAvaliacao">-</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Avaliador:</label>
                                        <p id="avaliador">-</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Data da Avaliação:</label>
                                        <p id="dataAvaliacao">-</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Justificativa do Avaliador:</label>
                                        <div class="p-3 bg-light rounded border" id="justificativaAvaliador">
                                            <em class="text-muted">Nenhuma justificativa fornecida.</em>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Inicializa o DataTable com as configurações desejadas
        $('#dataTable').DataTable({
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
            "searching": true,
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
            "order": [
                [5, 'desc']
            ], // Ordena por data de avaliação decrescente
            "columnDefs": [{
                responsivePriority: 1,
                targets: 0
            }, {
                responsivePriority: 2,
                targets: 8
            }, {
                responsivePriority: 3,
                targets: 5
            }, {
                responsivePriority: 4,
                targets: 7
            }, {
                responsivePriority: 5,
                targets: 2
            }, {
                responsivePriority: 6,
                targets: 3
            }, {
                responsivePriority: 7,
                targets: 6
            }, {
                responsivePriority: 8,
                targets: 4
            }, {
                responsivePriority: 9,
                targets: 1
            }]
        });

        // Abre modal de visualização
        $(document).on('click', '.visualizar-btn', function() {
            var id = $(this).data('id');
            var avaliadorNome = $(this).closest('tr').find('td:nth-child(7)').text().trim();

            // Reset do modal
            $('#modalLoading').show();
            $('#modalContent').hide();
            $('#visualizarModal').modal('show');

            $.ajax({
                url: '<?= site_url('historico-solicitacoes/detalhes') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Formatadores para melhor exibição
                        function formatFieldName(name) {
                            const names = {
                                'etapa': 'Etapa',
                                'acao': 'Ação',
                                'nome': 'Nome',
                                'responsavel': 'Responsável',
                                'equipe': 'Equipe',
                                'tempo_estimado_dias': 'Tempo Estimado (dias)',
                                'data_inicio': 'Data Início',
                                'data_fim': 'Data Fim',
                                'status': 'Status'
                            };
                            return names[name] || name;
                        }

                        function formatFieldValue(value) {
                            if (value === null || value === '') return '<span class="text-muted">Não informado</span>';
                            if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                const [year, month, day] = value.split('-');
                                return `${day}/${month}/${year}`;
                            }
                            if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
                                const [datePart, timePart] = value.split(' ');
                                const [year, month, day] = datePart.split('-');
                                return `${day}/${month}/${year} ${timePart}`;
                            }
                            return value;
                        }

                        // Preenche dados técnicos
                        let htmlAtuais = '';
                        if (response.dados_atuais) {
                            for (let key in response.dados_atuais) {
                                htmlAtuais += `<tr><th width="30%">${formatFieldName(key)}</th><td>${formatFieldValue(response.dados_atuais[key])}</td></tr>`;
                            }
                        }
                        $('#tabelaDadosAtuais').html(htmlAtuais);

                        let htmlAlterados = '';
                        if (response.dados_alterados) {
                            for (let key in response.dados_alterados) {
                                htmlAlterados += `
                                    <tr>
                                        <th width="30%">${formatFieldName(key)}</th>
                                        <td>
                                            <div class="text-danger mb-1"><small>Atual:</small><br><s>${formatFieldValue(response.dados_alterados[key].de)}</s></div>
                                            <div class="text-success"><small>Novo:</small><br><strong>${formatFieldValue(response.dados_alterados[key].para)}</strong></div>
                                        </td>
                                    </tr>`;
                            }
                        }
                        $('#tabelaDadosAlterados').html(htmlAlterados);

                        // Preenche seção do solicitante
                        $('#nomeSolicitante').text(response.data.solicitante || 'Não informado');
                        $('#dataSolicitacao').text(response.data.data_solicitacao ?
                            formatFieldValue(response.data.data_solicitacao) : 'Não informado');

                        if (response.data.justificativa_solicitante && response.data.justificativa_solicitante.trim() !== '') {
                            $('#justificativaSolicitacao').html(response.data.justificativa_solicitante);
                        } else {
                            $('#justificativaSolicitacao').html('<em class="text-muted">Nenhuma justificativa fornecida.</em>');
                        }

                        // Preenche seção do avaliador
                        const statusBadge = response.data.status === 'aprovada' ?
                            '<span class="badge badge-success">Aprovada</span>' :
                            response.data.status === 'rejeitada' ?
                            '<span class="badge badge-danger">Rejeitada</span>' :
                            '<span class="badge badge-warning">' + response.data.status + '</span>';
                        $('#statusAvaliacao').html(statusBadge);

                        // Usa o nome do avaliador da tabela ou do response (priorizando o response)
                        const avaliador = response.data.avaliador_nome || avaliadorNome || 'Sistema';
                        $('#avaliador').text(avaliador);

                        $('#dataAvaliacao').text(response.data.data_avaliacao ?
                            formatFieldValue(response.data.data_avaliacao) : 'Não avaliada');

                        if (response.data.justificativa_avaliador && response.data.justificativa_avaliador.trim() !== '') {
                            $('#justificativaAvaliador').html(response.data.justificativa_avaliador);
                        } else {
                            $('#justificativaAvaliador').html('<em class="text-muted">Nenhuma justificativa fornecida.</em>');
                        }

                        // Mostra conteúdo
                        $('#modalLoading').hide();
                        $('#modalContent').show();

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message || 'Erro ao carregar solicitação',
                            confirmButtonColor: '#3085d6'
                        });
                        $('#visualizarModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Falha ao comunicar com o servidor',
                        confirmButtonColor: '#3085d6'
                    });
                    $('#visualizarModal').modal('hide');
                }
            });
        });
    });
</script>