<div class="row mt-3 g-3">
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Nuevo plan</h5>
                <form id="planForm" action="<?= base_url('savePlan') ?>" method="POST" onsubmit="return false;">
                    <input type="hidden" id="plan_id" name="id" value="">
                    <div class="mb-2">
                        <label for="plan_codigo" class="form-label">Codigo</label>
                        <input type="text" class="form-control" id="plan_codigo" name="codigo" placeholder="BASICO" required>
                    </div>
                    <div class="mb-2">
                        <label for="plan_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="plan_nombre" name="nombre" placeholder="Basico" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="plan_price_month" class="form-label">Precio mensual</label>
                            <input type="number" step="0.01" class="form-control" id="plan_price_month" name="price_month" required>
                        </div>
                        <div class="col-6">
                            <label for="plan_price_year" class="form-label">Precio anual</label>
                            <input type="number" step="0.01" class="form-control" id="plan_price_year" name="price_year" required>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <label for="plan_included_users" class="form-label">Usuarios incluidos</label>
                            <input type="number" class="form-control" id="plan_included_users" name="included_users" value="1" min="0">
                        </div>
                        <div class="col-6">
                            <label for="plan_included_resources" class="form-label">Recursos incluidos</label>
                            <input type="number" class="form-control" id="plan_included_resources" name="included_resources" value="2" min="0">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <label for="plan_max_users" class="form-label">Max usuarios</label>
                            <input type="number" class="form-control" id="plan_max_users" name="max_users" value="50" min="0">
                        </div>
                        <div class="col-6">
                            <label for="plan_max_resources" class="form-label">Max recursos</label>
                            <input type="number" class="form-control" id="plan_max_resources" name="max_resources" value="100" min="0">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <label for="plan_soporte_horas" class="form-label">Soporte (hs)</label>
                            <input type="number" class="form-control" id="plan_soporte_horas" name="soporte_horas" value="0" min="0">
                        </div>
                        <div class="col-6">
                            <label for="plan_email_por_reserva" class="form-label">Email por reserva</label>
                            <select class="form-select" id="plan_email_por_reserva" name="email_por_reserva">
                                <option value="0" selected>No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-primary" id="planSaveBtn">Guardar plan</button>
                        <button type="button" class="btn btn-outline-secondary d-none" id="planCancelEditBtn">Cancelar edicion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Planes cargados</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Nombre</th>
                                <th>Mensual</th>
                                        <th>Anual</th>
                                        <th>Email x reserva</th>
                                        <th>Activo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="planesTableBody">
                                    <?php if (!empty($planes)) : ?>
                                        <?php foreach ($planes as $plan) : ?>
                                            <tr>
                                                <td class="d-none"><?= esc((string) ($plan['id'] ?? '')) ?></td>
                                                <td><?= esc((string) ($plan['codigo'] ?? '')) ?></td>
                                                <td><?= esc((string) ($plan['nombre'] ?? '')) ?></td>
                                                <td><?= esc((string) ($plan['price_month'] ?? '0')) ?></td>
                                                <td><?= esc((string) ($plan['price_year'] ?? '0')) ?></td>
                                                <td><?= ((int) ($plan['email_por_reserva'] ?? 0) === 1) ? 'Si' : 'No' ?></td>
                                                <td><?= ((int) ($plan['activo'] ?? 0) === 1) ? 'Si' : 'No' ?></td>
                                                <td>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary edit-plan"
                                                        data-id="<?= esc((string) ($plan['id'] ?? '')) ?>"
                                                        data-codigo="<?= esc((string) ($plan['codigo'] ?? '')) ?>"
                                                        data-nombre="<?= esc((string) ($plan['nombre'] ?? '')) ?>"
                                                        data-price-month="<?= esc((string) ($plan['price_month'] ?? '0')) ?>"
                                                        data-price-year="<?= esc((string) ($plan['price_year'] ?? '0')) ?>"
                                                        data-included-users="<?= esc((string) ($plan['included_users'] ?? '1')) ?>"
                                                        data-included-resources="<?= esc((string) ($plan['included_resources'] ?? '2')) ?>"
                                                        data-max-users="<?= esc((string) ($plan['max_users'] ?? '50')) ?>"
                                                        data-max-resources="<?= esc((string) ($plan['max_resources'] ?? '100')) ?>"
                                                        data-soporte-horas="<?= esc((string) ($plan['soporte_horas'] ?? '0')) ?>"
                                                        data-email-por-reserva="<?= esc((string) ((int) ($plan['email_por_reserva'] ?? 0))) ?>"
                                                    >
                                                        Editar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No hay planes cargados.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
