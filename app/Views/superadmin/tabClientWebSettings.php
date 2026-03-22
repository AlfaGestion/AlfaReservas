<?php
$profile = $clientProfile ?? [];
$plan = $currentPlan ?? [];
$planes = $planes ?? [];
$logoUrl = trim((string) ($profile['logo_url'] ?? ''));
$accessUser = $clientAccessUser ?? [];
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
$setupDaysLabels = ['1' => 'Lun', '2' => 'Mar', '3' => 'Mie', '4' => 'Jue', '5' => 'Vie', '6' => 'Sab', '0' => 'Dom'];
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
            .cp-websetup-frame {
                border: 1px solid rgba(122, 183, 231, .35);
                border-radius: 18px;
                padding: 18px;
                margin-bottom: 22px;
                background: linear-gradient(180deg, rgba(22, 94, 204, .08), rgba(255,255,255,.92));
            }
            .cp-websetup-kicker {
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
            .cp-websetup-kicker::before {
                content: "";
                width: 9px;
                height: 9px;
                border-radius: 50%;
                background: #e3f50d;
                box-shadow: 0 0 0 4px rgba(227, 245, 13, .12);
            }
            .cp-websetup-days {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .cp-websetup-day {
                position: relative;
            }
            .cp-websetup-day label {
                display: block;
                margin: 0;
            }
            .cp-websetup-day input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }
            .cp-websetup-day span {
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
            .cp-websetup-day input:checked + label span {
                background: #165ecc;
                border-color: #165ecc;
                color: #fff;
            }
            .cp-websetup-color {
                display: flex;
                align-items: center;
                gap: 12px;
                border: 1px solid rgba(122, 183, 231, .35);
                border-radius: 14px;
                padding: 10px 12px;
                background: rgba(255,255,255,.68);
            }
            .cp-websetup-color input[type="color"] {
                width: 52px;
                height: 42px;
                border: none;
                background: transparent;
                padding: 0;
            }
            .cp-websetup-note {
                border-radius: 14px;
                border: 1px solid rgba(255, 160, 66, .35);
                background: rgba(255, 160, 66, .08);
                padding: 14px 16px;
            }
            body.theme-dark .cp-websetup-frame {
                background: linear-gradient(180deg, rgba(20, 51, 84, .96), rgba(13, 31, 49, .92));
                border-color: rgba(122, 183, 231, .28);
                color: #e9f4ff;
            }
            body.theme-dark .cp-websetup-kicker {
                background: rgba(77, 122, 180, .18);
                border-color: rgba(122, 183, 231, .28);
                color: #dbefff;
            }
            body.theme-dark .cp-websetup-day span,
            body.theme-dark .cp-websetup-color {
                background: rgba(24, 51, 80, .74);
                border-color: rgba(122, 183, 231, .28);
                color: #dbefff;
            }
            body.theme-dark .cp-websetup-note {
                background: rgba(255, 160, 66, .12);
                border-color: rgba(255, 160, 66, .28);
                color: #ffe4c3;
            }
            .cp-websetup-advanced {
                border: 1px solid rgba(122, 183, 231, .28);
                border-radius: 16px;
                padding: 14px;
                background: rgba(255,255,255,.45);
            }
            .cp-websetup-advanced-toggle-btn {
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
            .cp-websetup-advanced-toggle-btn .fa-chevron-down {
                transition: transform .18s ease;
            }
            .cp-websetup-advanced-toggle-btn[aria-expanded="true"] .fa-chevron-down {
                transform: rotate(180deg);
            }
            .cp-websetup-advanced-body {
                display: none;
                margin-top: 12px;
            }
            .cp-websetup-advanced-body.is-open {
                display: block;
            }
            .cp-websetup-advanced-toolbar {
                display: grid;
                grid-template-columns: 1fr 1fr auto;
                gap: 12px;
                margin-bottom: 14px;
                align-items: end;
            }
            .cp-websetup-advanced-global {
                border: 1px solid rgba(122, 183, 231, .25);
                border-radius: 14px;
                padding: 10px 12px;
                background: rgba(255,255,255,.58);
            }
            .cp-websetup-advanced-actions {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .cp-websetup-advanced-row {
                display: grid;
                grid-template-columns: 90px 1fr 1fr;
                gap: 12px;
                align-items: start;
                padding: 12px 0;
                border-top: 1px solid rgba(122, 183, 231, .18);
            }
            .cp-websetup-advanced-row:first-child {
                border-top: none;
                padding-top: 0;
            }
            .cp-websetup-advanced-day {
                font-weight: 700;
                color: #1f4467;
                padding-top: 8px;
            }
            .cp-websetup-advanced-slot {
                border: 1px solid rgba(122, 183, 231, .25);
                border-radius: 14px;
                padding: 10px;
                background: rgba(255,255,255,.62);
            }
            .cp-websetup-advanced-slot.disabled {
                opacity: .55;
            }
            .cp-websetup-advanced-toggle {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 10px;
                font-weight: 600;
            }
            body.theme-dark .cp-websetup-advanced,
            body.theme-dark .cp-websetup-advanced-slot {
                background: rgba(24, 51, 80, .62);
                border-color: rgba(122, 183, 231, .2);
            }
            body.theme-dark .cp-websetup-advanced-global {
                background: rgba(24, 51, 80, .74);
                border-color: rgba(122, 183, 231, .2);
            }
            body.theme-dark .cp-websetup-advanced-toggle-btn {
                background: rgba(24, 51, 80, .74);
                border-color: rgba(122, 183, 231, .2);
                color: #dbefff;
            }
            body.theme-dark .cp-websetup-advanced-day {
                color: #dbefff;
            }
            @media (max-width: 991px) {
                .cp-websetup-advanced-row {
                    grid-template-columns: 1fr;
                }
                .cp-websetup-advanced-toolbar {
                    grid-template-columns: 1fr;
                }
                .cp-websetup-advanced-day {
                    padding-top: 0;
                }
            }
        </style>
        <h5 class="mb-3">Configurar web</h5>

        <div class="cp-websetup-frame">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="cp-websetup-kicker mb-2">Configuracion inicial</div>
                    <h6 class="mb-1">Configura tu sitio</h6>
                    <div class="text-muted">Completa la marca, el servicio, el email de reservas, los dias y los colores del sitio.</div>
                </div>
                <?php if ($openSetup) : ?>
                    <div class="small text-muted">Estas viendo este paso porque acabas de dar de alta tu cuenta.</div>
                <?php endif; ?>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="cp_site_title">Nombre del sitio</label>
                    <input type="text" class="form-control" id="cp_site_title" value="<?= esc($setupSiteTitle) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="cp_service_name">Nombre del servicio</label>
                    <input type="text" class="form-control" id="cp_service_name" value="<?= esc($setupServiceName) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="cp_reservation_email">Email para reservas</label>
                    <input type="email" class="form-control" id="cp_reservation_email" value="<?= esc($setupReservationEmail) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Dias de atencion</label>
                    <div class="cp-websetup-days">
                        <?php foreach ($setupDaysLabels as $dayValue => $dayLabel) : ?>
                            <?php $dayInputId = 'cp_web_open_day_' . $dayValue; ?>
                            <div class="cp-websetup-day">
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
                    <div class="cp-websetup-advanced">
                        <button type="button" class="cp-websetup-advanced-toggle-btn" data-advanced-toggle aria-expanded="false">
                            <span>Horarios avanzados</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="cp-websetup-advanced-body" data-advanced-body>
                            <div class="cp-websetup-advanced-toolbar">
                                <div class="cp-websetup-advanced-global">
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
                                <div class="cp-websetup-advanced-global">
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
                                <div class="cp-websetup-advanced-actions">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-advanced-action="check-all">Tildar todo</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-advanced-action="uncheck-all">Destildar todo</button>
                                </div>
                            </div>
                            <div class="small text-muted mb-3">
                                Configura por día si trabaja turno mañana, tarde o ambos. Un día puede quedar solo de mañana.
                            </div>
                            <?php foreach ($setupDaysLabels as $dayValue => $dayLabel) : ?>
                                <?php $dayCfg = $setupAdvancedSchedule[(string) $dayValue] ?? []; ?>
                                <div class="cp-websetup-advanced-row" data-advanced-day="<?= esc((string) $dayValue) ?>">
                                    <div class="cp-websetup-advanced-day">
                                        <div><?= esc($dayLabel) ?></div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input cp-advanced-active" type="checkbox" id="cp_web_adv_active_<?= esc((string) $dayValue) ?>" <?= !empty($dayCfg['active']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="cp_web_adv_active_<?= esc((string) $dayValue) ?>">Abre</label>
                                        </div>
                                    </div>
                                    <div class="cp-websetup-advanced-slot<?= !empty($dayCfg['active']) && !empty($dayCfg['morning_enabled']) ? '' : ' disabled' ?>" data-slot="morning">
                                        <label class="cp-websetup-advanced-toggle">
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
                                    <div class="cp-websetup-advanced-slot<?= !empty($dayCfg['active']) && !empty($dayCfg['afternoon_enabled']) ? '' : ' disabled' ?>" data-slot="afternoon">
                                        <label class="cp-websetup-advanced-toggle">
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
                    <div class="cp-websetup-color">
                        <input type="color" id="cp_primary_color" value="<?= esc($setupPrimaryColor) ?>">
                        <div>
                            <div class="fw-semibold">Base del sitio</div>
                            <div class="small text-muted">Cabecera, botones y acciones</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Color acento</label>
                    <div class="cp-websetup-color">
                        <input type="color" id="cp_accent_color" value="<?= esc($setupAccentColor) ?>">
                        <div>
                            <div class="fw-semibold">Destacados</div>
                            <div class="small text-muted">Pills, badges y avisos</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="cp-websetup-note d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-semibold mb-1">Recordatorio</div>
                            <div class="small">Recuerda configurar Mercado Pago para cobrar reservas online.</div>
                        </div>
                        <a href="<?= base_url('configMpView') ?>" class="btn btn-warning btn-sm">
                            <i class="fa-solid fa-credit-card me-1"></i> Configurar Mercado Pago
                        </a>
                    </div>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-success" id="saveClientSetupBtn">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar configuracion del sitio
                    </button>
                </div>
            </div>
        </div>

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
