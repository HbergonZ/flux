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
<script>
    var eixos = <?= json_encode($eixos, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" />
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
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
        $(document).on('click', '.avaliar-btn', function() {
            var id = $(this).data('id');
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
                        var data = response.data;
                        $('#solicitacaoId').val(id);
                        // DADOS ATUAIS ---------------------------
                        let htmlAtuais = '';
                        if (data.tipo && data.tipo.toLowerCase() === 'inclusão') {
                            htmlAtuais = `
<tr><th width="30%">Tipo</th><td>Novo(a) ${capitalize(data.nivel)}</td></tr>
<tr><th width="30%">Status</th><td><span class="badge badge-info">Novo Registro</span></td></tr>`;
                        } else {
                            let respJaExibido = false;
                            for (let key in data.dados_atuais) {
                                if (key === 'equipe' || key === 'ordem' || key === 'id') continue;
                                // Exibir Projeto por nome nos atuais
                                if (key === 'id_projeto') {
                                    htmlAtuais += `
<tr>
    <th width="30%">Projeto</th>
    <td>` + (data.nome_projeto ?? data.dados_atuais[key]) + `</td>
</tr>`;
                                }
                                // Exibir Plano por nome nos atuais
                                else if (key === 'id_plano') {
                                    htmlAtuais += `
<tr>
    <th width="30%">Plano</th>
    <td>` + (data.nome_plano ?? data.dados_atuais[key]) + `</td>
</tr>`;
                                } else if (key === 'id_eixo') {
                                    htmlAtuais += `
<tr>
    <th width="30%">Eixo</th>
    <td>` + (eixos[data.dados_atuais[key]] ?? '<span class="text-muted">Não informado</span>') + `</td>
</tr>`;
                                } else if (key === 'evidencias') {
                                    let qtdEv = data.dados_atuais.total_evidencias !== undefined ?
                                        data.dados_atuais.total_evidencias :
                                        (Array.isArray(data.dados_atuais.evidencias) ? data.dados_atuais.evidencias.length : 0);
                                    htmlAtuais += `
<tr>
    <th width="30%">Evidências</th>
    <td><span class="badge badge-secondary">Qtd. evidências: <b>${qtdEv}</b></span></td>
</tr>`;
                                } else if (key === 'indicadores') {
                                    let qtdInd = data.dados_atuais.total_indicadores !== undefined ?
                                        data.dados_atuais.total_indicadores :
                                        (Array.isArray(data.dados_atuais.indicadores) ? data.dados_atuais.indicadores.length : 0);
                                    htmlAtuais += `
<tr>
    <th width="30%">Indicadores</th>
    <td><span class="badge badge-secondary">Qtd. indicadores: <b>${qtdInd}</b></span></td>
</tr>`;
                                } else if (key === 'responsaveis_nomes' && !respJaExibido) {
                                    htmlAtuais += `
<tr>
    <th width="30%">Responsáveis</th>
    <td>` + formatFieldValue(data.dados_atuais[key], key) + `</td>
</tr>`;
                                    respJaExibido = true;
                                } else if (key === 'priorizacao_gab') {
                                    htmlAtuais += `
<tr>
    <th width="30%">Priorização GAB</th>
    <td>` + formatFieldValue(data.dados_atuais[key], key) + `</td>
</tr>`;
                                } else if (
                                    key !== 'total_evidencias' &&
                                    key !== 'total_indicadores' &&
                                    key !== 'responsaveis' &&
                                    key !== 'responsaveis_nomes' &&
                                    key !== 'id_eixo' &&
                                    key !== 'id_projeto' &&
                                    key !== 'id_plano'
                                ) {
                                    htmlAtuais += `
<tr>
    <th width="30%">` + formatFieldName(key) + `</th>
    <td>` + formatFieldValue(data.dados_atuais[key], key) + `</td>
</tr>`;
                                }
                            }
                        }
                        $('#tabelaDadosAtuais').html(htmlAtuais);
                        // ----- ALTERAÇÕES SOLICITADAS --------
                        let htmlAlterados = '';
                        if (data.tipo && data.tipo.toLowerCase() === 'inclusão') {
                            for (let key in data.dados_alterados) {
                                if (
                                    key === 'ordem' ||
                                    key === 'id' ||
                                    key === 'id_solicitante' ||
                                    key === 'total_evidencias' ||
                                    key === 'total_indicadores'
                                ) continue;
                                // Exibir Projeto por nome na inclusão
                                if (key === 'id_projeto') {
                                    htmlAlterados += `
<tr>
    <th width="30%">Projeto</th>
    <td class="text-success"><strong>${data.nome_projeto ?? data.dados_alterados[key]}</strong></td>
</tr>`;
                                }
                                // Exibir Plano por nome na inclusão
                                else if (key === 'id_plano') {
                                    htmlAlterados += `
<tr>
    <th width="30%">Plano</th>
    <td class="text-success"><strong>${data.nome_plano ?? data.dados_alterados[key]}</strong></td>
</tr>`;
                                } else if (key === 'id_eixo') {
                                    htmlAlterados += `
<tr>
    <th width="30%">Eixo</th>
    <td class="text-success"><strong>` + (eixos[data.dados_alterados[key]] ?? '<span class="text-muted">Não informado</span>') + `</strong></td>
</tr>`;
                                } else {
                                    htmlAlterados += `
<tr>
    <th width="30%">` + formatFieldName(key) + `</th>
    <td class="text-success"><strong>` + formatFieldValue(data.dados_alterados[key], key) + `</strong></td>
</tr>`;
                                }
                            }
                        } else if (data.tipo && data.tipo.toLowerCase() === 'exclusão') {
                            htmlAlterados = `
<tr><th width="30%">Tipo</th><td class="text-danger"><strong>Exclusão de ${capitalize(data.nivel)}</strong></td></tr>
<tr><th width="30%">Status</th><td><span class="badge badge-danger">Registro será removido</span></td></tr>`;
                        } else {
                            for (let key in data.dados_alterados) {
                                if (
                                    key === 'equipe' ||
                                    key === 'ordem' ||
                                    key === 'id' ||
                                    key === 'total_evidencias' ||
                                    key === 'total_indicadores'
                                ) continue;
                                // Exibir Projeto por nome na edição
                                if (key === 'id_projeto') {
                                    htmlAlterados += `
<tr>
    <th width="30%">Projeto</th>
    <td><strong>${data.nome_projeto ?? data.dados_alterados[key]}</strong></td>
</tr>`;
                                }
                                // Exibir Plano por nome na edição
                                else if (key === 'id_plano') {
                                    htmlAlterados += `
<tr>
    <th width="30%">Plano</th>
    <td><strong>${data.nome_plano ?? data.dados_alterados[key]}</strong></td>
</tr>`;
                                } else if (key === 'id_eixo') {
                                    htmlAlterados += `
<tr>
    <th width="30%">Eixo</th>
    <td>` + (eixos[data.dados_alterados[key]] ?? '<span class="text-muted">Não informado</span>') + `</td>
</tr>`;
                                } else if (key === 'evidencias') {
                                    if (data.dados_alterados.evidencias.adicionar && data.dados_alterados.evidencias.adicionar.length > 0) {
                                        htmlAlterados += `
<tr>
    <th width="30%">Evidências a Adicionar</th>
    <td>
        <div class="text-success">`;
                                        data.dados_alterados.evidencias.adicionar.forEach(ev => {
                                            htmlAlterados += `
            <div class="mb-3 p-2 border border-success rounded">
                ${formatEvidence(ev)}
            </div>`;
                                        });
                                        htmlAlterados += `</div></td></tr>`;
                                    }
                                    if (data.dados_alterados.evidencias.remover && data.dados_alterados.evidencias.remover.length > 0) {
                                        htmlAlterados += `
<tr>
    <th width="30%">Evidências a Remover</th>
    <td>
        <div class="text-danger">`;
                                        data.dados_alterados.evidencias.remover.forEach(ev => {
                                            htmlAlterados += `
            <div class="mb-3 p-2 border border-danger rounded">
                ${formatEvidence(ev)}
            </div>`;
                                        });
                                        htmlAlterados += `</div></td></tr>`;
                                    }
                                } else if (key === 'indicadores') {
                                    if (data.dados_alterados.indicadores.adicionar && data.dados_alterados.indicadores.adicionar.length > 0) {
                                        htmlAlterados += `
<tr>
    <th width="30%">Indicadores a Adicionar</th>
    <td>
        <div class="text-success">`;
                                        data.dados_alterados.indicadores.adicionar.forEach(ev => {
                                            htmlAlterados += `
            <div class="mb-3 p-2 border border-success rounded">
                ${formatIndicator(ev)}
            </div>`;
                                        });
                                        htmlAlterados += `</div></td></tr>`;
                                    }
                                    if (data.dados_alterados.indicadores.remover && data.dados_alterados.indicadores.remover.length > 0) {
                                        htmlAlterados += `
<tr>
    <th width="30%">Indicadores a Remover</th>
    <td>
        <div class="text-danger">`;
                                        data.dados_alterados.indicadores.remover.forEach(ev => {
                                            htmlAlterados += `
            <div class="mb-3 p-2 border border-danger rounded">
                ${formatIndicator(ev)}
            </div>`;
                                        });
                                        htmlAlterados += `</div></td></tr>`;
                                    }
                                } else if (key === 'responsaveis') {
                                    htmlAlterados += `
<tr>
    <th width="30%">Responsáveis</th>
    <td>` + formatFieldValue(data.dados_alterados[key], key) + `</td>
</tr>`;
                                } else if (
                                    typeof data.dados_alterados[key] === 'object' &&
                                    data.dados_alterados[key] !== null &&
                                    data.dados_alterados[key].hasOwnProperty('de') &&
                                    data.dados_alterados[key].hasOwnProperty('para')
                                ) {
                                    htmlAlterados += `
<tr>
    <th width="30%">` + formatFieldName(key) + `</th>
    <td>` + formatFieldValue(data.dados_alterados[key].para, key) + `</td>
</tr>`;
                                } else if (key !== 'responsaveis_nomes' && key !== 'id_eixo' && key !== 'id_projeto' && key !== 'id_plano') {
                                    htmlAlterados += `
<tr>
    <th width="30%">` + formatFieldName(key) + `</th>
    <td>` + formatFieldValue(data.dados_alterados[key], key) + `</td>
</tr>`;
                                }
                            }
                        }
                        $('#tabelaDadosAlterados').html(htmlAlterados);
                        // Info solicitante
                        $('#nomeSolicitante').text(data.solicitante || 'Não informado');
                        $('#dataSolicitacao').text(data.data_solicitacao ?
                            new Date(data.data_solicitacao).toLocaleString('pt-BR') : 'Não informado');
                        if (data.justificativa_solicitante && data.justificativa_solicitante.trim()) {
                            $('#justificativaSolicitacao').html(data.justificativa_solicitante);
                        } else {
                            $('#justificativaSolicitacao').html('<em class="text-muted">Nenhuma justificativa fornecida.</em>');
                        }
                        $('#modalLoading').hide();
                        $('#modalContent').show();
                    } else {
                        Swal.fire('Erro', response.message || 'Erro ao carregar solicitação', 'error');
                        $('#avaliarModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Erro de Comunicação',
                        text: 'Falha ao carregar dados da solicitação. Verifique sua conexão e tente novamente.',
                        icon: 'error'
                    });
                    $('#avaliarModal').modal('hide');
                }
            });
        });
        // Processamento aceitar/recusar
        $('.aceitar-btn, .recusar-btn').click(function() {
            var acao = $(this).hasClass('aceitar-btn') ? 'aceitar' : 'recusar';
            var formData = $('#formAvaliar').serialize() + '&acao=' + acao;
            var buttons = $('.aceitar-btn, .recusar-btn');
            buttons.prop('disabled', true);
            if (acao === 'aceitar') $('.aceitar-btn').html('<i class="fas fa-spinner fa-spin"></i> Processando...');
            if (acao === 'recusar') $('.recusar-btn').html('<i class="fas fa-spinner fa-spin"></i> Processando...');
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
            if (key === 'responsaveis_nomes' && Array.isArray(value)) {
                return value.length > 0 ? value.join(', ') : '<span class="text-muted">Nenhum responsável</span>';
            }
            if (key === 'responsaveis' && typeof value === 'object') {
                let html = '';
                if (value.adicionar_nomes && value.adicionar_nomes.length) {
                    html += '<span class="text-success"><b>Adicionar:</b> ' + value.adicionar_nomes.join(', ') + '</span><br>';
                }
                if (value.remover_nomes && value.remover_nomes.length) {
                    html += '<span class="text-danger"><b>Remover:</b> ' + value.remover_nomes.join(', ') + '</span>';
                }
                if (!html) html = '<span class="text-muted">Sem alterações</span>';
                return html;
            }
            if (key === 'priorizacao_gab') {
                if (value == 1) return '<span class="badge badge-success">Priorizado</span>';
                if (value == 0) return '<span class="badge badge-danger">Não priorizado</span>';
                if (typeof value === 'boolean')
                    return value ? '<span class="badge badge-success">Priorizado</span>' : '<span class="badge badge-danger">Não priorizado</span>';
                if (typeof value === 'string')
                    return value.charAt(0).toUpperCase() + value.slice(1);
                if (typeof value === 'object' && value !== null) {
                    return Object.values(value).join(', ');
                }
            }
            if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}$/)) {
                const [y, m, d] = value.split('-');
                return `${d}/${m}/${y}`;
            }
            if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
                const [date, time] = value.split(' ');
                const [y, m, d] = date.split('-');
                return `${d}/${m}/${y} ${time}`;
            }
            if (Array.isArray(value)) {
                return value.join(', ');
            }
            if (typeof value === 'object' && value !== null) {
                return Object.values(value).join(', ');
            }
            return value;
        }

        function formatEvidence(evidence) {
            let conteudo = evidence.link || evidence.evidencia || evidence.conteudo || '';
            let descricao = evidence.descricao || evidence.descricao_evidencia || '';
            let isLink = (
                evidence.tipo === 'link' ||
                evidence.tipo === 'url' ||
                (typeof conteudo === 'string' && (conteudo.startsWith('http://') || conteudo.startsWith('https://')))
            );
            let html = '<div class="mb-2">';
            html += '<div class="mb-2"><strong>Evidência:</strong></div>';
            if (isLink && conteudo) {
                html += `<div class="mb-2">
                    <a href="${conteudo}" class="btn btn-primary btn-sm text-truncate" style="max-width:160px;" target="_blank" rel="noopener">
                        <i class="fas fa-external-link-alt"></i> Acessar
                    </a>
                </div>`;
            } else if (conteudo) {
                html += `<div class="mb-2 text-break">${conteudo}</div>`;
            } else {
                html += `<div class="mb-2 text-muted">Sem evidência informada</div>`;
            }
            if (descricao) {
                html += `<div class="mb-1"><strong>Descrição:</strong></div>
                         <div class="mb-2 text-break">${descricao}</div>`;
            }
            html += '</div>';
            return html;
        }

        function formatIndicator(ind) {
            let nome = ind.nome || ind.indicador || ind.conteudo || '';
            let valor = ind.valor || '';
            let unidade = ind.unidade || '';
            let descricao = ind.descricao || '';
            let html = '<div class="mb-2">';
            html += '<div class="mb-2"><strong>Indicador:</strong></div>';
            html += `<div class="mb-2 text-break">${nome ? nome : '<span class="text-muted">N/A</span>'}</div>`;
            if (descricao) {
                html += '<div class="mb-1"><strong>Descrição:</strong></div>';
                html += `<div class="mb-2 text-break">${descricao}</div>`;
            }
            if (valor || unidade) {
                html += `<div class="mb-2"><span class="text-primary">${valor}${unidade ? ' ' + unidade : ''}</span></div>`;
            }
            html += '</div>';
            return html;
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    });
</script>