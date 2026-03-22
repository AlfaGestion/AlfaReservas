<?php
$profile = $clientProfile ?? [];
$plan = $currentPlan ?? [];
$planes = $planes ?? [];
$accessUser = $clientAccessUser ?? [];
$clientBaseSlug = strtolower(trim((string) ($profile['base'] ?? '')));
$clientLink = trim((string) ($profile['link'] ?? ''));
$tenantPublicUrl = $clientLink !== '' ? base_url(ltrim($clientLink, '/')) : ($clientBaseSlug !== '' ? base_url($clientBaseSlug) : '');
$rubro = strtolower(trim((string) ($profile['rubro_descripcion'] ?? '')));
$adminSuffix = in_array($rubro, ['comida', 'pedidos'], true) ? '/adminWeb' : '/admin';
$logoUrl = trim((string) ($profile['logo_url'] ?? ''));
$setup = $clientSetupConfig ?? [];
$setupSiteTitle = trim((string) ($setup['site_title'] ?? ($profile['razon_social'] ?? '')));
$setupServiceName = trim((string) ($setup['service_name'] ?? 'Reservas'));
$setupReservationEmail = trim((string) ($setup['reservation_email'] ?? ($accessUser['email'] ?? ($profile['email'] ?? ''))));
$setupOpenDays = json_decode((string) ($setup['open_days'] ?? '["1","2","3","4","5","6"]'), true);
$setupOpenDays = is_array($setupOpenDays) ? array_map('strval', $setupOpenDays) : ['1', '2', '3', '4', '5', '6'];
$setupOpenFrom = trim((string) ($setup['open_from'] ?? '08'));
$setupOpenUntil = trim((string) ($setup['open_until'] ?? '22'));
$setupPrimaryColor = trim((string) ($setup['primary_color'] ?? '#165ECC'));
$setupAccentColor = trim((string) ($setup['accent_color'] ?? '#E3F50D'));
$advancedSchedule = json_decode((string) ($setup['advanced_schedule_json'] ?? ''), true);
$advancedSchedule = is_array($advancedSchedule) ? $advancedSchedule : [];
$openSetup = !empty($openClientSetup);
$setupDaysLabels = [
    '1' => 'Lun',
    '2' => 'Mar',
    '3' => 'Mie',
    '4' => 'Jue',
    '5' => 'Vie',
    '6' => 'Sab',
    '0' => 'Dom',
];
$setupHourOptions = ['01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','00'];
$setupAdvancedSchedule = [];
foreach (array_keys($setupDaysLabels) as $dayValue) {
    $dayKey = (string) $dayValue;
    $defaultMorningUntil = ((int) $setupOpenUntil <= 14) ? $setupOpenUntil : '13';
    $defaultAfternoonFrom = ((int) $setupOpenFrom >= 14) ? $setupOpenFrom : '14';
    $row = is_array($advancedSchedule[$dayKey] ?? null) ? $advancedSchedule[$dayKey] : [];
    $isActive = in_array($dayKey, $setupOpenDays, true);
    $setupAdvancedSchedule[$dayKey] = [
        'active' => !empty($row['active']) || $isActive,
        'morning_enabled' => array_key_exists('morning_enabled', $row) ? !empty($row['morning_enabled']) : $isActive,
        'morning_from' => (string) ($row['morning_from'] ?? $setupOpenFrom),
        'morning_until' => (string) ($row['morning_until'] ?? $defaultMorningUntil),
        'afternoon_enabled' => array_key_exists('afternoon_enabled', $row) ? !empty($row['afternoon_enabled']) : ($isActive && (int) $setupOpenUntil > 14),
        'afternoon_from' => (string) ($row['afternoon_from'] ?? $defaultAfternoonFrom),
        'afternoon_until' => (string) ($row['afternoon_until'] ?? $setupOpenUntil),
    ];
}
$estadoCliente = strtoupper(trim((string) ($plan['estado_cliente'] ?? '')));
$isTrial = $estadoCliente === 'TRIAL';
$trialDaysLeft = isset($plan['trial_days_left']) ? (int) $plan['trial_days_left'] : null;
$usersQuotaTotal = (int) ($plan['users_quota_total'] ?? 0);
$usersQuotaUsed = (int) ($plan['users_quota_used'] ?? 0);
$usersQuotaRemaining = (int) ($plan['users_quota_remaining'] ?? 0);
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
            .cp-plan-current {
                border: 1px solid rgba(122, 183, 231, .35);
                border-radius: 12px;
                padding: 10px;
                background: rgba(16, 46, 73, .2);
            }
            body.theme-dark .cp-plan-current {
                background: rgba(53, 89, 123, .35);
                border-color: rgba(166, 205, 237, .45);
                color: #e9f4ff;
            }
            body.theme-dark .cp-plan-current strong {
                color: #ffffff;
            }
            body.theme-dark .plan-card .text-muted {
                color: #d8eafe !important;
            }
            body.theme-dark #cp_plan_formula,
            body.theme-dark #cp_plan_total {
                color: #e9f4ff !important;
            }
            .cp-setup-frame {
                border: 1px solid rgba(122, 183, 231, .35);
                border-radius: 18px;
                padding: 18px;
                background: linear-gradient(180deg, rgba(22, 94, 204, .08), rgba(255,255,255,.9));
            }
            .cp-setup-kicker {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 7px 12px;
                border-radius: 999px;
                border: 1px solid rgba(122, 183, 231, .35);
                background: rgba(22, 94, 204, .08);
                color: #1b4f87;
                font-size: .78rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .06em;
            }
            .cp-setup-kicker::before {
                content: "";
                width: 9px;
                height: 9px;
                border-radius: 50%;
                background: #e3f50d;
                box-shadow: 0 0 0 4px rgba(227, 245, 13, .12);
            }
            .cp-setup-frame .form-control,
            .cp-setup-frame .form-select {
                border-radius: 12px;
            }
            .cp-setup-logo-box {
                border: 1px dashed rgba(122, 183, 231, .5);
                border-radius: 16px;
                padding: 14px;
                text-align: center;
                background: rgba(255,255,255,.6);
                min-height: 122px;
            }
            .cp-setup-days {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .cp-setup-day {
                position: relative;
            }
            .cp-setup-day label {
                display: block;
                margin: 0;
            }
            .cp-setup-day input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }
            .cp-setup-day span {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 54px;
                padding: 9px 12px;
                border-radius: 12px;
                border: 1px solid rgba(122, 183, 231, .35);
                background: rgba(255,255,255,.72);
                color: #234a71;
                font-weight: 700;
                transition: all .18s ease;
                cursor: pointer;
                user-select: none;
            }
            .cp-setup-day input:checked + label span {
                background: #165ecc;
                border-color: #165ecc;
                color: #fff;
                box-shadow: 0 10px 24px rgba(22, 94, 204, .18);
            }
            .cp-color-chip {
                display: flex;
                align-items: center;
                gap: 12px;
                border: 1px solid rgba(122, 183, 231, .35);
                border-radius: 14px;
                padding: 10px 12px;
                background: rgba(255,255,255,.68);
            }
            .cp-color-chip input[type="color"] {
                width: 52px;
                height: 42px;
                border: none;
                background: transparent;
                padding: 0;
            }
            .cp-setup-note {
                border-radius: 14px;
                border: 1px solid rgba(255, 160, 66, .35);
                background: rgba(255, 160, 66, .08);
                padding: 14px 16px;
            }
            body.theme-dark .cp-setup-frame {
                background: linear-gradient(180deg, rgba(20, 51, 84, .96), rgba(13, 31, 49, .92));
                border-color: rgba(122, 183, 231, .28);
                color: #e9f4ff;
            }
            body.theme-dark .cp-setup-kicker {
                background: rgba(77, 122, 180, .18);
                border-color: rgba(122, 183, 231, .28);
                color: #dbefff;
            }
            body.theme-dark .cp-setup-logo-box,
            body.theme-dark .cp-setup-day span,
            body.theme-dark .cp-color-chip {
                background: rgba(24, 51, 80, .74);
                border-color: rgba(122, 183, 231, .28);
                color: #dbefff;
            }
            body.theme-dark .cp-setup-note {
                background: rgba(255, 160, 66, .12);
                border-color: rgba(255, 160, 66, .28);
                color: #ffe4c3;
            }
            body.theme-dark .cp-setup-frame .text-muted {
                color: #9fc0df !important;
            }
            .cp-advanced-schedule {
                border: 1px solid rgba(122, 183, 231, .28);
                border-radius: 16px;
                padding: 14px;
                background: rgba(255,255,255,.45);
            }
            .cp-advanced-toggle-btn {
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                border: 1px solid rgba(122, 183, 231, .35);
                border-radius: 14px;
                background: rgba(255,255,255,.62);
                color: #1f4467;
                font-weight: 700;
                padding: 12px 14px;
            }
            .cp-advanced-toggle-btn .fa-chevron-down {
                transition: transform .18s ease;
            }
            .cp-advanced-toggle-btn[aria-expanded="true"] .fa-chevron-down {
                transform: rotate(180deg);
            }
            .cp-advanced-body {
                display: none;
                margin-top: 12px;
            }
            .cp-advanced-body.is-open {
                display: block;
            }
            .cp-advanced-toolbar {
                display: grid;
                grid-template-columns: 1fr 1fr auto;
                gap: 12px;
                margin-bottom: 14px;
                align-items: end;
            }
            .cp-advanced-global {
                border: 1px solid rgba(122, 183, 231, .25);
                border-radius: 14px;
                padding: 10px 12px;
                background: rgba(255,255,255,.58);
            }
            .cp-advanced-actions {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .cp-advanced-row {
                display: grid;
                grid-template-columns: 90px 1fr 1fr;
                gap: 12px;
                align-items: start;
                padding: 12px 0;
                border-top: 1px solid rgba(122, 183, 231, .18);
            }
            .cp-advanced-row:first-child {
                border-top: none;
                padding-top: 0;
            }
            .cp-advanced-day {
                font-weight: 700;
                color: #1f4467;
                padding-top: 8px;
            }
            .cp-advanced-slot {
                border: 1px solid rgba(122, 183, 231, .25);
                border-radius: 14px;
                padding: 10px;
                background: rgba(255,255,255,.62);
            }
            .cp-advanced-slot.disabled {
                opacity: .55;
            }
            .cp-advanced-toggle {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 10px;
                font-weight: 600;
            }
            body.theme-dark .cp-advanced-schedule,
            body.theme-dark .cp-advanced-slot {
                background: rgba(24, 51, 80, .62);
                border-color: rgba(122, 183, 231, .2);
            }
            body.theme-dark .cp-advanced-global {
                background: rgba(24, 51, 80, .74);
                border-color: rgba(122, 183, 231, .2);
            }
            body.theme-dark .cp-advanced-toggle-btn {
                background: rgba(24, 51, 80, .74);
                border-color: rgba(122, 183, 231, .2);
                color: #dbefff;
            }
            body.theme-dark .cp-advanced-day {
                color: #dbefff;
            }
            @media (max-width: 991px) {
                .cp-advanced-row {
                    grid-template-columns: 1fr;
                }
                .cp-advanced-toolbar {
                    grid-template-columns: 1fr;
                }
                .cp-advanced-day {
                    padding-top: 0;
                }
            }
            #cp_web_config_block > h6,
            #cp_web_config_block > .border.rounded.p-3.mb-3 {
                display: none;
            }
        </style>

        <h5 class="mb-3">Perfil de la cuenta</h5>

        <div id="cp_web_config_block" style="display:none;">
            <div class="cp-setup-frame mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="cp-setup-kicker mb-2">Configuracion inicial</div>
                        <h6 class="mb-1">Configura tu sitio</h6>
                        <div class="text-muted">
                            Define el logo, la marca visible, el servicio, los horarios y los colores de tu sitio.
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($tenantPublicUrl !== '') : ?>
                            <a href="<?= esc($tenantPublicUrl) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="fa-solid fa-globe me-1"></i> Abrir web publica
                            </a>
                            <a href="<?= esc(rtrim($tenantPublicUrl, '/') . $adminSuffix) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fa-solid fa-sliders me-1"></i> Abrir admin
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">Logo del sitio</label>
                        <div class="cp-setup-logo-box mb-2" id="cp_setup_logo_preview_wrap">
                            <?php if ($logoUrl !== '') : ?>
                                <img id="cp_setup_logo_preview" src="<?= esc($logoUrl) ?>" alt="Logo cliente" style="max-height:80px;max-width:100%;">
                            <?php else : ?>
                                <div class="text-muted">Sin logo cargado</div>
                            <?php endif; ?>
                        </div>
                        <input type="file" class="form-control mb-2" id="cp_setup_logo_file" accept=".png,.jpg,.jpeg,.webp">
                        <button type="button" class="btn btn-outline-primary w-100" id="saveClientSetupLogoBtn">
                            <i class="fa-solid fa-image me-1"></i> Guardar logo
                        </button>
                    </div>
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="cp_site_title">Nombre del sitio</label>
                                <input type="text" class="form-control" id="cp_site_title" value="<?= esc($setupSiteTitle) ?>" placeholder="Ej. Turnok Centro">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="cp_service_name">Nombre del servicio</label>
                                <input type="text" class="form-control" id="cp_service_name" value="<?= esc($setupServiceName) ?>" placeholder="Ej. Reservas de cancha">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="cp_reservation_email">Email para reservas</label>
                                <input type="email" class="form-control" id="cp_reservation_email" value="<?= esc($setupReservationEmail) ?>" placeholder="Se toma por default el email registrado">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dias de atencion</label>
                                <div class="cp-setup-days">
                                    <?php foreach ($setupDaysLabels as $dayValue => $dayLabel) : ?>
                                        <?php $dayInputId = 'cp_open_day_' . $dayValue; ?>
                                        <div class="cp-setup-day">
                                            <input type="checkbox" id="<?= esc($dayInputId) ?>" name="cp_open_days" value="<?= esc($dayValue) ?>" <?= in_array((string) $dayValue, $setupOpenDays, true) ? 'checked' : '' ?>>
                                            <label for="<?= esc($dayInputId) ?>"><span><?= esc($dayLabel) ?></span></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="cp_open_from">Desde</label>
                                <select class="form-select" id="cp_open_from">
                                    <?php foreach ($setupHourOptions as $hourOption) : ?>
                                        <option value="<?= esc($hourOption) ?>" <?= $setupOpenFrom === $hourOption ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="cp_open_until">Hasta</label>
                                <select class="form-select" id="cp_open_until">
                                    <?php foreach ($setupHourOptions as $hourOption) : ?>
                                        <option value="<?= esc($hourOption) ?>" <?= $setupOpenUntil === $hourOption ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="cp-advanced-schedule">
                                    <button type="button" class="cp-advanced-toggle-btn" data-advanced-toggle aria-expanded="false">
                                        <span>Horarios avanzados</span>
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                    <div class="cp-advanced-body" data-advanced-body>
                                        <div class="cp-advanced-toolbar">
                                            <div class="cp-advanced-global">
                                                <label class="fw-semibold small mb-2 d-flex align-items-center gap-2">
                                                    <input type="checkbox" class="form-check-input m-0" data-advanced-global="morning-enabled-all" checked>
                                                    <span>Turno mañana general</span>
                                                </label>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">Desde</label>
                                                        <select class="form-select form-select-sm" data-advanced-global="morning-from">
                                                            <?php foreach ($setupHourOptions as $hourOption) : ?>
                                                                <option value="<?= esc($hourOption) ?>" <?= $setupOpenFrom === $hourOption ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">Hasta</label>
                                                        <select class="form-select form-select-sm" data-advanced-global="morning-until">
                                                            <?php foreach ($setupHourOptions as $hourOption) : ?>
                                                                <option value="<?= esc($hourOption) ?>" <?= (((int) $setupOpenUntil <= 14 ? $setupOpenUntil : '13') === $hourOption) ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cp-advanced-global">
                                                <label class="fw-semibold small mb-2 d-flex align-items-center gap-2">
                                                    <input type="checkbox" class="form-check-input m-0" data-advanced-global="afternoon-enabled-all" checked>
                                                    <span>Turno tarde general</span>
                                                </label>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">Desde</label>
                                                        <select class="form-select form-select-sm" data-advanced-global="afternoon-from">
                                                            <?php foreach ($setupHourOptions as $hourOption) : ?>
                                                                <option value="<?= esc($hourOption) ?>" <?= ((((int) $setupOpenFrom >= 14 ? $setupOpenFrom : '14')) === $hourOption) ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">Hasta</label>
                                                        <select class="form-select form-select-sm" data-advanced-global="afternoon-until">
                                                            <?php foreach ($setupHourOptions as $hourOption) : ?>
                                                                <option value="<?= esc($hourOption) ?>" <?= $setupOpenUntil === $hourOption ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="cp-advanced-actions">
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-advanced-action="check-all">Tildar todo</button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-advanced-action="uncheck-all">Destildar todo</button>
                                            </div>
                                        </div>
                                        <div class="small text-muted mb-3">
                                            Configura por día si abre mañana, tarde o ambos turnos. También puede quedar un día solo de mañana.
                                        </div>
                                        <?php foreach ($setupDaysLabels as $dayValue => $dayLabel) : ?>
                                            <?php $dayCfg = $setupAdvancedSchedule[(string) $dayValue] ?? []; ?>
                                            <div class="cp-advanced-row" data-advanced-day="<?= esc((string) $dayValue) ?>">
                                                <div class="cp-advanced-day">
                                                    <div><?= esc($dayLabel) ?></div>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input cp-advanced-active" type="checkbox" id="cp_adv_active_<?= esc((string) $dayValue) ?>" <?= !empty($dayCfg['active']) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="cp_adv_active_<?= esc((string) $dayValue) ?>">Abre</label>
                                                    </div>
                                                </div>
                                                <div class="cp-advanced-slot<?= !empty($dayCfg['active']) && !empty($dayCfg['morning_enabled']) ? '' : ' disabled' ?>" data-slot="morning">
                                                    <label class="cp-advanced-toggle">
                                                        <input type="checkbox" class="form-check-input cp-advanced-morning-enabled" <?= !empty($dayCfg['active']) && !empty($dayCfg['morning_enabled']) ? 'checked' : '' ?>>
                                                        <span>Turno mañana</span>
                                                    </label>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label small mb-1">Desde</label>
                                                            <select class="form-select form-select-sm cp-advanced-morning-from">
                                                                <?php foreach ($setupHourOptions as $hourOption) : ?>
                                                                    <option value="<?= esc($hourOption) ?>" <?= (string) ($dayCfg['morning_from'] ?? '') === $hourOption ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small mb-1">Hasta</label>
                                                            <select class="form-select form-select-sm cp-advanced-morning-until">
                                                                <?php foreach ($setupHourOptions as $hourOption) : ?>
                                                                    <option value="<?= esc($hourOption) ?>" <?= (string) ($dayCfg['morning_until'] ?? '') === $hourOption ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="cp-advanced-slot<?= !empty($dayCfg['active']) && !empty($dayCfg['afternoon_enabled']) ? '' : ' disabled' ?>" data-slot="afternoon">
                                                    <label class="cp-advanced-toggle">
                                                        <input type="checkbox" class="form-check-input cp-advanced-afternoon-enabled" <?= !empty($dayCfg['active']) && !empty($dayCfg['afternoon_enabled']) ? 'checked' : '' ?>>
                                                        <span>Turno tarde</span>
                                                    </label>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label small mb-1">Desde</label>
                                                            <select class="form-select form-select-sm cp-advanced-afternoon-from">
                                                                <?php foreach ($setupHourOptions as $hourOption) : ?>
                                                                    <option value="<?= esc($hourOption) ?>" <?= (string) ($dayCfg['afternoon_from'] ?? '') === $hourOption ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small mb-1">Hasta</label>
                                                            <select class="form-select form-select-sm cp-advanced-afternoon-until">
                                                                <?php foreach ($setupHourOptions as $hourOption) : ?>
                                                                    <option value="<?= esc($hourOption) ?>" <?= (string) ($dayCfg['afternoon_until'] ?? '') === $hourOption ? 'selected' : '' ?>><?= esc($hourOption) ?>:00</option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color principal</label>
                                <div class="cp-color-chip">
                                    <input type="color" id="cp_primary_color" value="<?= esc($setupPrimaryColor) ?>">
                                    <div>
                                        <div class="fw-semibold">Base del sitio</div>
                                        <div class="small text-muted">Cabecera, botones y acciones</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color acento</label>
                                <div class="cp-color-chip">
                                    <input type="color" id="cp_accent_color" value="<?= esc($setupAccentColor) ?>">
                                    <div>
                                        <div class="fw-semibold">Destacados</div>
                                        <div class="small text-muted">Pills, avisos y detalles visuales</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="cp-setup-note d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div>
                                        <div class="fw-semibold mb-1">Recordatorio de cobro online</div>
                                        <div class="small">Recuerda configurar Mercado Pago para cobrar reservas desde tu sitio.</div>
                                    </div>
                                    <a href="<?= base_url('configMpView') ?>" class="btn btn-warning btn-sm">
                                        <i class="fa-solid fa-credit-card me-1"></i> Configurar Mercado Pago
                                    </a>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-success" id="saveClientSetupBtn">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar configuracion
                                </button>
                                <?php if ($openSetup) : ?>
                                    <span class="small text-muted ms-2">Completa estos datos para terminar de preparar tu sitio.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h6 class="mb-2">Configuración web</h6>
            <div class="border rounded p-3 mb-3">
                <div class="fw-semibold mb-2">Configuración global de la cuenta</div>
                <div class="text-muted mb-3">
                    Esta sección queda para parámetros globales por cliente. La operación diaria de la web
                    (catálogo, horarios, turnos, reglas de reserva) se gestiona desde el admin de la propia web.
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($tenantPublicUrl !== '') : ?>
                        <a href="<?= esc($tenantPublicUrl) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fa-solid fa-globe me-1"></i> Abrir web pública
                        </a>
                        <a href="<?= esc(rtrim($tenantPublicUrl, '/') . $adminSuffix) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-sliders me-1"></i> Abrir admin de la web
                        </a>
                    <?php else : ?>
                        <div class="text-muted small">No hay link configurado para abrir la web del cliente.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="cp_general_block">
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
                <div class="row g-2 cp-plan-current">
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
                <div class="mb-2">
                    <strong>Usuario:</strong> <?= esc((string) ($accessUser['user'] ?? '-')) ?>
                    &nbsp; | &nbsp;
                    <strong>Email:</strong> <?= esc((string) ($accessUser['email'] ?? '-')) ?>
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

            <div id="cp_users_block" style="display:none;">
                <div class="mb-3">
                    <h6 class="mb-2">Acceso de la cuenta (TURNOK)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= esc((string) ($accessUser['user'] ?? '-')) ?></td>
                                    <td><?= esc((string) ($accessUser['email'] ?? '-')) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Usuarios de esta web</h6>
                    <button type="button" class="btn btn-success btn-sm" id="cp_add_user_btn" data-bs-toggle="modal" data-bs-target="#clientProfileNewUserModal">
                        <i class="fa-solid fa-user-plus me-1"></i> Agregar usuario
                    </button>
                </div>
                <div class="small mb-2" id="cp_users_quota_text"
                    data-total="<?= esc((string) $usersQuotaTotal) ?>"
                    data-used="<?= esc((string) $usersQuotaUsed) ?>"
                    data-remaining="<?= esc((string) $usersQuotaRemaining) ?>">
                    Te quedan <strong><?= esc((string) $usersQuotaRemaining) ?></strong> usuario(s) disponibles (usados: <?= esc((string) $usersQuotaUsed) ?> / <?= esc((string) $usersQuotaTotal) ?>).
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
            </div>
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
                    <label for="cp_new_email" class="form-label">Email (opcional)</label>
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
