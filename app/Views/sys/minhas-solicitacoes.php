<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Minhas Solicitações</h1>
    </div>

    <!-- Card para Solicitações Pendentes -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold text-white">
                Solicitações Pendentes
            </h6>
        </div>
        <div class="card-body">
            <?php if (count(array_filter($solicitacoes, fn($s) => strtolower($s['status']) == 'pendente')) > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="pendentesTable" width="100%" cellspacing="0">
                        <thead class="text-center">
                            <tr>
                                <th>Tipo Solicitação</th>
                                <th>Nível</th>
                                <th>Nome</th>
                                <th>Status</th>
                                <th>Data Solicitação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitacoes as $solicitacao) : ?>
                                <?php if (strtolower($solicitacao['status']) == 'pendente') : ?>
                                    <tr>
                                        <td class="text-center"><?= ucfirst($solicitacao['tipo']) ?></td>
                                        <td class="text-center"><?= ucfirst($solicitacao['nivel']) ?></td>
                                        <td><?= $solicitacao['nome'] ?></td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-warning">Pendente</span>
                                        </td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm visualizar-btn"
                                                data-id="<?= $solicitacao['id'] ?>"
                                                title="Visualizar Solicitação">
                                                <i class="fas fa-eye"></i> Visualizar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="alert alert-info text-center mb-0">
                    Nenhuma solicitação pendente encontrada.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Card para Histórico de Solicitações -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3 bg-primary">
            <h6 class="m-0 font-weight-bold text-white">
                Histórico de Solicitações
            </h6>
        </div>
        <div class="card-body">
            <?php if (count(array_filter($solicitacoes, fn($s) => strtolower($s['status']) != 'pendente')) > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="historicoTable" width="100%" cellspacing="0">
                        <thead class="text-center">
                            <tr>
                                <th>Tipo Solicitação</th>
                                <th>Nível</th>
                                <th>Nome</th>
                                <th>Status</th>
                                <th>Data Solicitação</th>
                                <th>Data Avaliação</th>
                                <th>Avaliador</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitacoes as $solicitacao) : ?>
                                <?php if (strtolower($solicitacao['status']) != 'pendente') : ?>
                                    <tr>
                                        <td class="text-center"><?= ucfirst($solicitacao['tipo']) ?></td>
                                        <td class="text-center"><?= ucfirst($solicitacao['nivel']) ?></td>
                                        <td><?= $solicitacao['nome'] ?></td>
                                        <td class="text-center align-middle">
                                            <?php if (strtolower($solicitacao['status']) == 'aprovada') : ?>
                                                <span class="badge badge-success">Aprovada</span>
                                            <?php elseif (strtolower($solicitacao['status']) == 'rejeitada') : ?>
                                                <span class="badge badge-danger">Rejeitada</span>
                                            <?php else : ?>
                                                <span class="badge badge-secondary"><?= ucfirst($solicitacao['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></td>
                                        <td class="text-center">
                                            <?= $solicitacao['data_avaliacao'] ? date('d/m/Y H:i', strtotime($solicitacao['data_avaliacao'])) : '-' ?>
                                        </td>
                                        <td class="text-center"><?= $solicitacao['avaliador_username'] ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm visualizar-btn"
                                                data-id="<?= $solicitacao['id'] ?>"
                                                title="Visualizar Solicitação">
                                                <i class="fas fa-eye"></i> Visualizar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="alert alert-info text-center mb-0">
                    Nenhuma solicitação no histórico.
                </div>
            <?php endif; ?>
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
                                        <table class="table table-sm table-bordered">
                                            <tbody id="tabelaDadosAtuais"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold">Alterações Solicitadas</h6>
                                    <div id="alteracoesSolicitadas">
                                        <!-- Conteúdo será inserido dinamicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção do Solicitante -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="m-0 font-weight-bold text-primary">Minhas Informações</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Data da Solicitação:</label>
                                        <p id="dataSolicitacao">-</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Minha Justificativa:</label>
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

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Inicialização do DataTable para pendentes
        $('#pendentesTable').DataTable({
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
            },
            "searching": true,
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 5,
            "order": [
                [4, 'desc']
            ],
            "columnDefs": [{
                    responsivePriority: 1,
                    targets: 0
                },
                {
                    responsivePriority: 2,
                    targets: 5
                },
                {
                    responsivePriority: 3,
                    targets: 3
                },
                {
                    responsivePriority: 4,
                    targets: 4
                },
                {
                    responsivePriority: 5,
                    targets: 2
                },
                {
                    responsivePriority: 6,
                    targets: 1
                }
            ]
        });

        // Inicialização do DataTable para histórico
        $('#historicoTable').DataTable({
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
            },
            "searching": true,
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 5,
            "order": [
                [4, 'desc']
            ],
            "columnDefs": [{
                    responsivePriority: 1,
                    targets: 0
                },
                {
                    responsivePriority: 2,
                    targets: 7
                },
                {
                    responsivePriority: 3,
                    targets: 3
                },
                {
                    responsivePriority: 4,
                    targets: 4
                },
                {
                    responsivePriority: 5,
                    targets: 5
                },
                {
                    responsivePriority: 6,
                    targets: 2
                },
                {
                    responsivePriority: 7,
                    targets: 6
                },
                {
                    responsivePriority: 8,
                    targets: 1
                }
            ]
        });

        // Função para formatar nomes de campos
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
                'status': 'Status',
                'descricao': 'Descrição',
                'prioridade': 'Prioridade',
                'orcamento': 'Orçamento'
            };
            return names[name] || name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        // Função para formatar valores
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
            return value;
        }

        // Abre modal de visualização
        $(document).on('click', '.visualizar-btn', function() {
            var id = $(this).data('id');
            var avaliadorNome = $(this).closest('tr').find('td:nth-child(7)').text().trim();

            // Reset do modal
            $('#modalLoading').show();
            $('#modalContent').hide();
            $('#visualizarModal').modal('show');

            $.ajax({
                url: '<?= site_url('minhas-solicitacoes/detalhes') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        try {
                            const responseData = response.data || {};
                            const tipoSolicitacao = (response.tipo || responseData.tipo || '').toString().toLowerCase();
                            const isInclusao = tipoSolicitacao.includes('inclusão') || tipoSolicitacao.includes('inclusao');
                            const isExclusao = tipoSolicitacao.includes('exclusão') || tipoSolicitacao.includes('exclusao');
                            const nivel = response.nivel || responseData.nivel || 'registro';
                            const nivelFormatado = nivel.charAt(0).toUpperCase() + nivel.slice(1);
                            const dadosAtuais = response.dados_atuais || {};
                            const dadosAlterados = response.dados_alterados || {};

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
                                for (let key in dadosAtuais) {
                                    htmlAtuais += `
                                        <tr>
                                            <th width="30%">${formatFieldName(key)}</th>
                                            <td>${formatFieldValue(dadosAtuais[key])}</td>
                                        </tr>`;
                                }
                            } else {
                                for (let key in dadosAtuais) {
                                    htmlAtuais += `
                                        <tr>
                                            <th width="30%">${formatFieldName(key)}</th>
                                            <td>${formatFieldValue(dadosAtuais[key])}</td>
                                        </tr>`;
                                }
                            }
                            $('#tabelaDadosAtuais').html(htmlAtuais);

                            // Preenche tabela de alterações
                            let htmlAlterados = '';

                            if (isInclusao) {
                                for (let key in dadosAlterados) {
                                    htmlAlterados += `
                                        <tr>
                                            <th width="30%">${formatFieldName(key)}</th>
                                            <td class="text-success"><strong>${formatFieldValue(dadosAlterados[key])}</strong></td>
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
                                        <td><span class="badge badge-danger">Registro removido</span></td>
                                    </tr>`;
                            } else {
                                for (let key in dadosAlterados) {
                                    if (dadosAlterados[key] && typeof dadosAlterados[key] === 'object') {
                                        htmlAlterados += `
                                            <tr>
                                                <th width="30%">${formatFieldName(key)}</th>
                                                <td>
                                                    <div class="text-danger mb-1"><small>Original:</small><br><s>${formatFieldValue(dadosAlterados[key].de)}</s></div>
                                                    <div class="text-success"><small>Alteração:</small><br><strong>${formatFieldValue(dadosAlterados[key].para)}</strong></div>
                                                </td>
                                            </tr>`;
                                    } else {
                                        htmlAlterados += `
                                            <tr>
                                                <th width="30%">${formatFieldName(key)}</th>
                                                <td class="text-success"><strong>${formatFieldValue(dadosAlterados[key])}</strong></td>
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

                            // Usando a mesma estrutura de tabela
                            $('#alteracoesSolicitadas').html(`
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <tbody>${htmlAlterados}</tbody>
                                    </table>
                                </div>
                            `);

                            // Preenche informações da solicitação
                            $('#dataSolicitacao').text(responseData.data_solicitacao ?
                                new Date(responseData.data_solicitacao).toLocaleString('pt-BR') : 'Não informado');
                            $('#justificativaSolicitacao').html(
                                responseData.justificativa_solicitante && responseData.justificativa_solicitante.trim() !== '' ?
                                responseData.justificativa_solicitante : '<em class="text-muted">Nenhuma justificativa fornecida.</em>'
                            );

                            // Preenche informações da avaliação
                            const status = responseData.status || '';
                            $('#statusAvaliacao').html(
                                status === 'aprovada' ? '<span class="badge badge-success">Aprovada</span>' :
                                status === 'rejeitada' ? '<span class="badge badge-danger">Rejeitada</span>' :
                                '<span class="badge badge-warning">' + status + '</span>'
                            );
                            $('#avaliador').text(responseData.avaliador_nome || avaliadorNome || 'Sistema');
                            $('#dataAvaliacao').text(responseData.data_avaliacao ?
                                new Date(responseData.data_avaliacao).toLocaleString('pt-BR') : 'Não avaliada');
                            $('#justificativaAvaliador').html(
                                responseData.justificativa_avaliador && responseData.justificativa_avaliador.trim() !== '' ?
                                responseData.justificativa_avaliador : '<em class="text-muted">Nenhuma justificativa fornecida.</em>'
                            );

                            // Mostra conteúdo
                            $('#modalLoading').hide();
                            $('#modalContent').fadeIn();

                        } catch (e) {
                            console.error('Erro:', e);
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Erro ao processar os dados da solicitação',
                                confirmButtonColor: '#3085d6'
                            });
                            $('#modalLoading').hide();
                            $('#visualizarModal').modal('hide');
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: response.message || 'Erro ao carregar solicitação',
                            confirmButtonColor: '#3085d6'
                        });
                        $('#modalLoading').hide();
                        $('#visualizarModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', status, error);
                    let errorMsg = 'Falha ao carregar os dados';

                    if (status === 'timeout') {
                        errorMsg = 'Tempo de resposta excedido';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: errorMsg,
                        confirmButtonColor: '#3085d6'
                    });
                    $('#modalLoading').hide();
                    $('#visualizarModal').modal('hide');
                }
            });
        });
    });
</script>