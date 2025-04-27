<!-- Bootstrap JS e Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome para ícones -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- jQuery (já está no seu código) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Modal de Visualização e Aprovação -->
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger btn-rejeitar">
                    <i class="fas fa-times mr-1"></i> Recusar
                </button>
                <button type="button" class="btn btn-primary btn-aprovar">
                    <i class="fas fa-check mr-1"></i> Aceitar
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
            <li class="breadcrumb-item active" aria-current="page">Solicitações de Edição</li>
        </ol>
    </nav>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Solicitações de Edição Pendentes</h1>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Solicitações</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTableSolicitacoes" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Projeto</th>
                            <th>Etapa</th>
                            <th>Ação</th>
                            <th>Solicitante</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <tr>
                                <td><?= $solicitacao['id'] ?></td>
                                <td><?= $solicitacao['nome_projeto'] ?></td>
                                <td><?= $solicitacao['etapa'] ?></td>
                                <td><?= $solicitacao['acao'] ?></td>
                                <td><?= $solicitacao['solicitante'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])) ?></td>
                                <td>
                                    <span class="badge badge-<?=
                                                                $solicitacao['status'] == 'pendente' ? 'warning' : ($solicitacao['status'] == 'aprovada' ? 'success' : 'danger')
                                                                ?>">
                                        <?= ucfirst($solicitacao['status']) ?>
                                    </span>
                                </td>
                                <td>
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
        // Verifica se SweetAlert2 está carregado
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 não foi carregado corretamente');
            // Carrega dinamicamente se não estiver disponível
            $.getScript('https://cdn.jsdelivr.net/npm/sweetalert2@11')
                .done(function() {
                    console.log('SweetAlert2 carregado dinamicamente');
                    initSolicitacoes();
                })
                .fail(function() {
                    console.error('Falha ao carregar SweetAlert2');
                    // Fallback para alertas nativos
                    window.Swal = {
                        fire: function(options) {
                            return Promise.resolve({
                                isConfirmed: confirm(options.text || 'Confirmar ação?')
                            });
                        }
                    };
                    initSolicitacoes();
                });
        } else {
            initSolicitacoes();
        }

        function initSolicitacoes() {
            $(document).ready(function() {
                // Variáveis globais
                var solicitacaoAtualId = null;
                var visualizarModal = new bootstrap.Modal(document.getElementById('visualizarSolicitacaoModal'));

                // Obter token CSRF do meta tag ou gerar
                function getCsrfToken() {
                    return $('meta[name="X-CSRF-TOKEN"]').attr('content') || '<?= csrf_hash() ?>';
                }

                // Evento para abrir o modal
                $(document).on('click', '.btn-ver-solicitacao', function() {
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
                        url: '<?= site_url('solicitacoes-edicao/detalhes/') ?>' + solicitacaoAtualId,
                        method: 'GET',
                        dataType: 'html',
                        success: function(response) {
                            $('#detalhesSolicitacao').html(response);
                        },
                        error: function() {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Erro', 'Falha ao carregar detalhes', 'error');
                            } else {
                                alert('Erro: Falha ao carregar detalhes');
                            }
                            visualizarModal.hide();
                        }
                    });

                    visualizarModal.show();
                });

                // Evento para aprovar
                $(document).on('click', '.btn-aprovar', function() {
                    confirmarProcessamento('aprovada');
                });

                // Evento para rejeitar
                $(document).on('click', '.btn-rejeitar', function() {
                    confirmarProcessamento('rejeitada');
                });

                // Função para confirmar ação
                function confirmarProcessamento(acao) {
                    if (!solicitacaoAtualId) {
                        console.error('ID da solicitação não definido');
                        return;
                    }

                    const actionText = acao === 'aprovada' ? 'aprovar' : 'rejeitar';
                    const actionColor = acao === 'aprovada' ? '#28a745' : '#dc3545';

                    Swal.fire({
                        title: `Confirmar ${actionText}?`,
                        text: `Você está prestes a ${actionText} esta solicitação!`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: actionColor,
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: `Sim, ${actionText}!`,
                        cancelButtonText: 'Cancelar',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processarSolicitacao(acao);
                        }
                    });
                }

                // Função principal de processamento
                function processarSolicitacao(acao) {
                    // Configuração dos botões
                    const $btnAprovar = $('.btn-aprovar');
                    const $btnRejeitar = $('.btn-rejeitar');

                    $btnAprovar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                    $btnRejeitar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                    // Dados da requisição
                    const requestData = {
                        acao: acao,
                        <?= csrf_token() ?>: getCsrfToken()
                    };

                    // Headers adicionais
                    const requestHeaders = {
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': getCsrfToken()
                    };

                    // Enviar requisição
                    $.ajax({
                        url: '<?= site_url('solicitacoes-edicao/processar/') ?>' + solicitacaoAtualId,
                        method: 'POST',
                        data: requestData,
                        headers: requestHeaders,
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                visualizarModal.hide();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sucesso!',
                                    text: response.message || 'Ação realizada com sucesso',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Erro', response.message || 'Falha ao processar', 'error');
                                resetButtons();
                            }
                        },
                        error: function(xhr) {
                            console.error('Erro completo:', xhr);
                            Swal.fire('Erro', 'Falha na comunicação com o servidor', 'error');
                            resetButtons();
                        }
                    });

                    function resetButtons() {
                        $btnAprovar.prop('disabled', false).html('<i class="fas fa-check"></i> Aceitar');
                        $btnRejeitar.prop('disabled', false).html('<i class="fas fa-times"></i> Recusar');
                    }
                }

                // Resetar botões ao fechar modal
                $('#visualizarSolicitacaoModal').on('hidden.bs.modal', function() {
                    $('.btn-aprovar, .btn-rejeitar').prop('disabled', false)
                        .html(function() {
                            return $(this).hasClass('btn-aprovar') ?
                                '<i class="fas fa-check"></i> Aceitar' :
                                '<i class="fas fa-times"></i> Recusar';
                        });
                });
            });
        }
    </script>