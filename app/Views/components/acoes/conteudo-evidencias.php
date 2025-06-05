<!-- Cabeçalho com informações da ação -->
<div class="card mb-4 border-primary">
    <div class="card-body py-2">
        <h6 class="card-title text-primary mb-1">
            <i class="fas fa-tasks mr-2"></i>Ação Relacionada
        </h6>
        <p class="card-text font-weight-bold mb-0"><?= esc($acao['nome']) ?></p>
    </div>
</div>

<!-- Formulário de adição de evidências -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-plus-circle mr-2"></i>Adicionar Nova Evidência
        </h6>
    </div>
    <div class="card-body">
        <form id="formAdicionarEvidencia">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
            <input type="hidden" name="acao_id" value="<?= esc($acao['id']) ?>">

            <div class="form-group">
                <label class="font-weight-bold">Tipo de Evidência</label>
                <div class="d-flex">
                    <div class="form-check mr-3">
                        <input class="form-check-input" type="radio" name="tipo" id="tipoTexto" value="texto" checked>
                        <label class="form-check-label" for="tipoTexto">Texto</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo" id="tipoLink" value="link">
                        <label class="form-check-label" for="tipoLink">Link</label>
                    </div>
                </div>
            </div>

            <div class="form-group" id="grupoTexto">
                <label for="evidenciaTexto">Evidência (Texto)*</label>
                <textarea class="form-control" id="evidenciaTexto" name="evidencia_texto" rows="3" required></textarea>
            </div>

            <div class="form-group d-none" id="grupoLink">
                <label for="evidenciaLink">URL*</label>
                <input type="url" class="form-control" id="evidenciaLink" name="evidencia_link" required placeholder="https://exemplo.com">
            </div>

            <div class="form-group">
                <label for="descricaoEvidencia">Descrição*</label>
                <textarea class="form-control" id="descricaoEvidencia" name="descricao" rows="2" required placeholder="Explique a evidência"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-plus-circle mr-2"></i>Adicionar Evidência
            </button>
        </form>
    </div>
</div>

<!-- Lista de evidências cadastradas -->
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-list-ul mr-2"></i>Evidências Cadastradas
        </h6>
        <span class="badge badge-primary badge-pill"><?= $totalEvidencias ?></span>
    </div>
    <div class="card-body p-0">
        <div class="overflow-auto" style="max-height: 30vh;">
            <?php if (empty($evidencias)): ?>
                <div class="alert alert-info text-center py-4 m-3">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <p class="mb-0">Nenhuma evidência cadastrada ainda.</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($evidencias as $index => $evidencia): ?>
                        <div class="list-group-item mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="badge badge-light mr-2">#<?= $totalEvidencias - $index ?></span>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($evidencia['created_at'])) ?>
                                            </small>
                                        </div>
                                        <button class="btn btn-sm btn-danger btn-remover-evidencia" data-id="<?= esc($evidencia['id']) ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
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
                                            <small class="d-block text-muted mt-1 text-truncate" style="max-width: 600px;">
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
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>