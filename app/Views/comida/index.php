<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($cliente['razon_social'] ?? 'Portal pedidos') ?></title>
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
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
        .portal-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .cart-shortcut {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .portal-logo {
            max-height: 68px;
            max-width: 240px;
            object-fit: contain;
        }
        .cart-summary {
            font-size: .9rem;
            color: #356089;
        }
        .catalogo-mode-switch .btn {
            min-width: 120px;
        }
        .catalogo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 14px;
        }
        .catalogo-item-card {
            border: 1px solid #d7e6f5;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .catalogo-item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(18, 40, 60, .12);
        }
        .catalogo-item-image {
            height: 180px;
            background: linear-gradient(135deg, #eef5fc, #d9e9fa);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .catalogo-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .catalogo-item-image .placeholder {
            color: #356089;
            font-size: 2rem;
            line-height: 1;
        }
        .catalogo-item-body {
            padding: 12px;
        }
        .catalogo-item-title {
            font-weight: 700;
            margin-bottom: 4px;
        }
        .catalogo-qty-controls {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }
        .catalogo-qty-value {
            min-width: 28px;
            text-align: center;
            font-weight: 700;
        }
        .catalogo-item-price {
            font-weight: 700;
            color: #0f5ea8;
        }
        .floating-cart-btn {
            position: fixed;
            right: 18px;
            top: 18px;
            z-index: 1050;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            box-shadow: 0 8px 20px rgba(11, 34, 54, .25);
        }
        .floating-cart-count {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            padding: 0 6px;
            background: #ff4d4f;
            color: #fff;
            font-size: .75rem;
            line-height: 20px;
            text-align: center;
            font-weight: 700;
        }
        body.theme-dark {
            background-color: #081b2d;
            color: #e8f3ff;
        }
        body.theme-dark .portal-card {
            background: rgba(17, 39, 60, 0.92);
            border-color: #3a5f80;
            color: #e8f3ff;
        }
        body.theme-dark .table {
            color: #e8f3ff;
        }
        body.theme-dark .table-bordered th,
        body.theme-dark .table-bordered td,
        body.theme-dark .table > :not(caption) > * > * {
            border-color: #3a5f80;
        }
        body.theme-dark .table thead th {
            background: #1b3957;
            color: #d8e9fb;
        }
        body.theme-dark .form-control,
        body.theme-dark .form-select {
            background: #10253a;
            border-color: #3a5f80;
            color: #e8f3ff;
        }
        body.theme-dark .form-control::placeholder {
            color: #a9c6e2;
        }
        body.theme-dark .btn-outline-dark {
            border-color: #77b7ff;
            color: #cbe5ff;
        }
        body.theme-dark .btn-outline-dark:hover {
            background: #1f4467;
            border-color: #7ab7e7;
            color: #fff;
        }
        body.theme-dark .catalogo-item-card {
            background: #132a40;
            border-color: #3a5f80;
            color: #e8f3ff;
        }
        body.theme-dark .catalogo-item-image {
            background: linear-gradient(135deg, #17314b, #214261);
        }
        body.theme-dark .catalogo-item-image .placeholder {
            color: #a4c8e9;
        }
        body.theme-dark .catalogo-item-price {
            color: #77b7ff;
        }
        body.theme-dark .cart-summary {
            color: #b7d8f6;
        }
        body.theme-dark .offcanvas {
            background: #10253a;
            color: #e8f3ff;
            border-left: 1px solid #3a5f80;
        }
        body.theme-dark .offcanvas-header {
            border-bottom: 1px solid #31557a;
        }
        body.theme-dark .offcanvas .text-muted {
            color: #b7d8f6 !important;
        }
        body.theme-dark .offcanvas .form-control,
        body.theme-dark .offcanvas .form-select,
        body.theme-dark .offcanvas textarea {
            background: #0d2134;
            color: #e8f3ff;
            border-color: #3a5f80;
        }
        body.theme-dark .offcanvas .form-control::placeholder,
        body.theme-dark .offcanvas textarea::placeholder {
            color: #a9c6e2;
        }
        body.theme-dark .offcanvas .btn-close {
            filter: invert(1) brightness(1.4);
            opacity: .9;
        }
        body.theme-dark .floating-cart-btn {
            box-shadow: 0 8px 20px rgba(0, 0, 0, .45);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="portal-header">
            <img class="portal-logo" src="<?= esc((string) (($branding['logo'] ?? '') !== '' ? $branding['logo'] : base_url('alfa.png'))) ?>" alt="Logo">
        </div>

        <?php if (session('msg')) : ?>
            <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
                <small><?= session('msg.body') ?></small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (session('tracking_link')) : ?>
            <div class="alert alert-success">
                <div class="fw-semibold">Tu pedido fue generado.</div>
                <div class="small">Link de seguimiento:</div>
                <div>
                    <a href="<?= esc((string) session('tracking_link')) ?>" target="_blank"><?= esc((string) session('tracking_link')) ?></a>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($tenantNotice)) : ?>
            <div class="alert alert-warning" role="alert">
                <small><?= esc($tenantNotice) ?></small>
            </div>
        <?php endif; ?>

        <div class="card portal-card shadow-sm mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="mb-0">Catalogo</h5>
                    <div class="btn-group btn-group-sm catalogo-mode-switch" role="group" aria-label="Modo de catalogo">
                        <button type="button" class="btn btn-outline-primary active" id="catalogoModeCardsBtn">Modo catalogo</button>
                        <button type="button" class="btn btn-outline-primary" id="catalogoModeTableBtn">Tabla</button>
                    </div>
                </div>
                <?php if (empty($catalogo)) : ?>
                    <div class="alert alert-warning mb-0">No hay items en el catalogo de esta base.</div>
                <?php else : ?>
                    <?php
                        $resolveImage = static function (array $item): string {
                            foreach (['imagen_url', 'foto_url', 'imagen', 'foto'] as $key) {
                                $v = trim((string) ($item[$key] ?? ''));
                                if ($v === '') {
                                    continue;
                                }
                                if (preg_match('#^https?://#i', $v) === 1) {
                                    return $v;
                                }
                                return base_url(ltrim($v, '/'));
                            }
                            return '';
                        };
                    ?>
                    <div id="catalogoCardsMode">
                        <div class="catalogo-grid">
                            <?php foreach ($catalogo as $item) : ?>
                                <?php
                                    $active = (int) ($item['activo'] ?? 0) === 1;
                                    $imageUrl = $resolveImage($item);
                                ?>
                                <div
                                    class="catalogo-item-card <?= $active ? '' : 'opacity-50' ?>"
                                    data-item-id="<?= esc((string) ($item['id'] ?? '')) ?>"
                                    data-item-name="<?= esc((string) ($item['nombre'] ?? '')) ?>"
                                    data-item-desc="<?= esc((string) ($item['descripcion'] ?? ''), 'attr') ?>"
                                    data-item-price="<?= esc((string) ($item['precio'] ?? '0')) ?>"
                                    title="<?= $active ? 'Click para seleccionar este item' : 'Item inactivo' ?>"
                                >
                                    <div class="catalogo-item-image">
                                        <?php if ($imageUrl !== '') : ?>
                                            <img src="<?= esc($imageUrl) ?>" alt="<?= esc((string) ($item['nombre'] ?? 'Item')) ?>">
                                        <?php else : ?>
                                            <div class="placeholder">🍽</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="catalogo-item-body">
                                        <div class="catalogo-item-title"><?= esc((string) ($item['nombre'] ?? '')) ?></div>
                                        <div class="small mb-1"><?= esc((string) ($item['descripcion'] ?? '')) ?></div>
                                        <div class="catalogo-item-price">$<?= esc((string) $item['precio']) ?></div>
                                        <div class="small"><?= $active ? 'Activo' : 'Inactivo' ?></div>
                                        <?php if ($active) : ?>
                                            <div class="catalogo-qty-controls">
                                                <button type="button" class="btn btn-outline-secondary btn-sm js-item-minus" data-item-id="<?= esc((string) ($item['id'] ?? '')) ?>">-</button>
                                                <span class="catalogo-qty-value js-item-qty" data-item-id="<?= esc((string) ($item['id'] ?? '')) ?>">0</span>
                                                <button type="button" class="btn btn-outline-primary btn-sm js-item-plus" data-item-id="<?= esc((string) ($item['id'] ?? '')) ?>">+</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="table-responsive d-none" id="catalogoTableMode">
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
            <a href="<?= base_url(ltrim((string) ($adminBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? '') . '/admin')), '/')) ?>" class="btn btn-outline-dark btn-sm">Acceso admin</a>
        </div>
    </div>
    <button type="button" class="btn btn-primary floating-cart-btn" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas" aria-controls="cartOffcanvas" title="Carrito">
        🛒
        <span class="floating-cart-count" id="floatingCartCount">0</span>
    </button>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="cartOffcanvasLabel">🛒 Carrito</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mb-3">
                <div class="small text-muted">Resumen</div>
                <div id="cartDetailText" class="fw-semibold">Sin item seleccionado.</div>
                <div id="cartItemsList" class="mt-2 small"></div>
            </div>
            <form action="<?= base_url(ltrim((string) ($publicBasePath ?? ('pedidos/' . ($cliente['codigo'] ?? ''))), '/')) . '/reservar' ?>" method="POST">
                <input type="hidden" id="catalogo_id" name="catalogo_id" value="">
                <input type="hidden" id="cantidad" name="cantidad" value="1">
                <input type="hidden" id="cart_items" name="cart_items" value="">
                <div class="mb-2">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input class="form-control" type="text" id="nombre" name="nombre" required>
                </div>
                <div class="mb-2">
                    <label class="form-label" for="telefono">Telefono</label>
                    <input class="form-control" type="text" id="telefono" name="telefono">
                </div>
                <div class="mb-2">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" type="email" id="email" name="email">
                </div>
                <div class="mb-2">
                    <label class="form-label" for="direccion">Direccion</label>
                    <input class="form-control" type="text" id="direccion" name="direccion">
                </div>
                <div class="mb-2">
                    <label class="form-label" for="entre_calles">Entre calles</label>
                    <input class="form-control" type="text" id="entre_calles" name="entre_calles">
                </div>
                <div class="mb-2">
                    <label class="form-label d-block">Ubicacion GPS</label>
                    <div class="input-group mb-2">
                        <input class="form-control" type="text" id="ubicacion_x" name="ubicacion_x" placeholder="X / Latitud">
                        <input class="form-control" type="text" id="ubicacion_y" name="ubicacion_y" placeholder="Y / Longitud">
                        <button type="button" class="btn btn-outline-secondary" id="getGpsBtn">Obtener ubicacion</button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="observaciones">Observaciones</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                </div>
                <button
                    type="submit"
                    id="cartSubmitBtn"
                    class="btn btn-primary w-100"
                    <?= (empty($catalogo) || (($tenantMode ?? 'full') === 'read_only')) ? 'disabled data-locked="1"' : '' ?>
                >Confirmar pedido</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const btnCards = document.getElementById('catalogoModeCardsBtn');
            const btnTable = document.getElementById('catalogoModeTableBtn');
            const cardsMode = document.getElementById('catalogoCardsMode');
            const tableMode = document.getElementById('catalogoTableMode');
            const itemSelect = document.getElementById('catalogo_id');
            const qtyInput = document.getElementById('cantidad');
            const cartSummaryText = document.getElementById('cartSummaryText');
            const cartDetailText = document.getElementById('cartDetailText');
            const floatingCartCount = document.getElementById('floatingCartCount');
            const cartSubmitBtn = document.getElementById('cartSubmitBtn');
            const cartSubmitLocked = !!(cartSubmitBtn && cartSubmitBtn.getAttribute('data-locked') === '1');
            const cartItemsInput = document.getElementById('cart_items');
            const gpsBtn = document.getElementById('getGpsBtn');
            const ubicacionXInput = document.getElementById('ubicacion_x');
            const ubicacionYInput = document.getElementById('ubicacion_y');
            const qtyByItem = {};
            const itemData = {};

            if (btnCards && btnTable && cardsMode && tableMode) {
                btnCards.addEventListener('click', function () {
                    btnCards.classList.add('active');
                    btnTable.classList.remove('active');
                    cardsMode.classList.remove('d-none');
                    tableMode.classList.add('d-none');
                });
                btnTable.addEventListener('click', function () {
                    btnTable.classList.add('active');
                    btnCards.classList.remove('active');
                    tableMode.classList.remove('d-none');
                    cardsMode.classList.add('d-none');
                });
            }

            if (itemSelect) {
                const refreshCartSummary = function () {
                    const cartItemsList = document.getElementById('cartItemsList');
                    const selectedId = itemSelect ? (itemSelect.value || '') : '';
                    const qty = qtyInput ? Number(qtyInput.value || 1) : 1;
                    const activeItems = Object.keys(qtyByItem)
                        .filter(function (id) { return Number(qtyByItem[id] || 0) > 0 && !!itemData[id]; })
                        .map(function (id) {
                            return {
                                id: id,
                                name: itemData[id].name,
                                descripcion: itemData[id].desc || '',
                                price: itemData[id].price,
                                qty: Number(qtyByItem[id] || 0)
                            };
                        });
                    if (cartItemsInput) {
                        cartItemsInput.value = JSON.stringify(activeItems.map(function (it) {
                            return { id: Number(it.id), qty: Number(it.qty) };
                        }));
                    }
                    if (floatingCartCount) {
                        const totalQty = activeItems.reduce(function (acc, it) { return acc + Number(it.qty || 0); }, 0);
                        floatingCartCount.textContent = String(totalQty);
                    }

                    if (cartItemsList) {
                        if (activeItems.length === 0) {
                            cartItemsList.innerHTML = '<span class="text-muted">No agregaste artículos todavía.</span>';
                        } else {
                            cartItemsList.innerHTML = activeItems.map(function (it) {
                                return '<div>• ' + it.name + ' x ' + it.qty + ' <span class="text-muted">($' + it.price + ')</span></div>';
                            }).join('');
                        }
                    }

                    if (!selectedId || !itemData[selectedId]) {
                        if (cartSummaryText) cartSummaryText.textContent = 'Sin item seleccionado';
                        if (cartDetailText) {
                            cartDetailText.textContent = 'Sin item seleccionado.';
                        }
                        if (cartSubmitBtn && !cartSubmitLocked) {
                            cartSubmitBtn.disabled = true;
                        }
                        return;
                    }
                    const meta = itemData[selectedId];
                    const line = meta.name + ' x ' + qty;
                    if (cartSummaryText) cartSummaryText.textContent = line;
                    if (cartDetailText) {
                        cartDetailText.textContent = line + ' - $' + meta.price + ' | ' + activeItems.length + ' item(s) en carrito';
                    }
                    if (cartSubmitBtn && !cartSubmitLocked) {
                        cartSubmitBtn.disabled = false;
                    }
                };

                const updateQtyBadges = function () {
                    document.querySelectorAll('.js-item-qty[data-item-id]').forEach(function (el) {
                        const id = el.getAttribute('data-item-id') || '';
                        el.textContent = String(qtyByItem[id] || 0);
                    });
                };

                const setSelectedItemQty = function (itemId, nextQty) {
                    const qty = Math.max(0, Number(nextQty || 0));
                    qtyByItem[itemId] = qty;
                    updateQtyBadges();

                    if (qty <= 0) {
                        if (itemSelect.value === itemId) {
                            itemSelect.value = '';
                            if (qtyInput) qtyInput.value = '1';
                        }
                        refreshCartSummary();
                        return;
                    }

                    itemSelect.value = itemId;
                    if (qtyInput) {
                        qtyInput.value = String(qty);
                    }
                    itemSelect.dispatchEvent(new Event('change'));
                    refreshCartSummary();
                };

                itemSelect.addEventListener('change', refreshCartSummary);
                refreshCartSummary();
                updateQtyBadges();

                document.querySelectorAll('.catalogo-item-card[data-item-id]').forEach(function (card) {
                    const cardId = card.getAttribute('data-item-id') || '';
                    if (cardId) {
                        itemData[cardId] = {
                            name: card.getAttribute('data-item-name') || 'Item',
                            desc: card.getAttribute('data-item-desc') || '',
                            price: card.getAttribute('data-item-price') || '0'
                        };
                    }
                    card.addEventListener('click', function () {
                        if (card.classList.contains('opacity-50')) {
                            return;
                        }
                        const id = card.getAttribute('data-item-id');
                        itemSelect.value = id || '';
                        if (id && (!qtyByItem[id] || qtyByItem[id] < 1)) {
                            qtyByItem[id] = 1;
                        }
                        if (qtyInput && id) {
                            qtyInput.value = String(qtyByItem[id] || 1);
                        }
                        updateQtyBadges();
                        itemSelect.dispatchEvent(new Event('change'));
                    });
                });

                document.querySelectorAll('.js-item-plus[data-item-id]').forEach(function (btn) {
                    btn.addEventListener('click', function (ev) {
                        ev.stopPropagation();
                        const id = btn.getAttribute('data-item-id') || '';
                        if (!id) return;
                        const next = Number(qtyByItem[id] || 0) + 1;
                        setSelectedItemQty(id, next);
                    });
                });

                document.querySelectorAll('.js-item-minus[data-item-id]').forEach(function (btn) {
                    btn.addEventListener('click', function (ev) {
                        ev.stopPropagation();
                        const id = btn.getAttribute('data-item-id') || '';
                        if (!id) return;
                        const next = Number(qtyByItem[id] || 0) - 1;
                        setSelectedItemQty(id, next);
                    });
                });
            }

            if (gpsBtn && ubicacionXInput && ubicacionYInput && navigator.geolocation) {
                gpsBtn.addEventListener('click', function () {
                    navigator.geolocation.getCurrentPosition(function (pos) {
                        ubicacionXInput.value = String(pos.coords.latitude || '');
                        ubicacionYInput.value = String(pos.coords.longitude || '');
                    }, function () {
                        alert('No se pudo obtener la ubicacion GPS.');
                    }, { enableHighAccuracy: true, timeout: 10000 });
                });
            }
        })();
    </script>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
</body>
</html>
