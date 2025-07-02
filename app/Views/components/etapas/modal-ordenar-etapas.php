<div class="modal fade" id="ordenarEtapasModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ordenar Etapas</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formOrdenarEtapas" action="<?= site_url("etapas/salvar-ordem/$idProjeto") ?>">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        Selecione a nova posição para cada etapa (a posição atual permanecerá visível como referência)
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Etapa</th>
                                <th style="width: 120px;">Ordem atual</th>
                                <th style="width: 120px;">Nova ordem</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaOrdenacao">
                            <?php foreach ($etapas as $etapa): ?>
                                <tr data-id="<?= $etapa['id'] ?>">
                                    <td><?= $etapa['nome'] ?></td>
                                    <td class="text-center"><?= $etapa['ordem'] ?></td>
                                    <td>
                                        <select name="ordem[<?= $etapa['id'] ?>]"
                                            class="form-control form-control-sm ordem-select"
                                            data-original="<?= $etapa['ordem'] ?>">
                                            <?php for ($i = 1; $i <= count($etapas); $i++): ?>
                                                <option value="<?= $i ?>" <?= $i == $etapa['ordem'] ? 'selected' : '' ?>>
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