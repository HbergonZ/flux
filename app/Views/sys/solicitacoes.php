<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Solicitações Pendentes</h1>
    </div>

    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Solicitações</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Tipo Solicitação</th>
                            <th>Nível</th>
                            <th>Nome</th>
                            <th>Solicitante</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao) : ?>
                            <tr>
                                <td class="text-center"><?= ucfirst($solicitacao['tipo']) ?></td>
                                <td class="text-center"><?= ucfirst(str_replace('acao', 'ação', $solicitacao['nivel'])) ?></td>
                                <td><?= esc($solicitacao['nome']) ?></td>
                                <td class="text-center"><?= esc($solicitacao['solicitante']) ?></td>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-warning"><?= ucfirst($solicitacao['status']) ?></span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-primary btn-sm avaliar-btn"
                                        data-id="<?= $solicitacao['id'] ?>"
                                        title="Avaliar Solicitação">
                                        <i class="fas fa-eye"></i> Avaliar
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

<!-- Modal de Avaliação -->
<div class="modal fade" id="avaliarModal" tabindex="-1" role="dialog" aria-labelledby="avaliarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="avaliarModalLabel">Avaliar Solicitação</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAvaliar">
                <input type="hidden" name="id" id="solicitacaoId">
                <div class="modal-body">
                    <div id="modalLoading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p class="mt-2">Carregando dados da solicitação...</p>
                    </div>
                    <div id="modalContent" style="display: none;">
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

                        <div>
                            <div class="form-group">
                                <label for="justificativa-avaliador">Justificativa do Avaliador (Opcional)</label>
                                <textarea class="form-control" id="justificativa-avaliador" name="justificativa" rows="3" placeholder="Adicione uma justificativa para sua decisão..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger recusar-btn">
                        <i class="fas fa-times"></i> Recusar
                    </button>
                    <button type="button" class="btn btn-success aceitar-btn">
                        <i class="fas fa-check"></i> Aceitar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Inicializa o DataTable
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
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
            "order": [
                [4, 'desc']
            ],
            "columnDefs": [{
                    responsivePriority: 1,
                    targets: 0
                },
                {
                    responsivePriority: 2,
                    targets: 6
                },
                {
                    responsivePriority: 3,
                    targets: 4
                },
                {
                    responsivePriority: 4,
                    targets: 5
                },
                {
                    responsivePriority: 5,
                    targets: 2
                },
                {
                    responsivePriority: 6,
                    targets: 3
                },
                {
                    responsivePriority: 7,
                    targets: 1
                }
            ]
        });

        // Abre modal de avaliação
        $(document).on('click', '.avaliar-btn', function() {
            var id = $(this).data('id');

            // Reset do modal
            $('#formAvaliar')[0].reset();
            $('#modalLoading').show();
            $('#modalContent').hide();

            $('#avaliarModal').modal('show');

            $.ajax({
                url: '<?= site_url('solicitacoes/avaliar') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#solicitacaoId').val(id);

                        // Formatadores para melhor exibição
                        function formatFieldName(name) {
                            const names = {
                                'id': 'ID',
                                'nome': 'Nome',
                                'sigla': 'Sigla',
                                'descricao': 'Descrição',
                                'identificador': 'Identificador',
                                'projeto_vinculado': 'Projeto Vinculado',
                                'priorizacao_gab': 'Priorização GAB',
                                'id_eixo': 'Eixo',
                                'id_plano': 'Plano',
                                'responsaveis': 'Responsáveis',
                                'projeto': 'Projeto',
                                'responsavel': 'Responsável',
                                'equipe': 'Equipe',
                                'tempo_estimado_dias': 'Tempo Estimado (dias)',
                                'entrega_estimada': 'Entrega Estimada',
                                'data_inicio': 'Data Início',
                                'data_fim': 'Data Fim',
                                'status': 'Status',
                                'ordem': 'Ordem',
                                'id_projeto': 'Projeto',
                                'id_etapa': 'Etapa'
                            };
                            return names[name] || name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        }

                        function formatFieldValue(value) {
                            if (value === null || value === '' || value === undefined)
                                return '<span class="text-muted">Não informado</span>';
                            if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                const [year, month, day] = value.split('-');
                                return `${day}/${month}/${year}`;
                            }
                            if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
                                const [date, time] = value.split(' ');
                                const [year, month, day] = date.split('-');
                                return `${day}/${month}/${year} ${time}`;
                            }
                            if (typeof value === 'boolean') {
                                return value ? 'Sim' : 'Não';
                            }
                            return value;
                        }

                        const tipoSolicitacao = response.tipo.toLowerCase();
                        const isInclusao = tipoSolicitacao === 'inclusão';
                        const isExclusao = tipoSolicitacao === 'exclusão';
                        const nivel = response.nivel || 'registro';
                        const nivelFormatado = nivel.charAt(0).toUpperCase() + nivel.slice(1);

                        // Preenche tabela de dados atuais
                        let htmlAtuais = '';

                        if (isInclusao) {
                            htmlAtuais = `
                                <tr>
                                    <th width="30%">Tipo</th>
                                    <td>Novo(a) ${nivelFormatado}</td>
                                </tr>
                                <tr>
                                    <th width="30%">Status</th>
                                    <td><span class="badge badge-info">Novo Registro</span></td>
                                </tr>`;
                        } else if (isExclusao) {
                            for (let key in response.dados_atuais) {
                                htmlAtuais += `
                                <tr>
                                    <th width="30%">${formatFieldName(key)}</th>
                                    <td>${formatFieldValue(response.dados_atuais[key])}</td>
                                </tr>`;
                            }
                        } else {
                            for (let key in response.dados_atuais) {
                                htmlAtuais += `
                                <tr>
                                    <th width="30%">${formatFieldName(key)}</th>
                                    <td>${formatFieldValue(response.dados_atuais[key])}</td>
                                </tr>`;
                            }
                        }
                        $('#tabelaDadosAtuais').html(htmlAtuais);

                        // Preenche tabela de alterações
                        let htmlAlterados = '';

                        if (isInclusao) {
                            for (let key in response.dados_alterados) {
                                htmlAlterados += `
                                <tr>
                                    <th width="30%">${formatFieldName(key)}</th>
                                    <td class="text-success"><strong>${formatFieldValue(response.dados_alterados[key])}</strong></td>
                                </tr>`;
                            }
                        } else if (isExclusao) {
                            htmlAlterados = `
                                <tr>
                                    <th width="30%">Tipo</th>
                                    <td class="text-danger"><strong>Exclusão de ${nivelFormatado}</strong></td>
                                </tr>
                                <tr>
                                    <th width="30%">Status</th>
                                    <td><span class="badge badge-danger">Registro será removido</span></td>
                                </tr>`;
                        } else {
                            for (let key in response.dados_alterados) {
                                if (response.dados_alterados[key] && typeof response.dados_alterados[key] === 'object') {
                                    htmlAlterados += `
                                    <tr>
                                        <th width="30%">${formatFieldName(key)}</th>
                                        <td>
                                            <div class="text-danger mb-1"><small>Atual:</small><br><s>${formatFieldValue(response.dados_alterados[key].de)}</s></div>
                                            <div class="text-success"><small>Novo:</small><br><strong>${formatFieldValue(response.dados_alterados[key].para)}</strong></div>
                                        </td>
                                    </tr>`;
                                } else {
                                    htmlAlterados += `
                                    <tr>
                                        <th width="30%">${formatFieldName(key)}</th>
                                        <td class="text-success"><strong>${formatFieldValue(response.dados_alterados[key])}</strong></td>
                                    </tr>`;
                                }
                            }

                            if (htmlAlterados === '') {
                                htmlAlterados = `
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        Nenhuma alteração detectada nos campos
                                    </td>
                                </tr>`;
                            }
                        }
                        $('#tabelaDadosAlterados').html(htmlAlterados);

                        $('#nomeSolicitante').text(response.data.solicitante || 'Não informado');

                        const dataSolicitacao = response.data.data_solicitacao ?
                            new Date(response.data.data_solicitacao).toLocaleString('pt-BR') :
                            'Não informado';
                        $('#dataSolicitacao').text(dataSolicitacao);

                        if (response.data.justificativa_solicitante && response.data.justificativa_solicitante.trim() !== '') {
                            $('#justificativaSolicitacao').html(response.data.justificativa_solicitante);
                        } else {
                            $('#justificativaSolicitacao').html('<em class="text-muted">Nenhuma justificativa fornecida.</em>');
                        }

                        // Mostra conteúdo e esconde loader
                        $('#modalLoading').hide();
                        $('#modalContent').show();

                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao carregar solicitação', 'error');
                        $('#avaliarModal').modal('hide');
                    }
                },
                error: function() {
                    Swal.fire('Erro', 'Falha ao comunicar com o servidor', 'error');
                    $('#avaliarModal').modal('hide');
                }
            });
        });

        // Processa aceitação
        $('.aceitar-btn').click(function() {
            processarSolicitacao('aceitar');
        });

        // Processa recusa
        $('.recusar-btn').click(function() {
            processarSolicitacao('recusar');
        });

        function processarSolicitacao(acao) {
            var formData = {
                id: $('#solicitacaoId').val(),
                acao: acao,
                justificativa: $('#justificativa-avaliador').val()
            };

            var buttons = $('#avaliarModal .modal-footer button');
            buttons.prop('disabled', true);
            $('.aceitar-btn, .recusar-btn').html('<i class="fas fa-spinner fa-spin"></i> Processando...');

            $.ajax({
                url: '<?= site_url('solicitacoes/processar') ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Sucesso!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#avaliarModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Erro!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Falha ao processar solicitação';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Erro!', errorMsg, 'error');
                },
                complete: function() {
                    buttons.prop('disabled', false);
                    $('.aceitar-btn').html('<i class="fas fa-check"></i> Aceitar');
                    $('.recusar-btn').html('<i class="fas fa-times"></i> Recusar');
                }
            });
        }
    });
</script>