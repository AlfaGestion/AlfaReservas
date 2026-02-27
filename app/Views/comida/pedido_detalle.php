<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del pedido</title>
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
</head>
<body class="pedidos-admin-page">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Detalle del pedido #<?= esc((string) ($pedido['id'] ?? '')) ?></h4>
            <img src="<?= esc((string) (($branding['logo'] ?? '') !== '' ? $branding['logo'] : base_url('alfa.png'))) ?>" alt="Logo" style="height: 36px;">
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3"><strong>Fecha:</strong> <?= esc((string) ($pedido['fecha'] ?? '')) ?></div>
                    <div class="col-md-3"><strong>Estado:</strong> <?= esc((string) ($pedido['estado'] ?? '')) ?></div>
                    <div class="col-md-3"><strong>Cliente:</strong> <?= esc((string) ($pedido['cliente_nombre'] ?? '-')) ?></div>
                    <div class="col-md-3"><strong>Telefono:</strong> <?= esc((string) ($pedido['cliente_telefono'] ?? '-')) ?></div>
                </div>
                <hr>
                <h6>Detalle</h6>
                <pre class="mb-0" style="white-space:pre-wrap;"><?= esc((string) ($pedido['observaciones'] ?? '')) ?></pre>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= base_url(ltrim((string) ($adminBasePath ?? ''), '/')) ?>" class="btn btn-outline-primary">Volver al admin</a>
        </div>
    </div>

    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
</body>
</html>

