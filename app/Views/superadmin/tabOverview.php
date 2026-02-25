<div class="row g-3 mt-2">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Clientes totales</div>
                <div class="fs-3 fw-bold"><?= esc((string) ($superadminStats['clientes_total'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Clientes habilitados</div>
                <div class="fs-3 fw-bold text-success"><?= esc((string) ($superadminStats['clientes_habilitados'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Clientes deshabilitados</div>
                <div class="fs-3 fw-bold text-warning"><?= esc((string) ($superadminStats['clientes_deshabilitados'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Rubros activos</div>
                <div class="fs-3 fw-bold text-primary"><?= esc((string) ($superadminStats['rubros_total'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body">
        <h5 class="mb-2">Centro de Superadmin</h5>
        <p class="mb-2">Desde este panel central administrás:</p>
        <ul class="mb-0">
            <li>Alta y estado de clientes.</li>
            <li>Rubros disponibles para nuevos clientes.</li>
            <li>Provisioning inicial de bases por cliente.</li>
        </ul>
    </div>
</div>
