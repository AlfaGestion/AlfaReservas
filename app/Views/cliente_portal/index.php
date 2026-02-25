<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($cliente['razon_social'] ?? 'Portal cliente') ?></title>
    <link rel="icon" href="<?= esc($branding['logo']) ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
    <style>
        body {
            min-height: 100vh;
            background: #f5f5f5;
            <?php if (!empty($branding['background'])) : ?>
            background-image: url('<?= esc($branding['background']) ?>');
            background-size: cover;
            background-position: center;
            <?php endif; ?>
        }
        .card-portal {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <img src="<?= esc($branding['logo']) ?>" alt="Logo cliente" style="max-height: 64px;">
            <span class="badge bg-dark">Codigo: <?= esc($cliente['codigo'] ?? '') ?></span>
        </div>

        <div class="card card-portal shadow-sm">
            <div class="card-body">
                <h4 class="mb-3"><?= esc($cliente['razon_social'] ?? '') ?></h4>
                <p class="mb-1"><strong>Base:</strong> <?= esc($stats['database'] ?? '') ?></p>
                <p class="mb-1"><strong>Rubro:</strong> <?= esc($cliente['rubro'] ?? '-') ?></p>
                <p class="mb-3"><strong>Link:</strong> <?= esc($cliente['link'] ?? '') ?></p>

                <?php if (!empty($stats['error'])) : ?>
                    <div class="alert alert-danger mb-0"><?= esc($stats['error']) ?></div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Tabla</th>
                                    <th>Existe</th>
                                    <th>Registros</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($stats['tables'] ?? []) as $name => $info) : ?>
                                    <tr>
                                        <td><?= esc($name) ?></td>
                                        <td><?= !empty($info['exists']) ? 'Si' : 'No' ?></td>
                                        <td><?= esc((string) ($info['count'] ?? 0)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="mt-3 small text-muted">
                    Branding por cliente: <?= esc($branding['tenantDir']) ?>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
</body>
</html>
