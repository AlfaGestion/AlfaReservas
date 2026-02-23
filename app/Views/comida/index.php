<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($cliente['razon_social'] ?? 'Portal comida') ?></title>
    <link rel="icon" href="<?= esc(!empty($branding['logo']) ? $branding['logo'] : base_url('favicon.ico')) ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background-color: #f4f4f4;
            <?php if (!empty($branding['background'])) : ?>
            background-image: url('<?= esc($branding['background']) ?>');
            background-size: cover;
            background-position: center;
            <?php endif; ?>
        }
        .portal-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid #e3e3e3;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <?php if (session('msg')) : ?>
            <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
                <small><?= session('msg.body') ?></small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <?php if (!empty($branding['logo'])) : ?>
                <img src="<?= esc($branding['logo']) ?>" alt="Logo cliente" style="max-height: 68px;">
            <?php else : ?>
                <h2 class="mb-0"><?= esc($cliente['razon_social'] ?? '') ?></h2>
            <?php endif; ?>
        </div>

        <div class="card portal-card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Nueva reserva de comida</h5>
                <form action="<?= base_url('comida/' . ($cliente['codigo'] ?? '') . '/reservar') ?>" method="POST">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label" for="nombre">Nombre</label>
                            <input class="form-control" type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="telefono">Telefono</label>
                            <input class="form-control" type="text" id="telefono" name="telefono">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="catalogo_id">Item catalogo</label>
                            <select class="form-select" id="catalogo_id" name="catalogo_id" required>
                                <option value="">Seleccionar item</option>
                                <?php foreach (($catalogo ?? []) as $item) : ?>
                                    <?php if ((int) ($item['activo'] ?? 0) === 1) : ?>
                                        <option value="<?= esc($item['id']) ?>">
                                            <?= esc($item['nombre']) ?> ($<?= esc((string) $item['precio']) ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" for="cantidad">Cantidad</label>
                            <input class="form-control" type="number" min="1" id="cantidad" name="cantidad" value="1" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="observaciones">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary" <?= empty($catalogo) ? 'disabled' : '' ?>>Guardar reserva</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card portal-card shadow-sm mt-3">
            <div class="card-body">
                <h5 class="mb-3">Catalogo</h5>
                <?php if (empty($catalogo)) : ?>
                    <div class="alert alert-warning mb-0">No hay items en el catalogo de esta base.</div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripcion</th>
                                    <th>Precio</th>
                                    <th>Activo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($catalogo as $item) : ?>
                                    <tr>
                                        <td><?= esc($item['nombre']) ?></td>
                                        <td><?= esc($item['descripcion'] ?? '') ?></td>
                                        <td>$<?= esc((string) $item['precio']) ?></td>
                                        <td><?= (int) ($item['activo'] ?? 0) === 1 ? 'Si' : 'No' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4 mb-2">
            <a href="<?= base_url('comida/' . ($cliente['codigo'] ?? '') . '/admin') ?>" class="btn btn-outline-dark btn-sm">Acceso admin</a>
        </div>
    </div>
</body>
</html>
