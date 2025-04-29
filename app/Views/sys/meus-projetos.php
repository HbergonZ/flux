<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Meus Projetos</h1>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-md-5 mx-3">
        <div class="card-body">
            <form id="formFiltros">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterName">Nome</label>
                            <input type="text" class="form-control" id="filterName" name="nome" placeholder="Filtrar por nome">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filterStatus">Status</label>
                            <select class="form-control" id="filterStatus" name="status">
                                <option value="">Todos</option>
                                <option value="Em andamento">Em andamento</option>
                                <option value="Finalizado">Finalizado</option>
                                <option value="Paralisado">Paralisado</option>
                                <option value="Não iniciado">Não iniciado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="button" id="btnLimparFiltros" class="btn btn-secondary btn-sm mr-2">
                                <i class="fas fa-broom"></i> Limpar
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Projetos -->
    <div class="card shadow mb-4 mx-md-5 mx-3">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Projetos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Nome</th>
                            <th>Objetivo</th>
                            <th>Perspectiva Estratégica</th>
                            <th>Status</th>
                            <th>Data de Início</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($projetos)) : ?>
                            <?php foreach ($projetos as $projeto) : ?>
                                <tr>
                                    <td><?= esc($projeto['nome']) ?></td>
                                    <td><?= esc($projeto['objetivo']) ?></td>
                                    <td><?= esc($projeto['perspectiva_estrategica'] ?? 'N/A') ?></td>
                                    <td class="text-center">
                                        <?php
                                        $badge_class = [
                                            'Em andamento' => 'badge-primary',
                                            'Não iniciado' => 'badge-secondary',
                                            'Finalizado' => 'badge-success',
                                            'Paralisado' => 'badge-warning'
                                        ][$projeto['status']] ?? 'badge-light';
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= $projeto['status'] ?></span>
                                    </td>
                                    <td class="text-center"><?= $projeto['data_formatada'] ?></td>
                                    <td class="text-center">
                                        <a href="<?= site_url('visao-projeto/' . $projeto['id']) ?>"
                                            class="btn btn-info btn-sm"
                                            title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center">Nenhum projeto encontrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    $(document).ready(function() {
        // Inicialização do DataTable
        var dataTable = $('#dataTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
            },
            responsive: true,
            lengthMenu: [5, 10, 25, 50],
            pageLength: 10,
            dom: '<"top"<"float-left"l><"float-right"f>>rt<"bottom"ip>',
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_length select').addClass('form-control form-control-sm');
            }
        });

        // Configuração do AJAX
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
        });

        // Filtros
        $('#formFiltros').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?= site_url('meus-projetos/filtrar') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        dataTable.clear();

                        $.each(response.data, function(_, projeto) {
                            var badge_class = {
                                'Em andamento': 'badge-primary',
                                'Não iniciado': 'badge-secondary',
                                'Finalizado': 'badge-success',
                                'Paralisado': 'badge-warning'
                            } [projeto.status] || 'badge-light';

                            dataTable.row.add([
                                proyecto.nome,
                                proyecto.objetivo,
                                proyecto.perspectiva_estrategica || 'N/A',
                                `<span class="badge ${badge_class}">${projeto.status}</span>`,
                                proyecto.data_formatada,
                                `<a href="<?= site_url('visao-projeto/') ?>${projeto.id}" class="btn btn-info btn-sm" title="Visualizar">
                    <i class="fas fa-eye"></i>
                 </a>`
                            ]).draw(false);
                        });
                    }
                }
            });
        });

        // Limpar filtros
        $('#btnLimparFiltros').click(function() {
            $('#formFiltros')[0].reset();
            $('#formFiltros').submit();
        });
    });
</script>