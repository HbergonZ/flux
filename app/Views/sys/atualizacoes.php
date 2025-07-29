<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Atualizações do Sistema</h1>
    </div>

    <!-- Card para Listagem de Versões -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3 bg-primary">
            <h6 class="m-0 font-weight-bold text-white">
                Histórico de Versões
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="versoesTable" width="100%" cellspacing="0" style="vertical-align: middle;">
                    <thead class="text-center">
                        <tr>
                            <th style="vertical-align: middle;">Versão</th>
                            <th style="vertical-align: middle;">Data</th>
                            <th style="vertical-align: middle;">Principais Melhorias</th>
                            <th style="vertical-align: middle;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Versão 1.1 -->
                        <tr>
                            <td class="text-center font-weight-bold" style="vertical-align: middle;">1.1.0</td>
                            <td class="text-center" style="vertical-align: middle;"><?= date('d/m/Y') ?></td>
                            <td style="vertical-align: middle;">
                                <ul class="mb-0 pl-3">
                                    <li>Padronização da hierarquia (Plano > Projeto > Etapa > Ação)</li>
                                    <li>Novos campos de datas e progresso</li>
                                    <li>Sistema de notificações para responsáveis</li>
                                </ul>
                            </td>
                            <td class="text-center" style="vertical-align: middle;">
                                <button class="btn btn-info btn-sm visualizar-btn"
                                    data-version="1.1.0"
                                    title="Visualizar Detalhes">
                                    <i class="fas fa-eye"></i> Detalhes
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes da Versão -->
<div class="modal fade" id="detalhesModal" tabindex="-1" role="dialog" aria-labelledby="detalhesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detalhesModalLabel">Detalhes da Versão <span id="versaoTitulo"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalLoading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-2">Carregando detalhes da versão...</p>
                </div>

                <div id="modalContent" style="display:none;">
                    <!-- Conteúdo será preenchido via JavaScript -->
                    <div id="detalhesVersao"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Dados das versões (poderia ser carregado via AJAX no futuro)
    const versoesData = {
        '1.1.0': {
            data: '<?= date('d/m/Y') ?>',
            detalhes: `
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">🛠️ Atualizações Gerais</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Padronização completa da hierarquia: <strong>Plano > Projeto > Etapa > Ação</strong></li>
                                    <li>Padronização visual dos <strong>modais</strong> em todo o sistema</li>
                                    <li>Tradução completa das <strong>tabelas e elementos de interface</strong> para idioma padrão</li>
                                    <li>Campos de texto com limite de exibição e opção <strong>"Veja mais"</strong> para melhor usabilidade</li>
                                    <li>Atualização automática de <strong>status</strong> com base nas <strong>datas informadas</strong> sempre que um usuário acessa a página</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">📊 Funcionalidades e Campos Adicionados</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><strong>Datas refinadas</strong> para ações:
                                        <ul>
                                            <li>Data de entrega estimada</li>
                                            <li>Data de início (real)</li>
                                            <li>Data de fim (real)</li>
                                        </ul>
                                    </li>
                                    <li>Inclusão de <strong>campo de progresso</strong> para <strong>Planos, Projetos e Etapas</strong>, com base na quantidade de ações finalizadas</li>
                                    <li>Inclusão de campo <strong>Meta</strong> (texto) na página de Projetos</li>
                                    <li>Inclusão de campo <strong>Indicadores</strong> (texto) para Projetos e Ações</li>
                                    <li>Inclusão do campo <strong>Data final do Projeto</strong>, calculada a partir da última ação finalizada</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">🧾 Gerenciamento e Organização</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Inclusão de <strong>coluna de ordenamento</strong> para níveis com sequenciamento</li>
                                    <li>Adicionada opção de <strong>paralisar Projetos</strong>, afetando também suas Etapas e Ações</li>
                                    <li><strong>Atribuição de responsáveis</strong> por Projetos e Ações a usuários do sistema</li>
                                    <li>Inclusão de <strong>filtro de priorização do gabinete</strong> para projetos específicos</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">🗂️ Evidências e Documentação</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Inclusão de <strong>anexos de evidência</strong> para Projetos e Ações:
                                        <ul>
                                            <li>Texto ou Link</li>
                                            <li>Obrigatoriedade de descrição da evidência</li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">🖥️ Interface e Visualização</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Possibilidade de <strong>exibir/ocultar campos</strong> na <strong>página de visão geral</strong></li>
                                    <li>Definição de tamanho máximo para campos específicos com visualização expandida sob demanda</li>
                                    <li>Melhoria na visualização com campos dinâmicos e atualizações visuais automatizadas</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">🔔 Notificações e Alertas</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Notificações automáticas para <strong>responsáveis</strong> em caso de <strong>atraso de ações</strong> vinculadas a eles</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">📡 Broadcast e Comunicação</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Inclusão de sistema de <strong>broadcast</strong> em todos os níveis de visão detalhada</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">🛡️ Segurança e Auditoria</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Registro de todas as <strong>ações realizadas por administradores</strong> para fins de auditoria e controle</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">✅ Lógica de Status</h6>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>Definição <strong>automática de status</strong> das ações com base nas datas preenchidas (início e fim)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `
        }
    };
</script>

<!-- Scripts necessários -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#versoesTable').DataTable({
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            "language": {
                "emptyTable": "Nenhuma versão disponível",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ versões",
                "infoEmpty": "Mostrando 0 a 0 de 0 versões",
                "infoFiltered": "(filtrado de _MAX_ versões no total)",
                "lengthMenu": "Mostrar _MENU_ versões por página",
                "loadingRecords": "Carregando...",
                "processing": "Processando...",
                "search": "Pesquisar:",
                "zeroRecords": "Nenhuma versão correspondente encontrada",
                "paginate": {
                    "first": "Primeira",
                    "last": "Última",
                    "next": "Próxima",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50],
            "pageLength": 10,
            "order": [
                [1, 'desc']
            ],
            "columnDefs": [{
                    "width": "10%",
                    "targets": 0,
                    "className": "align-middle"
                },
                {
                    "width": "15%",
                    "targets": 1,
                    "className": "align-middle"
                },
                {
                    "width": "60%",
                    "targets": 2,
                    "className": "align-middle"
                },
                {
                    "width": "15%",
                    "targets": 3,
                    "orderable": false,
                    "className": "align-middle"
                }
            ]
        });

        // Abrir modal ao clicar no botão Visualizar
        $(document).on('click', '.visualizar-btn', function() {
            const version = $(this).data('version');
            $('#versaoTitulo').text(version);
            $('#modalLoading').show();
            $('#modalContent').hide();
            $('#detalhesModal').modal('show');

            // Simular carregamento (no futuro pode ser AJAX)
            setTimeout(function() {
                if (versoesData[version]) {
                    $('#detalhesVersao').html(versoesData[version].detalhes);
                } else {
                    $('#detalhesVersao').html(`
                        <div class="alert alert-warning">
                            Detalhes da versão não encontrados.
                        </div>
                    `);
                }

                $('#modalLoading').hide();
                $('#modalContent').show();
            }, 500);
        });
    });
</script>