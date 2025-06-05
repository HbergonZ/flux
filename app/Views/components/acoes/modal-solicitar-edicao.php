<div class="modal fade" id="solicitarEdicaoModal" tabindex="-1" role="dialog" aria-labelledby="solicitarEdicaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitarEdicaoModalLabel">Solicitar Edição de Ação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSolicitarEdicao" method="post" action="<?= site_url('acoes/solicitar-edicao') ?>">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                <input type="hidden" name="id" id="solicitarEdicaoId">
                <input type="hidden" name="id_etapa" value="<?= $tipoOrigem === 'etapa' ? $idOrigem : '' ?>">
                <input type="hidden" name="id_projeto" value="<?= $tipoOrigem === 'projeto' ? $idOrigem : ($tipoOrigem === 'etapa' ? $etapa['id_projeto'] : '') ?>">

                <div class="modal-body">
                    <div id="alertNenhumaAlteracao" class="alert alert-warning d-none">
                        Você não fez nenhuma alteração nos campos. Modifique pelo menos um campo para enviar a solicitação.
                    </div>

                    <div class="form-group">
                        <label for="solicitarEdicaoNome">Nome*</label>
                        <input type="text" class="form-control" id="solicitarEdicaoNome" name="nome" required maxlength="255">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoResponsavel">Responsável</label>
                                <input type="text" class="form-control" id="solicitarEdicaoResponsavel" name="responsavel" maxlength="255">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarEdicaoTempoEstimado">Tempo Estimado (dias)</label>
                                <input type="number" class="form-control" id="solicitarEdicaoTempoEstimado" name="tempo_estimado_dias">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitarEdicaoEntregaEstimada">Entrega Estimada</label>
                                <input type="date" class="form-control" id="solicitarEdicaoEntregaEstimada" name="entrega_estimada">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoDataInicio">Data Início Real</label>
                                <input type="date" class="form-control" id="solicitarEdicaoDataInicio" name="data_inicio">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitarEdicaoDataFim">Data Fim Real</label>
                                <input type="date" class="form-control" id="solicitarEdicaoDataFim" name="data_fim">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="solicitarEdicaoStatus">Status</label>
                        <select class="form-control" id="solicitarEdicaoStatus" name="status">
                            <option value="Não iniciado">Não iniciado</option>
                            <option value="Em andamento">Em andamento</option>
                            <option value="Paralisado">Paralisado</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Evidências (obrigatório se estiver definindo data fim)</label>
                        <textarea class="form-control" name="evidencias" rows="3"
                            <?= !empty($formOriginalData['data_fim']) && empty($this->request->getPost('data_fim')) ? '' : 'required' ?>>
    </textarea>
                        <small class="form-text text-muted">
                            Descreva as evidências que comprovam o andamento ou conclusão desta ação.
                            Se estiver definindo uma data fim, este campo é obrigatório.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="solicitarEdicaoJustificativa">Justificativa para as alterações*</label>
                        <textarea class="form-control" id="solicitarEdicaoJustificativa" name="justificativa" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
                </div>
            </form>
        </div>
    </div>
</div>