<div class="row g-3 mt-2">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Empresa</div>
                <div class="fw-bold"><?= esc((string) ($superadminStats['empresa_nombre'] ?? '-')) ?></div>
                <div class="small text-muted">Cuenta: <?= esc((string) ($superadminStats['empresa_cuenta'] ?? '-')) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Sucursales</div>
                <div class="fs-3 fw-bold"><?= esc((string) ($superadminStats['clientes_total'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Reservas totales</div>
                <div class="fs-3 fw-bold"><?= esc((string) ($superadminStats['bookings_total'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Activas</div>
                <div class="fs-3 fw-bold text-success"><?= esc((string) ($superadminStats['bookings_active'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Proximas</div>
                <div class="fs-3 fw-bold text-primary"><?= esc((string) ($superadminStats['bookings_upcoming'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        <h5 class="mb-3">Resumen por sucursal</h5>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Sucursal</th>
                        <th>Base</th>
                        <th>Reservas</th>
                        <th>Proximas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sucursales = $superadminStats['sucursales'] ?? []; ?>
                    <?php if (!empty($sucursales)) : ?>
                        <?php foreach ($sucursales as $s) : ?>
                            <tr>
                                <td><?= esc((string) ($s['codigo'] ?? '-')) ?></td>
                                <td><?= esc((string) ($s['razon_social'] ?? '-')) ?></td>
                                <td><?= esc((string) ($s['base'] ?? '-')) ?></td>
                                <td><?= esc((string) ((int) ($s['bookings_total'] ?? 0))) ?></td>
                                <td><?= esc((string) ((int) ($s['bookings_upcoming'] ?? 0))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-muted text-center">No hay sucursales asociadas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
