<div class="modal fade" id="evidenciasAcaoModal" tabindex="-1" role="dialog" aria-labelledby="evidenciasAcaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt mr-2"></i>Gerenciar Evidências
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="conteudoEvidenciasModal">
                <?= view('components/acoes/conteudo-evidencias', [
                    'acao' => $acao,
                    'evidencias' => $evidencias,
                    'totalEvidencias' => count($evidencias)
                ]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Fechar
                </button>
            </div>
        </div>
    </div>
</div>