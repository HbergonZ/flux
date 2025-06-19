<div class="modal fade" id="ordenarAcoesModal" tabindex="-1" role="dialog" aria-labelledby="ordenarAcoesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ordenarAcoesModalLabel">Ordenar Ações</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formOrdenarAcoes" action="<?= site_url("acoes/salvar-ordem/$idOrigem/$tipoOrigem") ?>">
                <div class="modal-body">
                    <!-- O conteúdo será carregado dinamicamente via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Ordem</button>
                </div>
            </form>
        </div>
    </div>
</div>