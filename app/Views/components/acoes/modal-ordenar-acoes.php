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
                        Selecione a nova posição para cada ação (os itens trocarão de lugar)
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Ação</th>
                                <th style="width: 120px;">Posição Atual</th>
                                <th style="width: 120px;">Nova Posição</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($acoes as $index => $acao): ?>
                                <tr data-id="<?= $acao['id_acao'] ?>">
                                    <td><?= $acao['nome'] ?></td>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td>
                                        <select name="ordem[<?= $acao['id_acao'] ?>]"
                                            class="form-control form-control-sm ordem-select"
                                            data-current="<?= $index + 1 ?>">
                                            <?php for ($i = 1; $i <= count($acoes); $i++): ?>
                                                <option value="<?= $i ?>" <?= $i == ($index + 1) ? 'selected' : '' ?>>
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