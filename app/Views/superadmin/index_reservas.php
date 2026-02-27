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
    .superadmin-reservas .table thead th {
        color: #1f4467;
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
    body.theme-dark .superadmin-reservas .table,
    body.theme-dark .superadmin-reservas .table thead th,
    body.theme-dark .superadmin-reservas .table tbody td {
        color: #dbe9f8;
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

                    <?php if (session()->superadmin) : ?>
                        <button class="nav-link" id="nav-websettings-tab" data-bs-toggle="tab" data-bs-target="#nav-websettings" type="button" role="tab" aria-controls="nav-websettings" aria-selected="false"><i class="fa-solid fa-sliders"></i> Configurar web</button>
                        <button class="nav-link" id="nav-fields-tab" data-bs-toggle="tab" data-bs-target="#nav-fields" type="button" role="tab" aria-controls="nav-fields" aria-selected="false"><i class="fa-solid fa-futbol"></i> Canchas</button>
                        <button class="nav-link" id="nav-time-tab" data-bs-toggle="tab" data-bs-target="#nav-time" type="button" role="tab" aria-controls="nav-time" aria-selected="false"><i class="fa-regular fa-clock"></i> Horarios</button>
                        <button class="nav-link" id="nav-customers-tab" data-bs-toggle="tab" data-bs-target="#nav-customers" type="button" role="tab" aria-controls="nav-customers" aria-selected="false"><i class="fa-solid fa-user"></i> Clientes</button>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane active tab-card" id="nav-bookings" role="tabpanel" aria-labelledby="nav-bookings-tab" tabindex="0">
                    <?= view('superadmin/tabBookings', ['bookings' => $bookings ?? [], 'localities' => $localities ?? [], 'openingTime' => $openingTime ?? [], 'fields' => $fields ?? []]) ?>
                </div>

                <div class="tab-pane tab-card" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab" tabindex="0">
                    <?= view('superadmin/tabGeneral', ['users' => $users ?? [], 'usersFromTenant' => $usersFromTenant ?? false, 'fields' => $fields ?? [], 'rate' => $rate ?? [], 'offerRate' => $offerRate ?? [], 'closureText' => $closureText ?? '', 'bookingEmail' => $bookingEmail ?? '']) ?>
                </div>

                <?php if (session()->superadmin) : ?>
                    <div class="tab-pane tab-card" id="nav-websettings" role="tabpanel" aria-labelledby="nav-websettings-tab" tabindex="0">
                        <?= view('superadmin/tabClientWebSettings', [
                            'clientProfile' => $clientProfile ?? null,
                            'clientUsers' => $clientUsers ?? [],
                            'currentPlan' => $currentPlan ?? null,
                            'planes' => $clientPlanOptions ?? [],
                            'clientAccessUser' => $clientAccessUser ?? null,
                        ]) ?>
                    </div>

                    <div class="tab-pane tab-card" id="nav-fields" role="tabpanel" aria-labelledby="nav-fields-tab" tabindex="0">
                        <?= view('superadmin/tabFields', ['fields' => $fields ?? []]) ?>
                    </div>

                    <div class="tab-pane tab-card" id="nav-time" role="tabpanel" aria-labelledby="nav-time-tab" tabindex="0">
                        <?= view('superadmin/tabTime', ['time' => $time ?? []]) ?>
                    </div>

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
<?php if (session()->superadmin) : ?>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/superadminClientProfile.js?v=" . time()) ?>"></script>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nav = document.getElementById('nav-tab');
    const content = document.getElementById('nav-tabContent');
    if (!nav || !content) return;
    nav.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-bs-target]');
        if (!btn) return;
        const target = btn.getAttribute('data-bs-target');
        if (!target || !target.startsWith('#')) return;
        const pane = document.querySelector(target);
        if (!pane) return;

        nav.querySelectorAll('button.nav-link').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        content.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.remove('active', 'show'); });
        pane.classList.add('active', 'show');
    });
});
</script>
<?php echo $this->endSection() ?>
