<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Acceso no disponible') ?></title>
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm border-danger">
                    <div class="card-body p-4">
                        <h4 class="text-danger mb-3"><?= esc($title ?? 'Acceso no disponible') ?></h4>
                        <p class="mb-2"><?= esc($message ?? 'No podes ingresar en este momento.') ?></p>
                        <?php if (!empty($cliente['razon_social'])) : ?>
                            <p class="text-muted mb-0">Cliente: <?= esc($cliente['razon_social']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
