<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Login - Projeta</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="/projeta/app/ThirdParty/template/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Bem-vindo de volta!</h1>
                                    </div>

                                    <!-- Mensagens de erro/sucesso -->
                                    <?php if (session('error') !== null) : ?>
                                        <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
                                    <?php elseif (session('errors') !== null) : ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php if (is_array(session('errors'))) : ?>
                                                <?php foreach (session('errors') as $error) : ?>
                                                    <?= $error ?>
                                                    <br>
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
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user"
                                                id="email" name="email" inputmode="email" autocomplete="email"
                                                placeholder="Digite seu e-mail..." value="<?= old('email') ?>" required>
                                        </div>

                                        <!-- Password -->
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                id="password" name="password" inputmode="text" autocomplete="current-password"
                                                placeholder="Senha" required>
                                        </div>

                                        <!-- Remember me -->
                                        <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
                                            <div class="form-group">
                                                <div class="custom-control custom-checkbox small">
                                                    <input type="checkbox" class="custom-control-input" id="remember" name="remember" <?php if (old('remember')): ?> checked<?php endif ?>>
                                                    <label class="custom-control-label" for="remember">Lembrar-me</label>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Entrar
                                        </button>

                                        <hr>

                                        <!-- <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
                                            <div class="text-center">
                                                <a class="small" href="<?= url_to('magic-link') ?>">Esqueceu a senha? Use um link mágico</a>
                                            </div>
                                        <?php endif ?> -->

                                        <!-- <?php if (setting('Auth.allowRegistration')) : ?>
                                            <div class="text-center">
                                                <a class="small" href="<?= url_to('register') ?>">Criar uma conta!</a>
                                            </div>
                                        <?php endif ?> -->
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="/projeta/app/ThirdParty/template/vendor/jquery/jquery.min.js"></script>
    <script src="/projeta/app/ThirdParty/template/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="/projeta/app/ThirdParty/template/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="/projeta/app/ThirdParty/template/js/sb-admin-2.min.js"></script>
</body>

</html>