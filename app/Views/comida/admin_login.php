<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Pedidos</title>
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-center mb-3">
            <img src="<?= base_url('alfa.png') ?>" alt="Alfa" style="height: 48px;">
        </div>
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-1">Admin Pedidos</h4>
                        <p class="text-muted mb-3">
                            <?= esc($cliente['razon_social'] ?? '') ?> (<?= esc($cliente['codigo'] ?? '') ?>)
                        </p>

                        <?php if (session('msg')) : ?>
                            <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
                                <small><?= session('msg.body') ?></small>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($tenantNotice)) : ?>
                            <div class="alert alert-warning" role="alert">
                                <small><?= esc($tenantNotice) ?></small>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?= base_url('pedidos/' . ($cliente['codigo'] ?? '') . '/admin/login') ?>">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario o email</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contrasena</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('pedidos/' . ($cliente['codigo'] ?? '')) ?>" class="btn btn-outline-secondary">Volver</a>
                                <button type="submit" class="btn btn-primary">Ingresar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
</body>
</html>
