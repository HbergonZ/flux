<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Flux - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="<?php echo base_url('template/css/sb-admin-2.min.css'); ?>" rel="stylesheet">
</head>

<body class="bg-primary d-flex align-items-center" style="min-height:100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-9">
                <div class="card shadow-lg my-5 rounded-lg border-0">
                    <div class="card-body p-0">
                        <div class="row justify-content-center">
                            <div class="col-lg-11">
                                <div class="p-5">
                                    <div class="text-center">
                                        <!-- Ícone centralizado -->
                                        <div
                                            class="mx-auto d-flex align-items-center justify-content-center mb-4"
                                            style="width: 60px; height: 60px; background: linear-gradient(135deg,#2e59d9 60%,#224abe 100%); border-radius: 14px; box-shadow: 0 4px 12px 0 rgba(46,89,217,.13);">
                                            <i class="fas fa-project-diagram fa-2x text-white" style="transform: rotate(-15deg);"></i>
                                        </div>
                                        <h1 class="h3 text-gray-900 font-weight-bold mb-1">Bem-vindo(a) ao Flux</h1>
                                        <p class="text-secondary mb-4">Faça login para acessar sua conta</p>
                                    </div>
                                    <!-- Mensagens de erro/sucesso -->
                                    <?php if (session('error') !== null) : ?>
                                        <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
                                    <?php elseif (session('errors') !== null) : ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php if (is_array(session('errors'))) : ?>
                                                <?php foreach (session('errors') as $error) : ?>
                                                    <?= $error ?><br>
                                                <?php endforeach ?>
                                            <?php else : ?>
                                                <?= session('errors') ?>
                                            <?php endif ?>
                                        </div>
                                    <?php endif ?>
                                    <?php if (session('message') !== null) : ?>
                                        <div class="alert alert-success" role="alert"><?= session('message') ?></div>
                                    <?php endif ?>

                                    <form class="user" action="<?= url_to('login') ?>" method="post">
                                        <?= csrf_field() ?>

                                        <!-- Email -->
                                        <div class="form-group mb-3">
                                            <label for="email" class="font-weight-bold small mb-1">E-mail</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0">
                                                        <i class="fas fa-envelope text-primary"></i>
                                                    </span>
                                                </div>
                                                <input type="email" id="email" name="email" class="form-control border-left-0"
                                                    value="<?= old('email') ?>" autocomplete="email" required autofocus>
                                            </div>
                                        </div>

                                        <!-- Senha -->
                                        <div class="form-group mb-3">
                                            <label for="password" class="font-weight-bold small mb-1">Senha</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0">
                                                        <i class="fas fa-lock text-primary"></i>
                                                    </span>
                                                </div>
                                                <input type="password" id="password" name="password" class="form-control border-left-0"
                                                    autocomplete="current-password" required>
                                            </div>
                                        </div>

                                        <!-- Remember me -->
                                        <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
                                            <div class="form-group mb-3">
                                                <div class="custom-control custom-checkbox small">
                                                    <input type="checkbox" class="custom-control-input" id="remember" name="remember" <?php if (old('remember')): ?> checked<?php endif ?>>
                                                    <label class="custom-control-label" for="remember">Lembrar-me</label>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <button type="submit" class="btn btn-primary btn-block btn-user font-weight-bold mb-2">
                                            Entrar
                                        </button>
                                        <hr>
                                        <!--
                                        <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
                                        <div class="text-center">
                                            <a class="small" href="<?= url_to('magic-link') ?>">Esqueceu a senha? Use um link mágico</a>
                                        </div>
                                        <?php endif ?>
                                        <?php if (setting('Auth.allowRegistration')) : ?>
                                        <div class="text-center">
                                            <a class="small" href="<?= url_to('register') ?>">Criar uma conta!</a>
                                        </div>
                                        <?php endif ?>
                                        -->
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?php echo base_url('template/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('template/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('template/vendor/jquery-easing/jquery.easing.min.js'); ?>"></script>
    <script src="<?php echo base_url('template/js/sb-admin-2.min.js'); ?>"></script>
</body>

</html>