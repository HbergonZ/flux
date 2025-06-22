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
                "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
            },
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [5, 10, 25, 50, 100],
            "pageLength": 10,
            "order": [
                [4, 'desc']
            ]
        });

        // Abre modal de avaliação
        $(document).on('click', '.avaliar-btn', function() {
            var id = $(this).data('id');
            console.log('[FRONTEND] Botão avaliar clicado para solicitação ID:', id);

            // Mostra loading e reseta o modal
            $('#formAvaliar')[0].reset();
            $('#modalLoading').show();
            $('#modalContent').hide();
            $('#avaliarModal').modal('show');

            $.ajax({
                url: '<?= site_url('solicitacoes/avaliar') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    console.log('[FRONTEND] Enviando requisição AJAX para:', this.url);
                },
                success: function(response) {
                    console.groupCollapsed('[FRONTEND] Resposta completa da API - Solicitação ID:', id);
                    console.log('Status:', response.success ? 'SUCESSO' : 'ERRO');
                    console.log('Dados básicos:', {
                        tipo: response.tipo,
                        nivel: response.nivel,
                        solicitante: response.data.solicitante
                    });

                    console.log('Dados atuais (antes das alterações):', response.dados_atuais);
                    console.log('Dados alterados (solicitados):', response.dados_alterados);

                    if (response.dados_alterados.evidencias) {
                        console.group('Evidências:');
                        console.log('Total a adicionar:', response.dados_alterados.evidencias.adicionar?.length || 0);
                        console.log('Total a remover:', response.dados_alterados.evidencias.remover?.length || 0);
                        console.log('Detalhes:', response.dados_alterados.evidencias);
                        console.groupEnd();
                    }
                    console.groupEnd();

                    if (response.success) {
                        $('#solicitacaoId').val(id);

                        // Preenche tabela de dados atuais
                        let htmlAtuais = '';
                        if (response.tipo.toLowerCase() === 'inclusão') {
                            htmlAtuais = `
                        <tr><th width="30%">Tipo</th><td>Novo(a) ${response.nivel.charAt(0).toUpperCase() + response.nivel.slice(1)}</td></tr>
                        <tr><th width="30%">Status</th><td><span class="badge badge-info">Novo Registro</span></td></tr>`;
                        } else {
                            for (let key in response.dados_atuais) {
                                if (key !== 'equipe') {
                                    htmlAtuais += `
                                <tr>
                                    <th width="30%">${formatFieldName(key)}</th>
                                    <td>${formatFieldValue(response.dados_atuais[key], key)}</td>
                                </tr>`;
                                }
                            }
                        }
                        $('#tabelaDadosAtuais').html(htmlAtuais);

                        // Preenche tabela de alterações
                        let htmlAlterados = '';
                        if (response.tipo.toLowerCase() === 'inclusão') {
                            for (let key in response.dados_alterados) {
                                htmlAlterados += `
                            <tr>
                                <th width="30%">${formatFieldName(key)}</th>
                                <td class="text-success"><strong>${formatFieldValue(response.dados_alterados[key], key)}</strong></td>
                            </tr>`;
                            }
                        } else if (response.tipo.toLowerCase() === 'exclusão') {
                            htmlAlterados = `
                        <tr><th width="30%">Tipo</th><td class="text-danger"><strong>Exclusão de ${response.nivel.charAt(0).toUpperCase() + response.nivel.slice(1)}</strong></td></tr>
                        <tr><th width="30%">Status</th><td><span class="badge badge-danger">Registro será removido</span></td></tr>`;
                        } else {
                            // Processa alterações normais
                            for (let key in response.dados_alterados) {
                                if (key === 'evidencias') {
                                    // Evidências a adicionar
                                    if (response.dados_alterados.evidencias.adicionar?.length > 0) {
                                        htmlAlterados += `
                                    <tr>
                                        <th width="30%">Evidências a Adicionar</th>
                                        <td>
                                            <div class="text-success">`;

                                        response.dados_alterados.evidencias.adicionar.forEach(ev => {
                                            htmlAlterados += `
                                        <div class="mb-3 p-2 border border-success rounded">
                                            ${formatEvidence(ev)}
                                        </div>`;
                                        });

                                        htmlAlterados += `</div></td></tr>`;
                                    }

                                    // Evidências a remover
                                    if (response.dados_alterados.evidencias.remover?.length > 0) {
                                        htmlAlterados += `
                                    <tr>
                                        <th width="30%">Evidências a Remover</th>
                                        <td>
                                            <div class="text-danger">`;

                                        response.dados_alterados.evidencias.remover.forEach(ev => {
                                            htmlAlterados += `
                                        <div class="mb-3 p-2 border border-danger rounded">
                                            ${formatEvidence(ev)}
                                        </div>`;
                                        });

                                        htmlAlterados += `</div></td></tr>`;
                                    }
                                } else if (key !== 'equipe') {
                                    htmlAlterados += `
                                <tr>
                                    <th width="30%">${formatFieldName(key)}</th>
                                    <td>${formatFieldValue(response.dados_alterados[key], key)}</td>
                                </tr>`;
                                }
                            }
                        }
                        $('#tabelaDadosAlterados').html(htmlAlterados);

                        // Preenche informações do solicitante
                        $('#nomeSolicitante').text(response.data.solicitante || 'Não informado');
                        $('#dataSolicitacao').text(response.data.data_solicitacao ?
                            new Date(response.data.data_solicitacao).toLocaleString('pt-BR') : 'Não informado');

                        if (response.data.justificativa_solicitante?.trim()) {
                            $('#justificativaSolicitacao').html(response.data.justificativa_solicitante);
                        }

                        // Mostra conteúdo
                        $('#modalLoading').hide();
                        $('#modalContent').show();
                        console.log('[FRONTEND] Modal preenchido com sucesso');
                    } else {
                        console.error('[FRONTEND] Erro na resposta:', response.message);
                        Swal.fire('Erro', response.message || 'Erro ao carregar solicitação', 'error');
                        $('#avaliarModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.groupCollapsed('[FRONTEND] Erro na requisição AJAX');
                    console.error('Status:', status);
                    console.error('Mensagem:', error);
                    console.error('Resposta:', xhr.responseText);
                    console.groupEnd();

                    Swal.fire({
                        title: 'Erro de Comunicação',
                        text: 'Falha ao carregar dados da solicitação. Verifique sua conexão e tente novamente.',
                        icon: 'error'
                    });
                    $('#avaliarModal').modal('hide');
                }
            });
        });

        // Funções auxiliares
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
                'ordem': 'Ordem'
            };
            return names[name] || name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        function formatFieldValue(value, key) {
            if (value === null || value === '' || value === undefined) {
                return '<span class="text-muted">Não informado</span>';
            }

            // Formatação especial para equipe
            if (key === 'equipe_real' && Array.isArray(value)) {
                return value.length > 0 ? value.join(', ') : '<span class="text-muted">Nenhum membro</span>';
            }

            // Formatação de datas
            if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}$/)) {
                const [y, m, d] = value.split('-');
                return `${d}/${m}/${y}`;
            }
            if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
                const [date, time] = value.split(' ');
                const [y, m, d] = date.split('-');
                return `${d}/${m}/${y} ${time}`;
            }

            return value;
        }

        function formatEvidence(evidence) {
            console.log('[FRONTEND] Formatando evidência:', evidence);

            if (!evidence) {
                return '<em class="text-muted">Sem conteúdo</em>';
            }

            let html = '<div class="p-2 mb-2">';

            // Determina o conteúdo real da evidência
            let conteudo = evidence.link || evidence.evidencia || evidence.conteudo;
            let isLink = false;

            // Verifica se é um link (começa com http ou https)
            if (typeof conteudo === 'string' && (conteudo.startsWith('http://') || conteudo.startsWith('https://'))) {
                isLink = true;
            }

            // Exibe o conteúdo
            if (isLink) {
                html += `<div class="mb-1"><strong>Evidência:</strong> <a href="${conteudo}" target="_blank">${conteudo}</a></div>`;
            } else if (conteudo) {
                html += `<div class="mb-1"><strong>Evidência:</strong> ${conteudo}</div>`;
            } else {
                html += `<div class="mb-1"><strong>Evidência:</strong> <em class="text-muted">Sem conteúdo</em></div>`;
            }

            // Descrição (se existir)
            if (evidence.descricao) {
                html += `<div class="mb-1"><strong>Descrição:</strong> ${evidence.descricao}</div>`;
            }

            // ID (se existir)
            if (evidence.id) {
                html += `<div class="text-muted small">ID: ${evidence.id}</div>`;
            }

            html += '</div>';
            return html;
        }

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
                    } else if (xhr.statusText) {
                        errorMsg += ` (${xhr.statusText})`;
                    }
                    Swal.fire('Erro!', errorMsg, 'error');
                    $('#avaliarModal').modal('hide');
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