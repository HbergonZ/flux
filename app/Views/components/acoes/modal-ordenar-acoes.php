<div class="modal fade" id="ordenarAcoesModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ordenar Ações</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formOrdenarAcoes" action="<?= site_url("acoes/salvar-ordem/$idOrigem/$tipoOrigem") ?>">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        Selecione a nova posição para cada ação (a posição atual permanecerá visível como referência)
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ação</th>
                                <th style="width: 120px;">Ordem atual</th>
                                <th style="width: 120px;">Nova ordem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acoes as $acao): ?>
                                <tr data-id="<?= $acao['id'] ?>">
                                    <td><?= $acao['nome'] ?></td>
                                    <td class="text-center"><?= $acao['ordem'] ?></td>
                                    <td>
                                        <select name="ordem[<?= $acao['id'] ?>]"
                                            class="form-control form-control-sm ordem-select"
                                            data-original="<?= $acao['ordem'] ?>">
                                            <?php for ($i = 1; $i <= count($acoes); $i++): ?>
                                                <option value="<?= $i ?>" <?= $i == $acao['ordem'] ? 'selected' : '' ?>>
                                                    <?= $i ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Ordem</button>
                </div>
            </form>
        </div>
    </div>
</div>