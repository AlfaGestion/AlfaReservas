<?php
$profile = $clientProfile ?? [];
$plan = $currentPlan ?? [];
$planes = $planes ?? [];
$logoUrl = trim((string) ($profile['logo_url'] ?? ''));
?>

<div class="card mt-3">
    <div class="card-body">
        <style>
            #cp_plan_cards .plan-card {
                position: relative;
                border: 1px solid rgba(122, 183, 231, .35);
                border-radius: 12px;
                padding: 12px 14px;
                background: rgba(15, 48, 78, .35);
                cursor: pointer;
                transition: all .2s ease;
            }
            #cp_plan_cards .plan-card.active {
                border-color: #74b7e8;
                box-shadow: 0 0 0 2px rgba(116, 183, 232, .25) inset;
                background: rgba(32, 73, 109, .65);
            }
            #cp_plan_cards .plan-check {
                position: absolute;
                right: 10px;
                top: 10px;
                width: 24px;
                height: 24px;
                border-radius: 999px;
                border: 1px solid #6ea8d8;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #e5f3ff;
                background: rgba(24, 57, 86, .75);
                opacity: 0;
                transform: scale(.9);
                transition: all .2s ease;
            }
            #cp_plan_cards .plan-card.active .plan-check {
                opacity: 1;
                transform: scale(1);
                background: #2f82cc;
                border-color: #63b0f4;
                color: #fff;
            }
        </style>
        <h5 class="mb-3">Configurar web</h5>

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

        <h6 class="mb-2">Plan</h6>
        <div class="row g-2">
            <div class="col-md-3"><strong>Plan:</strong> <?= esc((string) ($plan['nombre'] ?? '-')) ?></div>
            <div class="col-md-3"><strong>Periodo:</strong> <?= esc((string) ($plan['periodo_human'] ?? '-')) ?></div>
            <div class="col-md-3"><strong>Usuarios:</strong> <?= esc((string) ($plan['included_users'] ?? '-')) ?></div>
            <div class="col-md-3"><strong>Recursos:</strong> <?= esc((string) ($plan['included_resources'] ?? '-')) ?></div>
        </div>

        <div class="mt-3">
            <label class="form-label">Planes disponibles</label>
            <div class="row g-2" id="cp_plan_cards">
                <?php foreach ($planes as $p) : ?>
                    <?php if ((int) ($p['activo'] ?? 0) !== 1) continue; ?>
                    <?php $selected = ((string) ($plan['plan_id'] ?? '') === (string) ($p['id'] ?? '')); ?>
                    <div class="col-md-4">
                        <div
                            class="plan-card <?= $selected ? 'active' : '' ?>"
                            data-plan-id="<?= esc((string) ($p['id'] ?? '')) ?>"
                            data-price-month="<?= esc((string) ($p['price_month'] ?? '0')) ?>"
                            data-price-year="<?= esc((string) ($p['price_year'] ?? '0')) ?>"
                            data-users="<?= esc((string) ($p['included_users'] ?? '1')) ?>"
                            data-resources="<?= esc((string) ($p['included_resources'] ?? '2')) ?>"
                        >
                            <span class="plan-check"><i class="fa-solid fa-check"></i></span>
                            <div class="fw-bold"><?= esc((string) ($p['nombre'] ?? $p['codigo'] ?? 'Plan')) ?></div>
                            <div class="small text-muted">$<?= esc(number_format((float) ($p['price_month'] ?? 0), 0, ',', '.')) ?> base mensual</div>
                        </div>
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
        <div class="mt-3">
            <button type="button" class="btn btn-outline-primary" id="saveClientPlanBtn">
                <i class="fa-solid fa-layer-group me-1"></i> Guardar plan
            </button>
        </div>
    </div>
</div>
