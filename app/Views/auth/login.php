<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Flux - Login</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('template/img/flux.ico'); ?>">
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
                                        <div class="mx-auto d-flex align-items-center justify-content-center mb-4"
                                            style="width: 60px; height: 60px; background: linear-gradient(135deg,#2e59d9 60%,#224abe 100%); border-radius: 14px; box-shadow: 0 4px 12px 0 rgba(46,89,217,.13);">
                                            <i class="fas fa-project-diagram fa-2x text-white" style="transform: rotate(-15deg);"></i>
                                        </div>
                                        <h1 class="h3 text-gray-900 font-weight-bold mb-1">Bem-vindo(a) ao Flux</h1>
                                        <p class="text-secondary mb-4">Faça login para acessar sua conta</p>
                                    </div>

                                    <!-- Switch de Autenticação -->
                                    <div class="form-group text-center mb-4">
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label class="btn btn-outline-primary <?= (old('auth_type') !== 'ldap') ? 'active' : '' ?>" id="localAuthLabel">
                                                <input type="radio" name="authType" value="local" <?= (old('auth_type') !== 'ldap') ? 'checked' : '' ?>> Email/Senha
                                            </label>
                                            <label class="btn btn-outline-primary <?= (old('auth_type') === 'ldap') ? 'active' : '' ?>" id="ldapAuthLabel">
                                                <input type="radio" name="authType" value="ldap" <?= (old('auth_type') === 'ldap') ? 'checked' : '' ?>> AD (CPF/Senha)
                                            </label>
                                        </div>
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

                                    <form class="user" action="<?= url_to('login') ?>" method="post" id="loginForm">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="auth_type" id="authType" value="<?= old('auth_type', 'local') ?>">

                                        <!-- Campo de Email (mostrado por padrão) -->
                                        <div class="form-group mb-3" id="emailField" style="<?= (old('auth_type') === 'ldap') ? 'display:none;' : '' ?>">
                                            <label for="email" class="font-weight-bold small mb-1">E-mail</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0">
                                                        <i class="fas fa-envelope text-primary"></i>
                                                    </span>
                                                </div>
                                                <input type="email" id="email" name="email" class="form-control border-left-0"
                                                    value="<?= old('email') ?>" autocomplete="email" <?= (old('auth_type') !== 'ldap') ? 'required' : '' ?>>
                                            </div>
                                        </div>

                                        <!-- Campo de CPF (oculto por padrão) -->
                                        <div class="form-group mb-3" id="cpfField" style="<?= (old('auth_type') !== 'ldap') ? 'display:none;' : '' ?>">
                                            <label for="cpf" class="font-weight-bold small mb-1">CPF (somente números)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-white border-right-0">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </span>
                                                </div>
                                                <input type="text" id="cpf" name="cpf" class="form-control border-left-0 cpf-mask"
                                                    value="<?= old('cpf') ?>" autocomplete="username" maxlength="11" <?= (old('auth_type') === 'ldap') ? 'required' : '' ?>>
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
                                                    <input type="checkbox" class="custom-control-input" id="remember" name="remember" <?= old('remember') ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="remember">Lembrar-me</label>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <button type="submit" class="btn btn-primary btn-block btn-user font-weight-bold mb-2">
                                            Entrar
                                        </button>
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

    <script>
        $(document).ready(function() {
            // Função principal para atualizar a visibilidade e obrigatoriedade dos campos
            function updateFields() {
                const isLDAP = $('#authType').val() === 'ldap';

                // Controle de visibilidade
                $('#emailField').toggle(!isLDAP);
                $('#cpfField').toggle(isLDAP);

                // Controle de obrigatoriedade
                $('#email').prop('required', !isLDAP);
                $('#cpf').prop('required', isLDAP);

                // Limpa os campos quando alternados
                if (isLDAP) {
                    $('#email').val('');
                } else {
                    $('#cpf').val('');
                }
            }

            // Manipulador de mudança no tipo de autenticação
            $('input[name="authType"]').change(function() {
                $('#authType').val($(this).val());
                updateFields();
            });

            // Manipulador de envio do formulário - desativa validação HTML5 para campos não usados
            $('#loginForm').on('submit', function(e) {
                const isLDAP = $('#authType').val() === 'ldap';

                // Remove temporariamente o required do campo não utilizado
                if (isLDAP) {
                    $('#email').prop('required', false);
                } else {
                    $('#cpf').prop('required', false);
                }

                // Se quiser adicionar validação customizada, pode ser feita aqui
                // Retorna true para enviar o formulário
                return true;
            });

            // Máscara para CPF (somente números)
            $('.cpf-mask').on('input', function() {
                this.value = this.value.replace(/\D/g, '');
            });

            // Inicialização baseada no estado atual
            <?php if (session('errors.cpf') || old('auth_type') === 'ldap'): ?>
                $('#localAuthLabel').removeClass('active');
                $('#ldapAuthLabel').addClass('active');
                $('#authType').val('ldap');
            <?php endif; ?>

            // Atualiza campos na carga inicial
            updateFields();
        });
    </script>
</body>

</html>