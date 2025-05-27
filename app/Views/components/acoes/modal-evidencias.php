<div class="modal fade" id="evidenciasAcaoModal" tabindex="-1" role="dialog" aria-labelledby="evidenciasAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt mr-2"></i>Evidências da Ação: <?= esc($acao['nome']) ?>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formAdicionarEvidencia" class="mb-4">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    <input type="hidden" name="acao_id" value="<?= esc($acao['id']) ?>">

                    <div class="form-group">
                        <label class="font-weight-bold">Tipo de Evidência</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" id="tipoTexto" value="texto" checked>
                            <label class="form-check-label" for="tipoTexto">Texto</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo" id="tipoLink" value="link">
                            <label class="form-check-label" for="tipoLink">Link</label>
                        </div>
                    </div>

                    <div class="form-group" id="grupoTexto">
                        <label for="evidenciaTexto">Evidência (Texto)*</label>
                        <textarea class="form-control" id="evidenciaTexto" name="evidencia_texto" rows="3" required></textarea>
                    </div>

                    <div class="form-group d-none" id="grupoLink">
                        <label for="evidenciaLink">URL*</label>
                        <input type="url" class="form-control" id="evidenciaLink" name="evidencia_link" placeholder="https://exemplo.com">
                    </div>

                    <div class="form-group">
                        <label for="descricaoEvidencia">Descrição</label>
                        <textarea class="form-control" id="descricaoEvidencia" name="descricao" rows="2" placeholder="Explique a evidência (opcional)"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-plus-circle mr-2"></i>Adicionar Evidência
                    </button>
                </form>

                <hr class="mt-4 mb-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ul mr-2"></i>Evidências Cadastradas
                    </h5>
                    <span class="badge badge-primary badge-pill"><?= count($evidencias) ?></span>
                </div>

                <div class="overflow-auto" style="max-height: 20vh;">
                    <?php if (empty($evidencias)): ?>
                        <div class="alert alert-info text-center py-4">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p class="mb-0">Nenhuma evidência cadastrada ainda.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($evidencias as $index => $evidencia): ?>
                                <div class="list-group-item mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong>Evidência #<?= $totalEvidencias - $index ?></strong>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($evidencia['created_at'])) ?>
                                                </small>
                                            </div>

                                            <?php if ($evidencia['tipo'] === 'texto'): ?>
                                                <div class="bg-light p-3 rounded mb-2">
                                                    <?= nl2br(esc($evidencia['evidencia'])) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="mb-2">
                                                    <a href="<?= esc($evidencia['evidencia']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-external-link-alt mr-2"></i>Abrir Link
                                                    </a>
                                                    <small class="d-block text-muted mt-1">
                                                        <?= esc($evidencia['evidencia']) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($evidencia['descricao'])): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted d-block"><strong>Descrição:</strong></small>
                                                    <div class="bg-light p-2 rounded">
                                                        <?= nl2br(esc($evidencia['descricao'])) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (auth()->user()->inGroup('admin')): ?>
                                            <button class="btn btn-sm btn-danger ml-2 btn-remover-evidencia" data-id="<?= esc($evidencia['id']) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>