<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Pedidos</title>
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
    <style>
        body.pedidos-admin-page { background:#f2f6fb; color:#16324a; }
        body.pedidos-admin-page .card { border:1px solid #d5e4f3; }
        body.pedidos-admin-page .table { color:inherit; }
        .ticket-paper { border:1px dashed #8fb1d3; border-radius:12px; background:#fff; }
        .ticket-line { border-bottom:1px dashed #d5e4f3; padding:8px 0; }
        .ticket-line:last-child { border-bottom:0; }
        body.theme-dark.pedidos-admin-page { background:#0b2236; color:#e8f3ff; }
        body.theme-dark.pedidos-admin-page .card { background:#16314a; border-color:#3a5f80; color:#e8f3ff; }
        body.theme-dark.pedidos-admin-page .nav-tabs { border-bottom-color:#3a5f80; }
        body.theme-dark.pedidos-admin-page .nav-tabs .nav-link { color:#c7dff5; border-color:transparent; }
        body.theme-dark.pedidos-admin-page .nav-tabs .nav-link.active { color:#fff; background:#1f4467; border-color:#3a5f80 #3a5f80 #1f4467; }
        body.theme-dark.pedidos-admin-page .form-control,
        body.theme-dark.pedidos-admin-page .form-select { background:#10253a; border-color:#3a5f80; color:#e8f3ff; }
        body.theme-dark.pedidos-admin-page .text-muted { color:#b5d2ec !important; }
        body.theme-dark.pedidos-admin-page .ticket-paper { background:#10253a; border-color:#4d7094; color:#e8f3ff; }
        body.theme-dark.pedidos-admin-page .ticket-line { border-bottom-color:#2d4d6d; }
    </style>
</head>
<body class="pedidos-admin-page">
    <div class="container py-3">
        <div class="d-flex justify-content-end mb-2">
            <img src="<?= esc((string) (($branding['logo'] ?? '') !== '' ? $branding['logo'] : base_url('alfa.png'))) ?>" alt="Logo" style="height: 38px;">
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
                <a href="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/logout' ?>" class="btn btn-outline-danger btn-sm mt-1">Cerrar sesion</a>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" id="pedidosAdminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary-pane" type="button" role="tab" aria-controls="summary-pane" aria-selected="true">Resumen</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="catalogo-tab" data-bs-toggle="tab" data-bs-target="#catalogo-pane" type="button" role="tab" aria-controls="catalogo-pane" aria-selected="false">Pedidos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="catalogo-items-tab" data-bs-toggle="tab" data-bs-target="#catalogo-items-pane" type="button" role="tab" aria-controls="catalogo-items-pane" aria-selected="false">Catalogo</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-pane" type="button" role="tab" aria-controls="settings-pane" aria-selected="false">Configuracion</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="clients-tab" data-bs-toggle="tab" data-bs-target="#clients-pane" type="button" role="tab" aria-controls="clients-pane" aria-selected="false">Clientes</button>
            </li>
        </ul>

        <div class="tab-content" id="pedidosAdminTabsContent">
            <div class="tab-pane fade show active" id="summary-pane" role="tabpanel" aria-labelledby="summary-tab">
                <?php
                    $pedidosRows = $pedidos ?? [];
                    $clientesRows = $tenantClientes ?? [];
                    $usersRows = $tenantUsers ?? [];
                    $catalogoRows = $catalogo ?? [];
                    $pedidosTotal = count($pedidosRows);
                    $pedidosPend = count(array_filter($pedidosRows, static fn($r) => strtolower((string) ($r['estado'] ?? '')) === 'pendiente'));
                    $pedidosComp = count(array_filter($pedidosRows, static fn($r) => in_array(strtolower((string) ($r['estado'] ?? '')), ['completado', 'finalizado'], true)));
                    $clientesTotal = count($clientesRows);
                    $usersActivos = count(array_filter($usersRows, static fn($u) => (int) ($u['active'] ?? 0) === 1));
                    $catalogoActivos = count(array_filter($catalogoRows, static fn($i) => (int) ($i['activo'] ?? 0) === 1));
                ?>
                <div class="row g-2">
                    <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted d-block">Pedidos</small><strong><?= esc((string) $pedidosTotal) ?></strong></div></div></div>
                    <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted d-block">Pendientes</small><strong><?= esc((string) $pedidosPend) ?></strong></div></div></div>
                    <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted d-block">Completados</small><strong><?= esc((string) $pedidosComp) ?></strong></div></div></div>
                    <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted d-block">Clientes</small><strong><?= esc((string) $clientesTotal) ?></strong></div></div></div>
                    <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted d-block">Usuarios activos</small><strong><?= esc((string) $usersActivos) ?></strong></div></div></div>
                    <div class="col-md-2"><div class="card"><div class="card-body"><small class="text-muted d-block">Items activos</small><strong><?= esc((string) $catalogoActivos) ?></strong></div></div></div>
                </div>
            </div>

            <div class="tab-pane fade" id="catalogo-pane" role="tabpanel" aria-labelledby="catalogo-tab">
                <div class="card">
                    <div class="card-body">
                        <h5>Pedidos de la web</h5>
                        <form method="GET" action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) ?>" class="row g-2 align-items-end mb-3">
                            <div class="col-sm-4 col-md-3">
                                <label class="form-label mb-1" for="pedido-fecha">Fecha</label>
                                <input type="date" id="pedido-fecha" name="fecha" class="form-control" value="<?= esc((string) ($pedidoFechaFiltro ?? date('Y-m-d'))) ?>">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '?fecha=' . date('Y-m-d') ?>" class="btn btn-outline-secondary">Hoy</a>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Telefono</th>
                                        <th>Estado</th>
                                        <th>Detalle</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pedidos ?? [])) : ?>
                                        <?php foreach (($pedidos ?? []) as $p) : ?>
                                            <tr>
                                                <td><?= esc((string) ($p['id'] ?? '')) ?></td>
                                                <td><?= esc((string) ($p['fecha'] ?? '')) ?></td>
                                                <td><?= esc((string) ($p['cliente_nombre'] ?? '-')) ?></td>
                                                <td><?= esc((string) ($p['cliente_telefono'] ?? '-')) ?></td>
                                                <td><?= esc((string) ($p['estado'] ?? '-')) ?></td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-link btn-sm p-0 js-ver-detalle"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#pedidoDetalleModal"
                                                        data-id="<?= esc((string) ($p['id'] ?? '')) ?>"
                                                        data-fecha="<?= esc((string) ($p['fecha'] ?? '')) ?>"
                                                        data-cliente="<?= esc((string) ($p['cliente_nombre'] ?? '-')) ?>"
                                                        data-telefono="<?= esc((string) ($p['cliente_telefono'] ?? '-')) ?>"
                                                        data-estado="<?= esc((string) ($p['estado'] ?? '-')) ?>"
                                                        data-detalle="<?= esc((string) ($p['observaciones'] ?? ''), 'attr') ?>"
                                                    >Ver detalle</button>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group" aria-label="Acciones pedido">
                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-secondary js-editar-pedido"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#pedidoEditarModal"
                                                            data-id="<?= esc((string) ($p['id'] ?? '')) ?>"
                                                            data-estado="<?= esc((string) ($p['estado'] ?? '-')) ?>"
                                                            data-detalle="<?= esc((string) ($p['observaciones'] ?? ''), 'attr') ?>"
                                                        >Editar</button>
                                                        <form method="POST" action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/pedido/' . (int) ($p['id'] ?? 0) . '/estado' ?>">
                                                            <input type="hidden" name="estado" value="preparando_envio">
                                                            <input type="hidden" name="fecha_filtro" value="<?= esc((string) ($pedidoFechaFiltro ?? date('Y-m-d'))) ?>">
                                                            <button type="submit" class="btn btn-outline-info">Preparar envio</button>
                                                        </form>
                                                        <form method="POST" action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/pedido/' . (int) ($p['id'] ?? 0) . '/estado' ?>">
                                                            <input type="hidden" name="estado" value="finalizado">
                                                            <input type="hidden" name="fecha_filtro" value="<?= esc((string) ($pedidoFechaFiltro ?? date('Y-m-d'))) ?>">
                                                            <button type="submit" class="btn btn-outline-success">Finalizar</button>
                                                        </form>
                                                        <form method="POST" action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/pedido/' . (int) ($p['id'] ?? 0) . '/estado' ?>">
                                                            <input type="hidden" name="estado" value="recibido">
                                                            <input type="hidden" name="fecha_filtro" value="<?= esc((string) ($pedidoFechaFiltro ?? date('Y-m-d'))) ?>">
                                                            <button type="submit" class="btn btn-outline-primary">Recibido</button>
                                                        </form>
                                                        <form method="POST" action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/pedido/' . (int) ($p['id'] ?? 0) . '/estado' ?>" onsubmit="return confirm('Se anulara el pedido. Continuar?');">
                                                            <input type="hidden" name="estado" value="anulado">
                                                            <input type="hidden" name="fecha_filtro" value="<?= esc((string) ($pedidoFechaFiltro ?? date('Y-m-d'))) ?>">
                                                            <button type="submit" class="btn btn-outline-danger">Anular</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr><td colspan="7" class="text-muted">Sin pedidos registrados para la fecha seleccionada.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="pedidoDetalleModal" tabindex="-1" aria-labelledby="pedidoDetalleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="pedidoDetalleModalLabel">Ticket del pedido</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="ticket-paper p-3">
                                    <div class="text-center mb-2">
                                        <strong>Pedido #<span id="ticket-id">-</span></strong>
                                    </div>
                                    <div class="ticket-line"><strong>Fecha:</strong> <span id="ticket-fecha">-</span></div>
                                    <div class="ticket-line"><strong>Cliente:</strong> <span id="ticket-cliente">-</span></div>
                                    <div class="ticket-line"><strong>Telefono:</strong> <span id="ticket-telefono">-</span></div>
                                    <div class="ticket-line"><strong>Email:</strong> <span id="ticket-email">-</span></div>
                                    <div class="ticket-line"><strong>Direccion:</strong> <span id="ticket-direccion">-</span></div>
                                    <div class="ticket-line"><strong>Entre calles:</strong> <span id="ticket-entre-calles">-</span></div>
                                    <div class="ticket-line"><strong>GPS:</strong> <span id="ticket-gps">-</span></div>
                                    <div class="ticket-line">
                                        <a id="ticket-map-link" href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info d-none">Ver en mapa</a>
                                    </div>
                                    <div class="ticket-line"><strong>Estado:</strong> <span id="ticket-estado">-</span></div>
                                    <div class="pt-2">
                                        <strong>Detalle:</strong>
                                        <pre id="ticket-detalle" class="mt-2 mb-0" style="white-space:pre-wrap;">-</pre>
                                    </div>
                                    <div class="pt-2">
                                        <strong>Items del pedido:</strong>
                                        <div id="ticket-items" class="mt-2 small">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="pedidoEditarModal" tabindex="-1" aria-labelledby="pedidoEditarModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" id="pedidoEditarForm">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="pedidoEditarModalLabel">Editar pedido</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label class="form-label" for="edit-estado">Estado</label>
                                        <select id="edit-estado" name="estado" class="form-select">
                                            <option value="pendiente">Pendiente</option>
                                            <option value="preparando envio">Preparando envio</option>
                                            <option value="finalizado">Finalizado</option>
                                            <option value="recibido">Recibido</option>
                                            <option value="anulado">Anulado</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label" for="edit-observaciones">Detalle / observaciones</label>
                                        <textarea id="edit-observaciones" name="observaciones" rows="5" class="form-control"></textarea>
                                    </div>
                                    <input type="hidden" name="fecha_filtro" value="<?= esc((string) ($pedidoFechaFiltro ?? date('Y-m-d'))) ?>">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="catalogo-items-pane" role="tabpanel" aria-labelledby="catalogo-items-tab">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Nuevo item de catalogo</h5>
                        <form id="catalogoForm" action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/catalogo' ?>" method="POST" enctype="multipart/form-data" onsubmit="return false;">
                            <input type="hidden" id="cat-id" name="id" value="">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label" for="cat-nombre">Nombre</label>
                                    <input class="form-control" type="text" id="cat-nombre" name="nombre" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="cat-precio">Precio</label>
                                    <input class="form-control" type="number" step="0.01" min="0" id="cat-precio" name="precio" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label" for="cat-descripcion">Descripcion</label>
                                    <input class="form-control" type="text" id="cat-descripcion" name="descripcion">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="cat-imagen">Imagen</label>
                                    <input class="form-control" type="file" id="cat-imagen" name="imagen" accept=".png,.jpg,.jpeg,.webp">
                                    <div class="form-text">Tambien podes pegar una imagen con Ctrl+V cuando este enfocado este campo.</div>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label" for="cat-imagen-url">O pegar URL de imagen (Google)</label>
                                    <div class="input-group mb-2">
                                        <input class="form-control" type="url" id="cat-imagen-url" name="imagen_url" placeholder="https://...">
                                        <button type="button" class="btn btn-outline-secondary" id="cat-search-google-btn">Buscar en Google</button>
                                        <button type="button" class="btn btn-outline-primary" id="cat-paste-image-btn">Pegar</button>
                                    </div>
                                    <div id="cat-image-preview-box" class="border rounded d-flex align-items-center justify-content-center" style="min-height:120px;background:rgba(0,0,0,.03);">
                                        <span class="text-muted small" id="cat-image-preview-empty">Sin imagen seleccionada</span>
                                        <img id="cat-image-preview" src="" alt="Preview imagen" style="max-height:116px;max-width:100%;display:none;">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <button type="button" id="catalogoSaveBtn" class="btn btn-primary" <?= (($tenantMode ?? 'full') === 'read_only') ? 'disabled' : '' ?>>Guardar item</button>
                                <button type="button" id="catalogoCancelEditBtn" class="btn btn-outline-secondary d-none">Cancelar edicion</button>
                            </div>
                            <div id="catalogoFormMsg" class="small mt-2"></div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-2">Catalogo actual</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripcion</th>
                                        <th>Precio</th>
                                        <th>Imagen</th>
                                        <th>Activo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="catalogoTableBody">
                                    <?php if (!empty($catalogo ?? [])) : ?>
                                        <?php foreach (($catalogo ?? []) as $item) : ?>
                                            <?php
                                                $imgRaw = trim((string) (($item['imagen'] ?? $item['foto'] ?? $item['imagen_url'] ?? $item['foto_url'] ?? '')));
                                                $imgUrl = '';
                                                if ($imgRaw !== '') {
                                                    $imgUrl = preg_match('#^https?://#i', $imgRaw) === 1 ? $imgRaw : base_url(ltrim($imgRaw, '/'));
                                                }
                                            ?>
                                            <tr>
                                                <td><?= esc((string) ($item['id'] ?? '')) ?></td>
                                                <td><?= esc((string) ($item['nombre'] ?? '')) ?></td>
                                                <td><?= esc((string) ($item['descripcion'] ?? '-')) ?></td>
                                                <td>$<?= esc(number_format((float) ($item['precio'] ?? 0), 2, '.', '')) ?></td>
                                                <td>
                                                    <?php if ($imgUrl !== '') : ?>
                                                        <img src="<?= esc($imgUrl) ?>" alt="Imagen item" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                                                    <?php else : ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= ((int) ($item['activo'] ?? 0) === 1) ? 'Si' : 'No' ?></td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary js-edit-catalogo"
                                                        data-id="<?= esc((string) ($item['id'] ?? '')) ?>"
                                                        data-nombre="<?= esc((string) ($item['nombre'] ?? ''), 'attr') ?>"
                                                        data-descripcion="<?= esc((string) ($item['descripcion'] ?? ''), 'attr') ?>"
                                                        data-precio="<?= esc((string) ($item['precio'] ?? '0'), 'attr') ?>"
                                                    >Editar</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr><td colspan="7" class="text-muted">Sin items cargados.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="settings-pane" role="tabpanel" aria-labelledby="settings-tab">
                <?php
                    $cfg = $webConfig ?? [];
                    $diasAbiertos = is_array($cfg['dias_abiertos'] ?? null) ? $cfg['dias_abiertos'] : [];
                    $diasLabel = [
                        '1' => 'Lunes',
                        '2' => 'Martes',
                        '3' => 'Miercoles',
                        '4' => 'Jueves',
                        '5' => 'Viernes',
                        '6' => 'Sabado',
                        '0' => 'Domingo',
                    ];
                ?>
                <ul class="nav nav-pills mb-3" id="settingsSubTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="cfg-general-tab" data-bs-toggle="pill" data-bs-target="#cfg-general-pane" type="button" role="tab">General</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="cfg-users-tab" data-bs-toggle="pill" data-bs-target="#cfg-users-pane" type="button" role="tab">Usuarios</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="cfg-offers-tab" data-bs-toggle="pill" data-bs-target="#cfg-offers-pane" type="button" role="tab">Ofertas</button>
                    </li>
                </ul>
                <div class="tab-content" id="settingsSubTabsContent">
                    <div class="tab-pane fade show active" id="cfg-general-pane" role="tabpanel" aria-labelledby="cfg-general-tab">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="mb-3">Configuracion general de la web</h5>
                                <form action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/general' ?>" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label d-block mb-2">Dias de apertura</label>
                                        <div class="d-flex flex-wrap gap-3">
                                            <?php foreach ($diasLabel as $diaVal => $diaTxt) : ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="dia_<?= esc($diaVal) ?>" name="dias_abiertos[]" value="<?= esc($diaVal) ?>" <?= in_array((string) $diaVal, $diasAbiertos, true) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="dia_<?= esc($diaVal) ?>"><?= esc($diaTxt) ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label" for="turno1_desde">Turno 1 desde</label>
                                            <input class="form-control" type="time" id="turno1_desde" name="turno1_desde" value="<?= esc((string) ($cfg['turno1_desde'] ?? '09:00')) ?>" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="turno1_hasta">Turno 1 hasta</label>
                                            <input class="form-control" type="time" id="turno1_hasta" name="turno1_hasta" value="<?= esc((string) ($cfg['turno1_hasta'] ?? '13:00')) ?>" required>
                                        </div>
                                        <div class="col-md-6 d-flex align-items-end">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="usar_segundo_turno" name="usar_segundo_turno" <?= ((string) ($cfg['usar_segundo_turno'] ?? '0') === '1') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="usar_segundo_turno">Usar segundo turno</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-1">
                                        <div class="col-md-3">
                                            <label class="form-label" for="turno2_desde">Turno 2 desde</label>
                                            <input class="form-control" type="time" id="turno2_desde" name="turno2_desde" value="<?= esc((string) ($cfg['turno2_desde'] ?? '17:00')) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="turno2_hasta">Turno 2 hasta</label>
                                            <input class="form-control" type="time" id="turno2_hasta" name="turno2_hasta" value="<?= esc((string) ($cfg['turno2_hasta'] ?? '21:00')) ?>">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary" <?= (($tenantMode ?? 'full') === 'read_only') ? 'disabled' : '' ?>>Guardar general</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">Configuracion web</h5>
                                <form action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/configuracion' ?>" method="POST">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label" for="mp_public_key">Mercado Pago Public Key</label>
                                            <input class="form-control" type="text" id="mp_public_key" name="mp_public_key" value="<?= esc((string) ($cfg['mp_public_key'] ?? '')) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="mp_access_token">Mercado Pago Access Token</label>
                                            <input class="form-control" type="text" id="mp_access_token" name="mp_access_token" value="<?= esc((string) ($cfg['mp_access_token'] ?? '')) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="color_primary">Color principal</label>
                                            <input class="form-control form-control-color" type="color" id="color_primary" name="color_primary" value="<?= esc((string) ($cfg['color_primary'] ?? '#1f4467')) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="color_secondary">Color secundario</label>
                                            <input class="form-control form-control-color" type="color" id="color_secondary" name="color_secondary" value="<?= esc((string) ($cfg['color_secondary'] ?? '#f39323')) ?>">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary" <?= (($tenantMode ?? 'full') === 'read_only') ? 'disabled' : '' ?>>Guardar configuracion</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cfg-users-pane" role="tabpanel" aria-labelledby="cfg-users-tab">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="mb-3">Crear usuario de la web</h5>
                                <form action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/usuarios' ?>" method="POST">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label" for="user">Usuario</label>
                                            <input class="form-control" type="text" id="user" name="user" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="email">Email (opcional)</label>
                                            <input class="form-control" type="email" id="email" name="email">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="password">Contrasena</label>
                                            <input class="form-control" type="password" id="password" name="password" required>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary" <?= (($tenantMode ?? 'full') === 'read_only') ? 'disabled' : '' ?>>Crear usuario</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-2">Usuarios actuales</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead><tr><th>Usuario</th><th>Email</th><th>Activo</th></tr></thead>
                                        <tbody>
                                            <?php if (!empty($tenantUsers ?? [])) : ?>
                                                <?php foreach (($tenantUsers ?? []) as $u) : ?>
                                                    <tr>
                                                        <td><?= esc((string) ($u['user'] ?? '')) ?></td>
                                                        <td><?= esc((string) ($u['email'] ?? '-')) ?></td>
                                                        <td><?= ((int) ($u['active'] ?? 0) === 1) ? 'Si' : 'No' ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <tr><td colspan="3" class="text-muted">Sin usuarios.</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cfg-offers-pane" role="tabpanel" aria-labelledby="cfg-offers-tab">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">Ofertas</h5>
                                <form action="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) . '/ofertas' ?>" method="POST">
                                    <div class="row g-2">
                                        <div class="col-md-3 d-flex align-items-end">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="offer_enabled" name="offer_enabled" <?= ((string) ($cfg['offer_enabled'] ?? '0') === '1') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="offer_enabled">Oferta activa</label>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label" for="offer_title">Titulo</label>
                                            <input class="form-control" type="text" id="offer_title" name="offer_title" value="<?= esc((string) ($cfg['offer_title'] ?? '')) ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label" for="offer_percent">% Descuento</label>
                                            <input class="form-control" type="number" min="0" max="100" id="offer_percent" name="offer_percent" value="<?= esc((string) ($cfg['offer_percent'] ?? '0')) ?>">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary" <?= (($tenantMode ?? 'full') === 'read_only') ? 'disabled' : '' ?>>Guardar ofertas</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="clients-pane" role="tabpanel" aria-labelledby="clients-tab">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-2">Clientes de la web</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr><th>ID</th><th>Nombre</th><th>Telefono</th><th>Email</th><th>Activo</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($tenantClientes ?? [])) : ?>
                                        <?php foreach (($tenantClientes ?? []) as $c) : ?>
                                            <tr>
                                                <td><?= esc((string) ($c['id'] ?? '')) ?></td>
                                                <td><?= esc((string) ($c['nombre'] ?? '')) ?></td>
                                                <td><?= esc((string) ($c['telefono'] ?? '-')) ?></td>
                                                <td><?= esc((string) ($c['email'] ?? '-')) ?></td>
                                                <td><?= ((int) ($c['activo'] ?? 0) === 1) ? 'Si' : 'No' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr><td colspan="5" class="text-muted">Sin clientes cargados.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
    <script>
        (function () {
            const nombreInput = document.getElementById('cat-nombre');
            const descInput = document.getElementById('cat-descripcion');
            const searchBtn = document.getElementById('cat-search-google-btn');
            const pasteBtn = document.getElementById('cat-paste-image-btn');
            const imageInput = document.getElementById('cat-imagen');
            const imageUrlInput = document.getElementById('cat-imagen-url');
            const previewImg = document.getElementById('cat-image-preview');
            const previewEmpty = document.getElementById('cat-image-preview-empty');

            const showPreview = function (url) {
                if (!previewImg || !previewEmpty) {
                    return;
                }
                if (!url) {
                    previewImg.style.display = 'none';
                    previewImg.src = '';
                    previewEmpty.style.display = '';
                    return;
                }
                previewImg.src = url;
                previewImg.style.display = '';
                previewEmpty.style.display = 'none';
            };

            if (searchBtn) {
                searchBtn.addEventListener('click', function () {
                    const q = [nombreInput ? nombreInput.value : '', descInput ? descInput.value : '']
                        .join(' ')
                        .trim();
                    const query = q !== '' ? q + ' producto' : 'producto';
                    window.open('https://www.google.com/search?tbm=isch&q=' + encodeURIComponent(query), '_blank');
                });
            }

            if (imageUrlInput) {
                imageUrlInput.addEventListener('input', function () {
                    const v = (imageUrlInput.value || '').trim();
                    if (v !== '') {
                        showPreview(v);
                    } else if (!imageInput || !imageInput.files || !imageInput.files.length) {
                        showPreview('');
                    }
                });
            }

            if (imageInput) {
                imageInput.addEventListener('change', function () {
                    if (imageInput.files && imageInput.files.length) {
                        showPreview(URL.createObjectURL(imageInput.files[0]));
                    } else if (!imageUrlInput || !(imageUrlInput.value || '').trim()) {
                        showPreview('');
                    }
                });
                imageInput.addEventListener('paste', function (event) {
                    const items = event.clipboardData && event.clipboardData.items ? event.clipboardData.items : [];
                    if (!items.length) {
                        return;
                    }
                    for (let i = 0; i < items.length; i += 1) {
                        const it = items[i];
                        if (!it.type || it.type.indexOf('image/') !== 0) {
                            continue;
                        }
                        const blob = it.getAsFile();
                        if (!blob) {
                            continue;
                        }
                        const ext = (it.type.split('/')[1] || 'png').replace('jpeg', 'jpg');
                        const file = new File([blob], 'pegado.' + ext, { type: it.type });
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        imageInput.files = dt.files;
                        showPreview(URL.createObjectURL(file));
                        event.preventDefault();
                        break;
                    }
                });
            }

            if (pasteBtn) {
                pasteBtn.addEventListener('click', async function () {
                    try {
                        if (navigator.clipboard && navigator.clipboard.read) {
                            const clipboardItems = await navigator.clipboard.read();
                            for (const item of clipboardItems) {
                                const imgType = item.types.find(function (t) { return t.indexOf('image/') === 0; });
                                if (imgType && imageInput) {
                                    const blob = await item.getType(imgType);
                                    const ext = (imgType.split('/')[1] || 'png').replace('jpeg', 'jpg');
                                    const file = new File([blob], 'pegado.' + ext, { type: imgType });
                                    const dt = new DataTransfer();
                                    dt.items.add(file);
                                    imageInput.files = dt.files;
                                    showPreview(URL.createObjectURL(file));
                                    return;
                                }
                            }
                        }
                        if (navigator.clipboard && navigator.clipboard.readText && imageUrlInput) {
                            const txt = (await navigator.clipboard.readText() || '').trim();
                            if (/^https?:\/\//i.test(txt)) {
                                imageUrlInput.value = txt;
                                showPreview(txt);
                                return;
                            }
                        }
                        alert('No se pudo pegar automaticamente. Usa Ctrl+V en el campo de imagen o pega la URL manualmente.');
                    } catch (e) {
                        alert('No se pudo acceder al portapapeles. Pega con Ctrl+V.');
                    }
                });
            }
        })();

        (function () {
            const form = document.getElementById('catalogoForm');
            const tbody = document.getElementById('catalogoTableBody');
            const saveBtn = document.getElementById('catalogoSaveBtn');
            const cancelBtn = document.getElementById('catalogoCancelEditBtn');
            const msg = document.getElementById('catalogoFormMsg');
            const idInput = document.getElementById('cat-id');
            const nombreInput = document.getElementById('cat-nombre');
            const descripcionInput = document.getElementById('cat-descripcion');
            const precioInput = document.getElementById('cat-precio');
            const imgInput = document.getElementById('cat-imagen');
            const imgUrlInput = document.getElementById('cat-imagen-url');
            const previewImg = document.getElementById('cat-image-preview');
            const previewEmpty = document.getElementById('cat-image-preview-empty');

            if (!form || !tbody || !saveBtn || !idInput || !nombreInput || !precioInput) {
                return;
            }

            const escHtml = function (v) {
                return String(v == null ? '' : v)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            };
            const setMsg = function (text, tone) {
                if (!msg) return;
                msg.textContent = text || '';
                msg.className = 'small mt-2 ' + (tone === 'danger' ? 'text-danger' : 'text-success');
            };
            const resetForm = function () {
                idInput.value = '';
                nombreInput.value = '';
                descripcionInput.value = '';
                precioInput.value = '';
                if (imgInput) imgInput.value = '';
                if (imgUrlInput) imgUrlInput.value = '';
                if (previewImg) {
                    previewImg.src = '';
                    previewImg.style.display = 'none';
                }
                if (previewEmpty) previewEmpty.style.display = '';
                saveBtn.textContent = 'Guardar item';
                if (cancelBtn) cancelBtn.classList.add('d-none');
            };
            const rowHtml = function (item) {
                const imgRaw = String(item.imagen || item.foto || item.imagen_url || item.foto_url || '').trim();
                const imgUrl = imgRaw ? (/^https?:\/\//i.test(imgRaw) ? imgRaw : ('<?= rtrim(base_url('/'), '/') ?>/' + imgRaw.replace(/^\/+/, ''))) : '';
                return ''
                    + '<tr>'
                    + '<td>' + escHtml(item.id) + '</td>'
                    + '<td>' + escHtml(item.nombre || '') + '</td>'
                    + '<td>' + escHtml(item.descripcion || '-') + '</td>'
                    + '<td>$' + escHtml((Number(item.precio || 0)).toFixed(2)) + '</td>'
                    + '<td>' + (imgUrl ? ('<img src="' + escHtml(imgUrl) + '" alt="Imagen item" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">') : '-') + '</td>'
                    + '<td>' + (Number(item.activo || 0) === 1 ? 'Si' : 'No') + '</td>'
                    + '<td><button type="button" class="btn btn-sm btn-outline-primary js-edit-catalogo"'
                    + ' data-id="' + escHtml(item.id) + '"'
                    + ' data-nombre="' + escHtml(item.nombre || '') + '"'
                    + ' data-descripcion="' + escHtml(item.descripcion || '') + '"'
                    + ' data-precio="' + escHtml(item.precio || 0) + '"'
                    + '>Editar</button></td>'
                    + '</tr>';
            };
            const renderCatalogo = function (items) {
                if (!items || !items.length) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-muted">Sin items cargados.</td></tr>';
                    return;
                }
                tbody.innerHTML = items.map(rowHtml).join('');
            };
            const bindEditButtons = function () {
                document.querySelectorAll('.js-edit-catalogo').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        idInput.value = btn.dataset.id || '';
                        nombreInput.value = btn.dataset.nombre || '';
                        descripcionInput.value = btn.dataset.descripcion || '';
                        precioInput.value = btn.dataset.precio || '';
                        saveBtn.textContent = 'Guardar cambios';
                        if (cancelBtn) cancelBtn.classList.remove('d-none');
                        setMsg('', 'success');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                });
            };

            if (cancelBtn) {
                cancelBtn.addEventListener('click', function () {
                    resetForm();
                    setMsg('', 'success');
                });
            }
            bindEditButtons();

            saveBtn.addEventListener('click', async function () {
                if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
                    return;
                }
                setMsg('Guardando...', 'success');
                saveBtn.disabled = true;
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: new FormData(form),
                    });
                    const data = await res.json();
                    if (!res.ok || data.error) {
                        setMsg((data && data.message) ? data.message : 'No se pudo guardar el item.', 'danger');
                        return;
                    }
                    const items = (data.data && data.data.catalogo) ? data.data.catalogo : [];
                    renderCatalogo(items);
                    bindEditButtons();
                    resetForm();
                    setMsg((data && data.message) ? data.message : 'Item guardado.', 'success');
                } catch (e) {
                    setMsg('No se pudo guardar el item.', 'danger');
                } finally {
                    saveBtn.disabled = false;
                }
            });
        })();

        document.querySelectorAll('.js-ver-detalle').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                const setText = function (id, value) {
                    const el = document.getElementById(id);
                    if (el) {
                        el.textContent = (value || '').trim() !== '' ? value : '-';
                    }
                };

                setText('ticket-id', btn.dataset.id || '');
                setText('ticket-fecha', btn.dataset.fecha || '');
                setText('ticket-cliente', btn.dataset.cliente || '');
                setText('ticket-telefono', btn.dataset.telefono || '');
                setText('ticket-email', '-');
                setText('ticket-direccion', '-');
                setText('ticket-entre-calles', '-');
                setText('ticket-gps', '-');
                const mapLinkEl = document.getElementById('ticket-map-link');
                if (mapLinkEl) {
                    mapLinkEl.classList.add('d-none');
                    mapLinkEl.removeAttribute('href');
                }
                setText('ticket-estado', btn.dataset.estado || '');
                setText('ticket-detalle', btn.dataset.detalle || '');
                const itemsEl = document.getElementById('ticket-items');
                if (itemsEl) {
                    itemsEl.textContent = 'Cargando...';
                }

                try {
                    const basePath = "<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) ?>";
                    const id = btn.dataset.id || '0';
                    const res = await fetch(basePath + '/pedido/' + id + '/json', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const payload = await res.json();
                    if (!res.ok || payload.error || !payload.data) {
                        if (itemsEl) itemsEl.textContent = '-';
                        return;
                    }
                    const p = payload.data.pedido || {};
                    setText('ticket-id', String(p.id || btn.dataset.id || ''));
                    setText('ticket-fecha', String(p.fecha || btn.dataset.fecha || ''));
                    setText('ticket-cliente', String(p.cliente_nombre || btn.dataset.cliente || ''));
                    setText('ticket-telefono', String(p.cliente_telefono || btn.dataset.telefono || ''));
                    setText('ticket-email', String(p.email || ''));
                    setText('ticket-direccion', String(p.direccion || ''));
                    setText('ticket-entre-calles', String(p.entre_calles || ''));
                    const lat = String(p.ubicacion_x || '').trim();
                    const lng = String(p.ubicacion_y || '').trim();
                    const hasCoords = lat !== '' && lng !== '' && !Number.isNaN(Number(lat)) && !Number.isNaN(Number(lng));
                    const gps = hasCoords ? (lat + ' , ' + lng) : '';
                    setText('ticket-gps', gps);
                    if (mapLinkEl && hasCoords) {
                        mapLinkEl.href = 'https://www.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng);
                        mapLinkEl.classList.remove('d-none');
                    }
                    setText('ticket-estado', String(p.estado || btn.dataset.estado || ''));
                    setText('ticket-detalle', String(p.observaciones || btn.dataset.detalle || ''));

                    if (itemsEl) {
                        const insumos = (payload.data.insumos || []);
                        if (!insumos.length) {
                            itemsEl.textContent = 'Sin items.';
                        } else {
                            itemsEl.innerHTML = insumos.map(function (it) {
                                const n = String(it.nombre || ('Articulo #' + String(it.idArticulo || '')));
                                const d = String(it.descripcion || '');
                                const q = String(it.cantidad || '1');
                                const pr = String(it.precio || '0');
                                return '<div>• ' + n + ' x ' + q + ' <span class="text-muted">($' + pr + ')</span>' + (d ? ('<div class="text-muted">' + d + '</div>') : '') + '</div>';
                            }).join('');
                        }
                    }
                } catch (e) {
                    if (itemsEl) itemsEl.textContent = '-';
                }
            });
        });

        document.querySelectorAll('.js-editar-pedido').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const form = document.getElementById('pedidoEditarForm');
                if (!form) {
                    return;
                }
                const basePath = "<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) ?>";
                form.action = basePath + '/pedido/' + (btn.dataset.id || '0') + '/editar';

                const estadoSel = document.getElementById('edit-estado');
                const detalleTxt = document.getElementById('edit-observaciones');
                if (estadoSel) {
                    const estadoVal = (btn.dataset.estado || 'pendiente').toLowerCase().replace('_', ' ');
                    estadoSel.value = estadoVal;
                }
                if (detalleTxt) {
                    detalleTxt.value = btn.dataset.detalle || '';
                }
            });
        });
    </script>
</body>
</html>
