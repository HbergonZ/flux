<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste LDAP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="text-center mb-0">Teste de Conexão AD</h3>
                        <a href="<?= site_url('/') ?>" class="btn btn-sm btn-outline-secondary">Voltar para Página Inicial</a>
                    </div>
                    <div class="card-body">
                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger">
                                <?= session('error') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($success) && $success): ?>
                            <div class="alert alert-success">
                                <h4>Autenticação LDAP bem-sucedida!</h4>
                                <p><strong>Usuário DN:</strong> <?= esc($userDn) ?></p>

                                <h5 class="mt-4">Dados do Usuário:</h5>
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Nome:</strong> <?= esc($userData['nome']) ?></li>
                                    <li class="list-group-item"><strong>Email:</strong> <?= esc($userData['email']) ?></li>
                                    <li class="list-group-item"><strong>Base DN:</strong> <?= esc($userData['base_dn']) ?></li>
                                </ul>

                                <div class="d-flex justify-content-between mt-3">
                                    <a href="<?= site_url('testeldap') ?>" class="btn btn-primary">Testar Novamente</a>
                                    <a href="<?= site_url('/') ?>" class="btn btn-secondary">Voltar para Página Inicial</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="post" action="<?= site_url('testeldap/testar') ?>">
                                <?= csrf_field() ?>

                                <div class="mb-3">
                                    <label for="username" class="form-label">Usuário</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Senha</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Testar Conexão</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>