<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de pedido</title>
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Seguimiento de pedido</h4>
            <img src="<?= esc((string) (($branding['logo'] ?? '') !== '' ? $branding['logo'] : base_url('alfa.png'))) ?>" alt="Logo" style="max-height:52px;max-width:180px;">
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div><strong>Codigo seguimiento:</strong> <?= esc((string) ($pedido['codigo_seguimiento'] ?? '-')) ?></div>
                <div><strong>Pedido:</strong> #<?= esc((string) ($pedido['id'] ?? '-')) ?></div>
                <div><strong>Fecha:</strong> <?= esc((string) ($pedido['fecha'] ?? '-')) ?></div>
                <div><strong>Estado:</strong> <span class="badge bg-primary"><?= esc((string) ($pedido['estado_label'] ?? '-')) ?></span></div>
                <?php if (trim((string) ($pedido['fecha_recibido'] ?? '')) !== '') : ?>
                    <div><strong>Recibido el:</strong> <?= esc((string) ($pedido['fecha_recibido'] ?? '-')) ?></div>
                <?php endif; ?>
                <?php $estado = strtolower(trim((string) ($pedido['estado'] ?? ''))); ?>
                <?php if (in_array($estado, ['finalizado', 'completado', 'enviado'], true)) : ?>
                    <form method="POST" action="<?= base_url(ltrim((string) ($publicBasePath ?? ''), '/')) . '/pedido/' . rawurlencode((string) ($pedido['codigo_seguimiento'] ?? '')) . '/recibido' ?>" class="mt-2">
                        <button type="submit" class="btn btn-success btn-sm">Confirmar recibido</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-2">Datos de entrega</h6>
                <div><strong>Nombre:</strong> <?= esc((string) ($pedido['nombre_cliente'] ?? '-')) ?></div>
                <div><strong>Telefono:</strong> <?= esc((string) ($pedido['telefono'] ?? '-')) ?></div>
                <div><strong>Email:</strong> <?= esc((string) ($pedido['email'] ?? '-')) ?></div>
                <div><strong>Direccion:</strong> <?= esc((string) ($pedido['direccion'] ?? '-')) ?></div>
                <div><strong>Entre calles:</strong> <?= esc((string) ($pedido['entre_calles'] ?? '-')) ?></div>
                <div><strong>Observacion:</strong> <?= esc((string) ($pedido['observacion'] ?? '-')) ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="mb-2">Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripcion</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($insumos ?? [])) : ?>
                                <?php foreach (($insumos ?? []) as $it) : ?>
                                    <tr>
                                        <td><?= esc((string) ($it['nombre'] ?? '-')) ?></td>
                                        <td><?= esc((string) ($it['descripcion'] ?? '-')) ?></td>
                                        <td><?= esc((string) ($it['cantidad'] ?? '1')) ?></td>
                                        <td>$<?= esc((string) ($it['precio'] ?? '0')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="4" class="text-muted">Sin items.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= base_url(ltrim((string) ($publicBasePath ?? ''), '/')) ?>" class="btn btn-outline-primary">Volver</a>
        </div>
    </div>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
</body>
</html>
