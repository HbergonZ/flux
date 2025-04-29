<!-- Bootstrap JS e Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome para ícones -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- jQuery (já está no seu código) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">

<!-- Modal de Visualização -->
<div class="modal fade" id="visualizarSolicitacaoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalhes da Solicitação</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detalhesSolicitacao">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('meus-projetos') ?>">Projetos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Minhas Solicitações</li>
        </ol>
    </nav>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Minhas Solicitações de Edição</h1>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Histórico de Solicitações</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableSolicitacoes" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Projeto</th>
                            <th>Ação</th>
                            <th>Etapa</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Alterações Solicitadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <tr>
                                <td class="text-center"><?= $solicitacao['id'] ?></td>
                                <td><?= $solicitacao['nome_projeto'] ?></td>
                                <td><?= $solicitacao['acao'] ?></td>
                                <td><?= $solicitacao['etapa'] ?></td>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-<?=
                                                                $solicitacao['status'] == 'pendente' ? 'warning' : ($solicitacao['status'] == 'aprovada' ? 'success' : 'danger')
                                                                ?>">
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

    <script>
        $(document).ready(function() {
            // Inicialização do DataTables
            var table = $('#dataTableSolicitacoes').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                responsive: true,
                lengthMenu: [10, 25, 50, 100],
                pageLength: 10,
                order: [
                    [0, 'desc']
                ],
                dom: '<"top"lf>rt<"bottom"ip>',
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-control form-control-sm');
                }
            });

            // Variável para armazenar o modal
            var visualizarModal = new bootstrap.Modal(document.getElementById('visualizarSolicitacaoModal'));

            // Obter token CSRF
            function getCsrfToken() {
                return $('meta[name="X-CSRF-TOKEN"]').attr('content') || '<?= csrf_hash() ?>';
            }

            // Evento para abrir o modal (usando delegação para funcionar com paginação)
            $('#dataTableSolicitacoes tbody').on('click', '.btn-ver-solicitacao', function() {
                var solicitacaoId = $(this).data('id');

                // Mostrar loading
                $('#detalhesSolicitacao').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Carregando...</span>
                </div>
                <p>Carregando detalhes...</p>
            </div>
        `);

                // Mostrar o modal
                visualizarModal.show();

                // Carregar detalhes via AJAX
                $.ajax({
                    url: '<?= site_url('minhas-solicitacoes/detalhes/') ?>' + solicitacaoId,
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': getCsrfToken()
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#detalhesSolicitacao').html(response.html);
                        } else {
                            Swal.fire('Erro', response.message || 'Erro ao carregar detalhes', 'error');
                            visualizarModal.hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro:', error);
                        Swal.fire('Erro', 'Falha na comunicação com o servidor', 'error');
                        visualizarModal.hide();
                    }
                });
            });

            // Evento para fechar o modal ao clicar no botão de fechar
            $('#visualizarSolicitacaoModal .btn-close, #visualizarSolicitacaoModal .btn-secondary').on('click', function() {
                visualizarModal.hide();
            });

            // Evento para fechar o modal ao pressionar ESC
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#visualizarSolicitacaoModal').hasClass('show')) {
                    visualizarModal.hide();
                }
            });

            // Fallback para SweetAlert2 caso não esteja carregado
            if (typeof Swal === 'undefined') {
                window.Swal = {
                    fire: function(options) {
                        alert(options.text || options.title || 'Mensagem do sistema');
                        return Promise.resolve({
                            isConfirmed: true
                        });
                    }
                };
            }
        });
    </script>
</div>