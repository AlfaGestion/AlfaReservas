<?php
$profile = $clientProfile ?? [];
$plan = $currentPlan ?? [];
$planes = $planes ?? [];
$accessUser = $clientAccessUser ?? [];
$clientLink = trim((string) ($profile['link'] ?? ''));
$tenantPublicUrl = $clientLink !== '' ? base_url(ltrim($clientLink, '/')) : '';
$logoUrl = trim((string) ($profile['logo_url'] ?? ''));
$estadoCliente = strtoupper(trim((string) ($plan['estado_cliente'] ?? '')));
$isTrial = $estadoCliente === 'TRIAL';
$trialDaysLeft = isset($plan['trial_days_left']) ? (int) $plan['trial_days_left'] : null;
?>

<div class="card mt-3">
    <div class="card-body">
        <style>
            .plan-card-list { display: grid; gap: 10px; }
            .plan-card {
                border: 1px solid rgba(122, 183, 231, .35);
                border-radius: 14px;
                padding: 14px;
                background: rgba(15, 48, 78, .35);
                cursor: pointer;
                transition: all .2s ease;
            }
            .plan-card.active {
                border-color: #74b7e8;
                box-shadow: 0 0 0 2px rgba(116,183,232,.25) inset;
                background: rgba(32, 73, 109, .65);
            }
        </style>
        <h5 class="mb-3">Perfil de la cuenta</h5>
        <ul class="nav nav-pills mb-3" id="cpProfileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="cp-general-tab" data-bs-toggle="pill" data-bs-target="#cp-general-pane" type="button" role="tab">General</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cp-users-tab" data-bs-toggle="pill" data-bs-target="#cp-users-pane" type="button" role="tab">Usuarios</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="cp-general-pane" role="tabpanel" aria-labelledby="cp-general-tab">

        <form id="clientProfileForm" onsubmit="return false;">
            <input type="hidden" id="cp_id" value="<?= esc((string) ($profile['id'] ?? '')) ?>">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label" for="cp_razon_social">Razon social</label>
                    <input type="text" class="form-control" id="cp_razon_social" value="<?= esc((string) ($profile['razon_social'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="cp_nombre_apellido">Nombre contacto</label>
                    <input type="text" class="form-control" id="cp_nombre_apellido" value="<?= esc((string) ($profile['NombreApellido'] ?? '')) ?>">
                </div>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-success" id="saveClientProfileBtn">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar perfil
                </button>
            </div>
        </form>

        <hr class="my-4">

        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Logo actual</label>
                <div class="border rounded p-2 text-center" id="cp_logo_preview_wrap">
                    <?php if ($logoUrl !== '') : ?>
                        <img id="cp_logo_preview" src="<?= esc($logoUrl) ?>" alt="Logo cliente" style="max-height:80px;max-width:100%;">
                    <?php else : ?>
                        <div class="text-muted">Sin logo cargado</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="cp_logo_file">Actualizar logo</label>
                <input type="file" class="form-control mb-2" id="cp_logo_file" accept=".png,.jpg,.jpeg,.webp">
                <button type="button" class="btn btn-outline-primary" id="saveClientLogoBtn">
                    <i class="fa-solid fa-image me-1"></i> Guardar logo
                </button>
            </div>
        </div>

        <hr class="my-4">

        <h6 class="mb-2">Plan actual</h6>
        <div class="row g-2">
            <div class="col-md-3"><strong>Plan:</strong> <?= esc((string) ($plan['nombre'] ?? '-')) ?></div>
            <div class="col-md-3"><strong>Periodo:</strong> <?= esc((string) ($plan['periodo_human'] ?? '-')) ?></div>
            <div class="col-md-3"><strong>Usuarios:</strong> <?= esc((string) ($plan['included_users'] ?? '-')) ?></div>
            <div class="col-md-3"><strong>Recursos:</strong> <?= esc((string) ($plan['included_resources'] ?? '-')) ?></div>
        </div>
        <?php if ($isTrial) : ?>
            <div class="alert alert-warning mt-2 mb-0 py-2">
                <strong>Modo prueba activo.</strong>
                <?php if ($trialDaysLeft !== null) : ?>
                    Te quedan <?= esc((string) $trialDaysLeft) ?> dia(s).
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="mt-3">
            <label class="form-label">Planes disponibles</label>
            <div class="plan-card-list" id="cp_plan_cards">
                <?php foreach ($planes as $p) : ?>
                    <?php if ((int) ($p['activo'] ?? 0) !== 1) continue; ?>
                    <?php $selected = ((string) ($plan['plan_id'] ?? '') === (string) ($p['id'] ?? '')); ?>
                    <div
                        class="plan-card <?= $selected ? 'active' : '' ?>"
                        data-plan-id="<?= esc((string) ($p['id'] ?? '')) ?>"
                        data-price-month="<?= esc((string) ($p['price_month'] ?? '0')) ?>"
                        data-price-year="<?= esc((string) ($p['price_year'] ?? '0')) ?>"
                        data-users="<?= esc((string) ($p['included_users'] ?? '1')) ?>"
                        data-resources="<?= esc((string) ($p['included_resources'] ?? '2')) ?>"
                    >
                        <div class="fw-bold"><?= esc((string) ($p['nombre'] ?? $p['codigo'] ?? 'Plan')) ?></div>
                        <div class="small text-muted">$<?= esc(number_format((float) ($p['price_month'] ?? 0), 0, ',', '.')) ?> base mensual</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <input type="hidden" id="cp_plan_id" value="<?= esc((string) ($plan['plan_id'] ?? '')) ?>">
        <div class="row g-2 mt-2">
            <div class="col-md-4">
                <label for="cp_plan_periodo" class="form-label">Periodo</label>
                <select class="form-select" id="cp_plan_periodo">
                    <option value="MONTH" <?= (($plan['periodo'] ?? '') === 'MONTH') ? 'selected' : '' ?>>Mensual</option>
                    <option value="YEAR" <?= (($plan['periodo'] ?? '') === 'YEAR') ? 'selected' : '' ?>>Anual</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="cp_plan_resources" class="form-label">Cantidad de servicios</label>
                <input type="number" min="0" class="form-control" id="cp_plan_resources" value="<?= esc((string) ($plan['included_resources'] ?? '2')) ?>">
            </div>
            <div class="col-md-4">
                <label for="cp_plan_users" class="form-label">Cantidad de usuarios</label>
                <input type="number" min="0" class="form-control" id="cp_plan_users" value="<?= esc((string) ($plan['included_users'] ?? '1')) ?>">
            </div>
        </div>
        <div class="mt-2 small text-muted" id="cp_plan_formula">Formula: base del plan + ajustes por servicios/usuarios</div>
        <div class="fw-bold mt-1" id="cp_plan_total">Total estimado: -</div>
        <div class="row g-2 mt-2">
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-outline-primary w-100" id="saveClientPlanBtn">
                    <i class="fa-solid fa-layer-group me-1"></i> Guardar plan
                </button>
            </div>
        </div>

        <hr class="my-4">

        <h6 class="mb-2">Mi acceso</h6>
        <div class="row g-2 mb-2">
            <div class="col-md-6"><strong>Usuario (AlfaReserva):</strong> <?= esc((string) ($accessUser['user'] ?? '-')) ?></div>
            <div class="col-md-6"><strong>Email (AlfaReserva):</strong> <?= esc((string) ($accessUser['email'] ?? '-')) ?></div>
        </div>
        <div class="row g-2">
            <div class="col-md-4">
                <label for="cp_current_password" class="form-label">Contrasena actual</label>
                <input type="password" class="form-control" id="cp_current_password">
            </div>
            <div class="col-md-4">
                <label for="cp_new_password_self" class="form-label">Nueva contrasena</label>
                <input type="password" class="form-control" id="cp_new_password_self">
            </div>
            <div class="col-md-4">
                <label for="cp_new_repeat_password_self" class="form-label">Repetir nueva contrasena</label>
                <input type="password" class="form-control" id="cp_new_repeat_password_self">
            </div>
        </div>
        <div class="form-text">Solo podes cambiar tu propia contrasena.</div>
        <div class="mt-2">
            <button type="button" class="btn btn-outline-warning" id="saveOwnPasswordBtn">
                <i class="fa-solid fa-key me-1"></i> Cambiar mi contrasena
            </button>
        </div>

        <hr class="my-4">
        <h6 class="mb-2">Reservas</h6>
        <?php if ($tenantPublicUrl !== '') : ?>
            <a href="<?= esc($tenantPublicUrl) ?>" target="_blank" class="btn btn-outline-info">
                <i class="fa-solid fa-calendar-plus me-1"></i> Agregar reserva
            </a>
        <?php else : ?>
            <div class="text-muted">No hay link publico configurado.</div>
        <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="cp-users-pane" role="tabpanel" aria-labelledby="cp-users-tab">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Usuarios de esta web</h6>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#clientProfileNewUserModal">
                <i class="fa-solid fa-user-plus me-1"></i> Agregar usuario
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="clientProfileUsersTableBody">
                    <?php if (!empty($clientUsers)) : ?>
                        <?php foreach ($clientUsers as $u) : ?>
                            <tr>
                                <td><?= esc((string) ($u['user'] ?? '-')) ?></td>
                                <td><?= esc((string) ($u['email'] ?? '-')) ?></td>
                                <td><?= ((int) ($u['active'] ?? 0) === 1) ? 'Activo' : 'Inactivo' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="3" class="text-muted text-center">Sin usuarios.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <hr class="my-4">
        <h6 class="mb-2">Reservas</h6>
        <?php if ($tenantPublicUrl !== '') : ?>
            <a href="<?= esc($tenantPublicUrl) ?>" target="_blank" class="btn btn-outline-info">
                <i class="fa-solid fa-calendar-plus me-1"></i> Agregar reserva
            </a>
        <?php else : ?>
            <div class="text-muted">No hay link público configurado.</div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="clientProfileNewUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label for="cp_new_user" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="cp_new_user">
                </div>
                <div class="mb-2">
                    <label for="cp_new_email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="cp_new_email">
                </div>
                <div class="mb-2">
                    <label for="cp_new_password" class="form-label">Contrasena</label>
                    <input type="password" class="form-control" id="cp_new_password">
                </div>
                <div class="mb-0">
                    <label for="cp_new_repeat_password" class="form-label">Repetir contrasena</label>
                    <input type="password" class="form-control" id="cp_new_repeat_password">
                </div>
                <div class="form-text">Este usuario se crea en la base de datos de tu cliente.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="saveClientProfileUserBtn">Guardar usuario</button>
            </div>
        </div>
    </div>
</div>
