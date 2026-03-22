<?php echo $this->extend('templates/dashboard_panel') ?>

<?php echo $this->section('title') ?>
<title>Panel</title>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>

<style>
    .superadmin-reservas .card,
    .superadmin-reservas .tab-card {
        background: #ffffff;
        border: 1px solid #d8e6f4;
        border-radius: 12px;
        color: #17324d;
    }
    .superadmin-reservas .tab-card {
        padding: 14px;
        margin-top: 12px;
    }
    .superadmin-reservas .nav-tabs {
        border-bottom-color: #cfe0f1;
    }
    .superadmin-reservas .nav-tabs .nav-link {
        color: #2a5378;
        border-color: transparent transparent #cfe0f1 transparent;
    }
    .superadmin-reservas .nav-tabs .nav-link.active {
        color: #0b63b6;
        background: #ffffff;
        border-color: #cfe0f1 #cfe0f1 #ffffff #cfe0f1;
        font-weight: 600;
    }
    .superadmin-reservas .general-subtabs {
        gap: 10px;
    }
    .superadmin-reservas .general-subtabs .nav-link {
        border: 1px solid #d8e6f4;
        border-radius: 999px;
        background: #f5f9fd;
        color: #2a5378;
        font-weight: 600;
        padding: 8px 14px;
    }
    .superadmin-reservas .general-subtabs .nav-link.active {
        background: #165ecc;
        border-color: #165ecc;
        color: #ffffff;
    }
    .superadmin-reservas .general-subpane {
        margin-top: 14px;
    }
    .superadmin-reservas .table {
        --bs-table-bg: #ffffff;
        --bs-table-striped-bg: #f5f9fd;
        --bs-table-striped-color: #17324d;
        --bs-table-color: #17324d;
        --bs-table-border-color: #d8e6f4;
    }
    .superadmin-reservas .table thead th {
        color: #1f4467;
    }
    .superadmin-reservas .table td,
    .superadmin-reservas .table th {
        vertical-align: middle;
    }
    .superadmin-reservas .table .btn-outline-primary {
        color: #165ecc;
        border-color: #9fc0df;
    }
    .superadmin-reservas .table .btn-outline-primary:hover {
        color: #ffffff;
        background: #165ecc;
        border-color: #165ecc;
    }
    .superadmin-reservas .table .btn-outline-danger {
        color: #bb2d3b;
        border-color: #e4a8af;
    }
    .superadmin-reservas .table .btn-outline-danger:hover {
        color: #ffffff;
        background: #bb2d3b;
        border-color: #bb2d3b;
    }
    .superadmin-reservas .modal-content {
        background: #ffffff;
        border: 1px solid #d8e6f4;
        color: #17324d;
    }
    .superadmin-reservas .modal-header,
    .superadmin-reservas .modal-footer {
        border-color: #d8e6f4;
    }
    .superadmin-reservas .modal .form-label {
        color: #2a5378;
        font-weight: 600;
    }
    .superadmin-reservas .modal .form-control {
        background: #ffffff;
        border-color: #cfe0f1;
        color: #17324d;
    }
    .superadmin-reservas .modal .form-control:focus {
        border-color: #165ecc;
        box-shadow: 0 0 0 0.2rem rgba(22, 94, 204, 0.15);
    }
    .superadmin-reservas .btn {
        border-radius: 10px;
    }

    body.theme-dark .superadmin-reservas .card,
    body.theme-dark .superadmin-reservas .tab-card {
        background: #182d42;
        border-color: #33506e;
        color: #dbe9f8;
    }
    body.theme-dark .superadmin-reservas .nav-tabs {
        border-bottom-color: #345672;
    }
    body.theme-dark .superadmin-reservas .nav-tabs .nav-link {
        color: #b7d4ee;
        border-color: transparent transparent #345672 transparent;
    }
    body.theme-dark .superadmin-reservas .nav-tabs .nav-link.active {
        color: #dff0ff;
        background: #182d42;
        border-color: #345672 #345672 #182d42 #345672;
    }
    body.theme-dark .superadmin-reservas .general-subtabs .nav-link {
        background: #21374d;
        border-color: #33506e;
        color: #c7dff5;
    }
    body.theme-dark .superadmin-reservas .general-subtabs .nav-link.active {
        background: #165ecc;
        border-color: #165ecc;
        color: #ffffff;
    }
    body.theme-dark .superadmin-reservas .table,
    body.theme-dark .superadmin-reservas .table thead th,
    body.theme-dark .superadmin-reservas .table tbody td {
        color: #dbe9f8;
    }
    body.theme-dark .superadmin-reservas .table {
        --bs-table-bg: #182d42;
        --bs-table-striped-bg: #21374d;
        --bs-table-striped-color: #dbe9f8;
        --bs-table-color: #dbe9f8;
        --bs-table-border-color: #33506e;
    }
    body.theme-dark .superadmin-reservas .table .btn-outline-primary {
        color: #c7dff5;
        border-color: #4b77a0;
    }
    body.theme-dark .superadmin-reservas .table .btn-outline-primary:hover {
        color: #ffffff;
        background: #165ecc;
        border-color: #165ecc;
    }
    body.theme-dark .superadmin-reservas .table .btn-outline-danger {
        color: #f1b5bb;
        border-color: #8d4b56;
    }
    body.theme-dark .superadmin-reservas .table .btn-outline-danger:hover {
        color: #ffffff;
        background: #bb2d3b;
        border-color: #bb2d3b;
    }
    body.theme-dark .superadmin-reservas .modal-content {
        background: #182d42;
        border-color: #33506e;
        color: #dbe9f8;
    }
    body.theme-dark .superadmin-reservas .modal-header,
    body.theme-dark .superadmin-reservas .modal-footer {
        border-color: #33506e;
    }
    body.theme-dark .superadmin-reservas .modal .form-label {
        color: #c7dff5;
    }
    body.theme-dark .superadmin-reservas .modal .form-control {
        background: #21374d;
        border-color: #33506e;
        color: #dbe9f8;
    }
    body.theme-dark .superadmin-reservas .modal .form-control:focus {
        border-color: #78b7f0;
        box-shadow: 0 0 0 0.2rem rgba(120, 183, 240, 0.16);
    }
    body.theme-dark .superadmin-reservas .text-muted,
    body.theme-dark .superadmin-reservas .small.text-muted {
        color: #9fc0df !important;
    }
</style>

<?php if (session('msg')) : ?>
    <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
        <small> <?= session('msg.body') ?> </small>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="container superadmin-reservas">
    <div class="row">
        <div class="col-12">
            <nav>
                <div class="nav nav-tabs mt-3" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-bookings-tab" data-bs-toggle="tab" data-bs-target="#nav-bookings" type="button" role="tab" aria-controls="nav-bookings" aria-selected="true"><i class="fa-regular fa-calendar-days"></i> Reservas</button>
                    <button class="nav-link" id="nav-general-tab" data-bs-toggle="tab" data-bs-target="#nav-general" type="button" role="tab" aria-controls="nav-general" aria-selected="false"><i class="fa-solid fa-gear"></i> General</button>
                    <button class="nav-link" id="nav-reports-tab" data-bs-toggle="tab" data-bs-target="#nav-reports" type="button" role="tab" aria-controls="nav-reports" aria-selected="false"><i class="fa-solid fa-file-lines"></i> Reportes de cobro</button>

                    <?php if ($isClientScoped ?? false) : ?>
                        <button class="nav-link" id="nav-websettings-tab" data-bs-toggle="tab" data-bs-target="#nav-websettings" type="button" role="tab" aria-controls="nav-websettings" aria-selected="false"><i class="fa-solid fa-sliders"></i> Configurar web</button>
                    <?php endif; ?>
                    <?php if (session()->superadmin) : ?>
                        <button class="nav-link" id="nav-customers-tab" data-bs-toggle="tab" data-bs-target="#nav-customers" type="button" role="tab" aria-controls="nav-customers" aria-selected="false"><i class="fa-solid fa-user"></i> Clientes</button>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane active tab-card" id="nav-bookings" role="tabpanel" aria-labelledby="nav-bookings-tab" tabindex="0">
                    <?= view('superadmin/tabBookings', ['bookings' => $bookings ?? [], 'localities' => $localities ?? [], 'openingTime' => $openingTime ?? [], 'fields' => $fields ?? []]) ?>
                </div>

                <div class="tab-pane tab-card" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab" tabindex="0">
                    <?php if (session()->superadmin) : ?>
                        <ul class="nav nav-pills general-subtabs" id="general-subtabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-users-tab" data-bs-toggle="pill" data-bs-target="#general-users-pane" type="button" role="tab" aria-controls="general-users-pane" aria-selected="true">
                                    Usuarios
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="general-settings-tab" data-bs-toggle="pill" data-bs-target="#general-settings-pane" type="button" role="tab" aria-controls="general-settings-pane" aria-selected="false">
                                    Ajustes
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="general-fields-tab" data-bs-toggle="pill" data-bs-target="#general-fields-pane" type="button" role="tab" aria-controls="general-fields-pane" aria-selected="false">
                                    Canchas
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="general-time-tab" data-bs-toggle="pill" data-bs-target="#general-time-pane" type="button" role="tab" aria-controls="general-time-pane" aria-selected="false">
                                    Horarios
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active general-subpane" id="general-users-pane" role="tabpanel" aria-labelledby="general-users-tab">
                                <?= view('superadmin/tabUsers', ['users' => $users ?? [], 'usersFromTenant' => $usersFromTenant ?? false]) ?>
                            </div>
                            <div class="tab-pane fade general-subpane" id="general-settings-pane" role="tabpanel" aria-labelledby="general-settings-tab">
                                <?= view('superadmin/tabGeneral', ['users' => $users ?? [], 'usersFromTenant' => $usersFromTenant ?? false, 'fields' => $fields ?? [], 'rate' => $rate ?? [], 'offerRate' => $offerRate ?? [], 'closureText' => $closureText ?? '', 'bookingEmail' => $bookingEmail ?? '']) ?>
                            </div>
                            <div class="tab-pane fade general-subpane" id="general-fields-pane" role="tabpanel" aria-labelledby="general-fields-tab">
                                <?= view('superadmin/tabFields', ['fields' => $fields ?? []]) ?>
                            </div>
                            <div class="tab-pane fade general-subpane" id="general-time-pane" role="tabpanel" aria-labelledby="general-time-tab">
                                <?= view('superadmin/tabTime', ['time' => $time ?? []]) ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <?= view('superadmin/tabGeneral', ['users' => $users ?? [], 'usersFromTenant' => $usersFromTenant ?? false, 'fields' => $fields ?? [], 'rate' => $rate ?? [], 'offerRate' => $offerRate ?? [], 'closureText' => $closureText ?? '', 'bookingEmail' => $bookingEmail ?? '']) ?>
                    <?php endif; ?>
                </div>

                <?php if ($isClientScoped ?? false) : ?>
                    <div class="tab-pane tab-card" id="nav-websettings" role="tabpanel" aria-labelledby="nav-websettings-tab" tabindex="0">
                        <?= view('superadmin/tabClientWebSettings', [
                            'clientProfile' => $clientProfile ?? null,
                            'clientUsers' => $clientUsers ?? [],
                            'currentPlan' => $currentPlan ?? null,
                            'planes' => $clientPlanOptions ?? [],
                            'clientAccessUser' => $clientAccessUser ?? null,
                            'clientSetupConfig' => $clientSetupConfig ?? [],
                            'openClientSetup' => $openClientSetup ?? false,
                        ]) ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->superadmin) : ?>
                    <div class="tab-pane tab-card" id="nav-customers" role="tabpanel" aria-labelledby="nav-customers-tab" tabindex="0">
                        <?= view('superadmin/tabCustomersLegacy') ?>
                    </div>
                <?php endif; ?>

                <div class="tab-pane tab-card" id="nav-reports" role="tabpanel" aria-labelledby="nav-reports-tab" tabindex="0">
                    <?= view('superadmin/tabReports', ['users' => $users ?? []]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="spinnerCompletarPago" tabindex="-1" data-bs-backdrop="static" aria-labelledby="spinnerCompletarPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered d-flex justify-content-center">
        <div class="d-flex justify-content-center align-items-center">
            <div class="spinner-border" style="width: 4rem; height: 4rem; color: #f39323" role="status">
                <span class="visually-hidden">Guardando pago...</span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalResultPayment" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalResultPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" id="paymentResult"></div>
    </div>
</div>

<div class="modal fade" id="generateReportModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="generateReportModalLabel">Resumen</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="paymentsMethodsResume"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" id="printReport">Imprimir</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalResult" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalResultLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" id="bookingEditResult"></div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('footer') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/abmSuperadmin.js?v=" . time()) ?>"></script>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/searchReports.js?v=" . time()) ?>"></script>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/searchBookings.js?v=" . time()) ?>"></script>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/customers.js?v=" . time()) ?>"></script>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/editReserva.js?v=" . time()) ?>"></script>
<?php if ($isClientScoped ?? false) : ?>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/superadminClientProfile.js?v=" . time()) ?>"></script>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const openClientSetupOnLoad = <?= !empty($openClientSetup) ? 'true' : 'false' ?>;
    const nav = document.getElementById('nav-tab');
    const content = document.getElementById('nav-tabContent');
    if (nav && content) {
        nav.addEventListener('click', function (e) {
            const btn = e.target.closest('button[data-bs-target]');
            if (!btn) return;
            const target = btn.getAttribute('data-bs-target');
            if (!target || !target.startsWith('#')) return;
            const pane = content.querySelector(target);
            if (!pane) return;

            nav.querySelectorAll(':scope > button.nav-link').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            Array.from(content.children).forEach(function (p) {
                if (p.classList.contains('tab-pane')) {
                    p.classList.remove('active', 'show');
                }
            });
            pane.classList.add('active', 'show');
        });
    }

    if (openClientSetupOnLoad) {
        const setupBtn = document.getElementById('nav-websettings-tab');
        const setupPane = document.getElementById('nav-websettings');
        if (nav) {
            nav.querySelectorAll(':scope > button.nav-link').forEach(function (b) { b.classList.remove('active'); });
        }
        if (content) {
            Array.from(content.children).forEach(function (p) {
                if (p.classList.contains('tab-pane')) {
                    p.classList.remove('active', 'show');
                }
            });
        }
        if (setupBtn) setupBtn.classList.add('active');
        if (setupPane) setupPane.classList.add('active', 'show');
    }

    document.addEventListener('click', async function (e) {
        const editFieldBtn = e.target.closest('.quick-edit-field');
        if (editFieldBtn) {
            const fieldId = editFieldBtn.dataset.id;
            const selectEditField = document.getElementById('selectEditField');
            const selectEditFields = document.getElementById('selectEditFields');
            const enterFields = document.getElementById('enterFields');
            if (selectEditField) selectEditField.classList.remove('d-none');
            if (enterFields) enterFields.classList.add('d-none');
            if (selectEditFields) {
                selectEditFields.value = fieldId;
            }
            if (typeof getEditField === 'function') {
                await getEditField(fieldId);
            }
            return;
        }

        const editUserBtn = e.target.closest('.edit-user-btn');
        if (!editUserBtn) return;

        const modalEl = document.getElementById('editUserModal');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const userId = editUserBtn.dataset.id;

        try {
            const response = await fetch(`${baseUrl}getUser/${userId}`);
            const result = await response.json();
            const user = result.data || {};
            document.getElementById('editUserId').value = user.id || '';
            document.getElementById('editUserName').value = user.user || '';
            document.getElementById('editUserDisplayName').value = user.name || '';
            document.getElementById('editUserEmail').value = user.email || '';
            document.getElementById('editUserCuenta').value = user.cuenta || '';
            document.getElementById('editUserPassword').value = '';
            document.getElementById('editUserSuperadmin').checked = Number(user.superadmin || 0) === 1;
            modal.show();
        } catch (error) {
            console.error(error);
            alert('No se pudo cargar el usuario.');
        }
    });

    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const payload = {
                id: document.getElementById('editUserId').value,
                user: document.getElementById('editUserName').value.trim(),
                name: document.getElementById('editUserDisplayName').value.trim(),
                email: document.getElementById('editUserEmail').value.trim(),
                cuenta: document.getElementById('editUserCuenta').value.trim(),
                password: document.getElementById('editUserPassword').value,
                superadmin: document.getElementById('editUserSuperadmin').checked,
            };

            try {
                const response = await fetch(`${baseUrl}editUser`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const result = await response.json();
                if (!response.ok || result.error) {
                    alert(result.message || 'No se pudo editar el usuario.');
                    return;
                }
                location.reload();
            } catch (error) {
                console.error(error);
                alert('No se pudo editar el usuario.');
            }
        });
    }
});
</script>
<?php echo $this->endSection() ?>
