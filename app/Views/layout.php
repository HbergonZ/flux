<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Flux</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('template/img/flux.ico'); ?>">



    <!-- Custom fonts for this template-->
    <link href=<?php echo base_url("template/vendor/fontawesome-free/css/all.min.css"); ?> rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href=<?php echo base_url("template/css/sb-admin-2.min.css"); ?> rel="stylesheet">
    <link href=<?php echo base_url("template/vendor/datatables/dataTables.bootstrap4.min.css"); ?> rel="stylesheet">


</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url('/'); ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-project-diagram"></i> <!-- Ícone mais relacionado a projetos -->
                </div>
                <div class="sidebar-brand-text mx-3">Flux</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <?php if (auth()->user()->inGroup('admin') || auth()->user()->inGroup('superadmin')): ?>
                <!-- Área Gerencial -->
                <div class="sidebar-heading">
                    Área Gerencial
                </div>

                <!-- Solicitações -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/solicitacoes'); ?>">
                        <i class="fas fa-edit"></i>
                        <span>Solicitações</span>
                        <?php if (isset($total_solicitacoes_pendentes) && $total_solicitacoes_pendentes > 0): ?>
                            <i class="fas fa-exclamation-circle text-warning ml-1"></i>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Histórico de Solicitações -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/historico-solicitacoes'); ?>">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Histórico de Solicitações</span>
                    </a>
                </li>

                <!-- Atribuir grupos -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/gerenciar-usuarios'); ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>Gerenciar Usuários</span>
                    </a>
                </li>

                <hr class="sidebar-divider d-none d-md-block">

            <?php endif; ?>

            <!-- Área do Usuário -->
            <div class="sidebar-heading">
                Área do Usuário
            </div>

            <!-- Minhas atividades -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo base_url('/planos'); ?>">
                    <i class="fas fa-tasks"></i>
                    <span>Visão Detalhada</span>
                </a>
            </li>

            <!-- Visão Geral -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo base_url('/visao-geral'); ?>">
                    <i class="fas fa-th-list"></i>
                    <span>Visão Geral</span>
                </a>
            </li>

            <!-- Minhas Solicitações -->
            <li class="nav-item">
                <a class="nav-link" href="<?php echo base_url('/minhas-solicitacoes'); ?>">
                    <i class="fas fa-inbox"></i>
                    <span>Minhas Solicitações</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>

        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                                aria-labelledby="searchDropdown">
                                <form class="form-inline mr-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small"
                                            placeholder="Search for..." aria-label="Search"
                                            aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter" id="contadorAcoesAtrasadas">0</span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Ações Atrasadas
                                </h6>
                                <div id="listaAcoesAtrasadas">
                                    <!-- Conteúdo será carregado via AJAX -->
                                    <div class="text-center py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Carregando...
                                    </div>
                                </div>
                                <a class="dropdown-item text-center small text-gray-500" href="<?= site_url('/planos') ?>">Ver todas as ações</a>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>


                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="text-right mr-3">
                                    <span class="d-none d-lg-inline text-gray-600 small">
                                        <?= auth()->user()->name ?? 'Usuário' ?><br>
                                        <small class="text-muted">
                                            <?= auth()->user()->getGroups()[0] ?? 'Sem grupo' ?>
                                        </small>
                                    </span>
                                </div>
                                <img class="img-profile rounded-circle"
                                    src=<?php echo base_url("template/img/undraw_profile.svg"); ?>>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Sair
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <?php echo $content; ?>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="fas fa-sign-out-alt mr-2"></i>Pronto para sair?
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                        <div>
                            <span class="font-weight-bold text-danger">Atenção!</span><br>
                            Você tem certeza que deseja encerrar sua sessão?
                        </div>
                    </div>
                    <p class="mb-0 text-muted">
                        Selecione <span class="font-weight-bold">"Sair"</span> abaixo se realmente deseja finalizar sua sessão atual.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </button>
                    <a class="btn btn-danger" href="<?php echo base_url('/logout'); ?>">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap core JavaScript-->
    <script src=<?php echo base_url("template/vendor/jquery/jquery.min.js"); ?>></script>
    <script src=<?php echo base_url("template/vendor/bootstrap/js/bootstrap.bundle.min.js"); ?>></script>

    <!-- Core plugin JavaScript-->
    <script src=<?php echo base_url("template/vendor/jquery-easing/jquery.easing.min.js"); ?>></script>

    <!-- Custom scripts for all pages-->
    <script src=<?php echo base_url("template/js/sb-admin-2.min.js"); ?>></script>

    <!-- Page level plugins -->
    <script src=<?php echo base_url("template/vendor/datatables/jquery.dataTables.min.js"); ?>></script>
    <script src=<?php echo base_url("template/vendor/datatables/dataTables.bootstrap4.min.js"); ?>></script>

    <script>
        $(document).ready(function() {
            // Carregar ações atrasadas
            function carregarAcoesAtrasadas() {
                $.ajax({
                    url: '<?= site_url('acoes/acoes-atrasadas-usuario') ?>',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const lista = $('#listaAcoesAtrasadas');
                            const contador = $('#contadorAcoesAtrasadas');

                            // Atualizar contador
                            contador.text(response.data.length);
                            contador.toggleClass('d-none', response.data.length === 0);

                            // Atualizar lista
                            if (response.data.length > 0) {
                                let html = '';
                                response.data.forEach(acao => {
                                    const diasAtraso = acao.dias_atraso;
                                    const dataFormatada = new Date(acao.entrega_estimada).toLocaleDateString('pt-BR');

                                    html += `
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-danger">
                                                <i class="fas fa-exclamation-triangle text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="small text-gray-500">
                                                <span class="font-weight-bold">Projeto:</span> ${acao.projeto_nome}
                                            </div>
                                            <span class="font-weight-bold">${acao.nome}</span>
                                            <div class="small">
                                                <span class="text-danger">Data máxima: ${acao.entrega_estimada_formatada}</span> |
                                                Atraso: ${diasAtraso} ${diasAtraso > 1 ? 'dias' : 'dia'}
                                            </div>
                                        </div>
                                    </a>`;
                                });

                                lista.html(html);
                            } else {
                                lista.html('<div class="text-center py-3 text-muted">Nenhuma ação atrasada</div>');
                            }
                        }
                    },
                    error: function() {
                        $('#listaAcoesAtrasadas').html('<div class="text-center py-3 text-danger">Erro ao carregar notificações</div>');
                    }
                });
            }

            // Carregar inicialmente
            carregarAcoesAtrasadas();

            // Atualizar a cada 5 minutos (300000 ms)
            setInterval(carregarAcoesAtrasadas, 300000);
        });
    </script>


</body>

</html>