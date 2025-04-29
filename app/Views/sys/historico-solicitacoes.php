<!-- Bootstrap JS e Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome para ícones -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">

<!-- Modal de Visualização - Bootstrap 5 -->
<div class="modal fade" id="visualizarSolicitacaoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalhes da Solicitação Processada</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detalhesSolicitacao">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('projetos-cadastrados') ?>">Projetos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Histórico de Solicitações</li>
        </ol>
    </nav>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Histórico de Solicitações de Edição</h1>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Solicitações Processadas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableHistorico" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th class="col-projeto">Projeto</th>
                            <th>Ação</th>
                            <th class="col-etapa">Etapa</th>
                            <th>Solicitante</th>
                            <th>Data Solicitação</th>
                            <th>Data Processamento</th>
                            <th>Status</th>
                            <th>Alterações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <tr>
                                <td class="text-wrap"><?= $solicitacao['nome_projeto'] ?></td>
                                <td><?= $solicitacao['acao'] ?></td>
                                <td class="text-wrap"><?= $solicitacao['etapa'] ?></td>
                                <td class="text-center"><?= $solicitacao['solicitante'] ?></td>
                                <td class="text-center"><?= $solicitacao['data_formatada'] ?></td>
                                <td class="text-center"><?= $solicitacao['data_processamento_formatada'] ?></td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $solicitacao['status'] == 'aprovada' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($solicitacao['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info btn-ver-solicitacao" data-id="<?= $solicitacao['id'] ?>">
                                        <i class="fas fa-eye"></i> Ver
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

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Inicialização do DataTables para o histórico
        var table = $('#dataTableHistorico').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
            },
            responsive: {
                details: {
                    type: 'column',
                    target: -1
                }
            },
            columnDefs: [{
                    targets: [0, 2], // Colunas Projeto (0) e Etapa (2)
                    className: 'text-wrap',
                    width: '20%'
                },
                {
                    targets: '_all',
                    className: 'align-middle'
                },
                {
                    responsivePriority: 1,
                    targets: [4, 5] // Colunas de data
                },
                {
                    targets: -1, // Última coluna
                    orderable: false,
                    searchable: false
                }
            ],
            autoWidth: false,
            scrollX: false,
            lengthMenu: [10, 25, 50, 100],
            pageLength: 10,
            order: [
                [4, 'desc']
            ], // Ordena por Data de Solicitação
            dom: '<"top"lf>rt<"bottom"ip>',
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_length select').addClass('form-control form-control-sm');
            },
            createdRow: function(row, data, dataIndex) {
                // Garante quebra de texto nas colunas específicas
                $('td:eq(0), td:eq(2)', row).css('white-space', 'normal');
            }
        });

        // Variáveis globais
        var solicitacaoAtualId = null;
        var visualizarModal = new bootstrap.Modal(document.getElementById('visualizarSolicitacaoModal'));

        // Obter token CSRF do meta tag ou gerar
        function getCsrfToken() {
            return $('meta[name="X-CSRF-TOKEN"]').attr('content') || '<?= csrf_hash() ?>';
        }

        // Evento para abrir o modal (usando delegação para funcionar com paginação)
        $('#dataTableHistorico tbody').on('click', '.btn-ver-solicitacao', function() {
            solicitacaoAtualId = $(this).data('id');

            // Mostrar loading
            $('#detalhesSolicitacao').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Carregando...</span>
                    </div>
                    <p>Carregando detalhes...</p>
                </div>
            `);

            // Carregar detalhes via AJAX
            $.ajax({
                url: '<?= site_url('historico-solicitacoes/detalhes/') ?>' + solicitacaoAtualId,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': getCsrfToken()
                },
                dataType: 'html',
                success: function(response) {
                    $('#detalhesSolicitacao').html(response);
                },
                error: function(xhr, status, error) {
                    console.error('Erro:', error);
                    Swal.fire('Erro', 'Falha ao carregar detalhes', 'error');
                    visualizarModal.hide();
                }
            });

            visualizarModal.show();
        });
    });
</script>