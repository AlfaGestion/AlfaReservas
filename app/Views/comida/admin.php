<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Pedidos</title>
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
</head>
<body style="background-color:#f7f7f7;">
    <div class="container py-3">
        <div class="d-flex justify-content-end mb-2">
            <img src="<?= base_url('alfa.png') ?>" alt="Alfa" style="height: 38px;">
        </div>

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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1"><?= esc($cliente['razon_social'] ?? '') ?></h4>
                <small class="text-muted">Admin Catalogo | Codigo <?= esc($cliente['codigo'] ?? '') ?> | Base <?= esc($cliente['base'] ?? '') ?></small>
            </div>
            <div class="text-end">
                <small class="d-block text-muted">Sesion: <?= esc($tenantAdminUser ?? 'admin') ?></small>
                <a href="<?= base_url('pedidos/' . ($cliente['codigo'] ?? '') . '/admin/logout') ?>" class="btn btn-outline-danger btn-sm mt-1">Cerrar sesion</a>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" id="pedidosAdminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="catalogo-tab" data-bs-toggle="tab" data-bs-target="#catalogo-pane" type="button" role="tab" aria-controls="catalogo-pane" aria-selected="true">Catalogo</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-pane" type="button" role="tab" aria-controls="general-pane" aria-selected="false">General</button>
            </li>
        </ul>

        <div class="tab-content" id="pedidosAdminTabsContent">
            <div class="tab-pane fade show active" id="catalogo-pane" role="tabpanel" aria-labelledby="catalogo-tab">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Nuevo item de catalogo</h5>
                        <form action="<?= base_url('pedidos/' . ($cliente['codigo'] ?? '') . '/admin/catalogo') ?>" method="POST">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label" for="nombre">Nombre</label>
                                    <input class="form-control" type="text" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="precio">Precio</label>
                                    <input class="form-control" type="number" step="0.01" min="0" id="precio" name="precio" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="descripcion">Descripcion</label>
                                    <input class="form-control" type="text" id="descripcion" name="descripcion">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn" style="background-color:#f39323; color:#fff;" <?= (($tenantMode ?? 'full') === 'read_only') ? 'disabled' : '' ?>>Guardar item</button>
                                <a href="<?= base_url('pedidos/' . ($cliente['codigo'] ?? '')) ?>" class="btn btn-secondary">Volver al portal</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5>Catalogo actual</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripcion</th>
                                        <th>Precio</th>
                                        <th>Activo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($catalogo)) : ?>
                                        <?php foreach ($catalogo as $item) : ?>
                                            <tr>
                                                <td><?= esc($item['id']) ?></td>
                                                <td><?= esc($item['nombre']) ?></td>
                                                <td><?= esc($item['descripcion'] ?? '') ?></td>
                                                <td>$<?= esc((string) $item['precio']) ?></td>
                                                <td><?= (int) ($item['activo'] ?? 0) === 1 ? 'Si' : 'No' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr><td colspan="5" class="text-muted">Sin items cargados.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="general-pane" role="tabpanel" aria-labelledby="general-tab">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-2">Configuracion general</h5>
                        <p class="text-muted mb-0">Esta pestaña queda preparada para los proximos ajustes de pedidos (horarios, estado del local, etc.).</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
</body>
</html>
