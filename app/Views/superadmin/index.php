<?php echo $this->extend('templates/dashboard_panel') ?>

<?php echo $this->section('title') ?>
<title>Clientes</title>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>

<style>
    .superadmin-center .card {
        background: #ffffff;
        border: 1px solid #d8e6f4;
        color: #17324d;
    }
    .superadmin-center .card .text-muted {
        color: #5a7794 !important;
    }
    .superadmin-center .nav-tabs {
        border-bottom-color: #cfe0f1;
    }
    .superadmin-center .nav-tabs .nav-link {
        color: #2a5378;
        border-color: transparent transparent #cfe0f1 transparent;
    }
    .superadmin-center .nav-tabs .nav-link.active {
        color: #0b63b6;
        background: #ffffff;
        border-color: #cfe0f1 #cfe0f1 #ffffff #cfe0f1;
        font-weight: 600;
    }
    .superadmin-center .table {
        color: #17324d;
    }
    .superadmin-center .table thead th {
        color: #1f4467;
    }

    body.theme-dark .superadmin-center .card {
        background: #182d42;
        border-color: #33506e;
        color: #dbe9f8;
    }
    body.theme-dark .superadmin-center .card .text-muted {
        color: #9fc0df !important;
    }
    body.theme-dark .superadmin-center .nav-tabs {
        border-bottom-color: #345672;
    }
    body.theme-dark .superadmin-center .nav-tabs .nav-link {
        color: #b7d4ee;
        border-color: transparent transparent #345672 transparent;
    }
    body.theme-dark .superadmin-center .nav-tabs .nav-link.active {
        color: #dff0ff;
        background: #182d42;
        border-color: #345672 #345672 #182d42 #345672;
    }
    body.theme-dark .superadmin-center .table {
        color: #dbe9f8;
    }
    body.theme-dark .superadmin-center .table thead th {
        color: #c7e1f7;
    }
</style>

<?php if (session('msg')) : ?>
    <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
        <small> <?= session('msg.body') ?> </small>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="container superadmin-center">
    <div class="row">
        <div class="col-12">
            <nav>
                <div class="nav nav-tabs mt-3" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-overview-tab" data-bs-toggle="tab" data-bs-target="#nav-overview" type="button" role="tab" aria-controls="nav-overview" aria-selected="true">
                        <i class="fa-solid fa-chart-line"></i> Resumen
                    </button>
                    <button class="nav-link" id="nav-config-tab" data-bs-toggle="tab" data-bs-target="#nav-config" type="button" role="tab" aria-controls="nav-config" aria-selected="false">
                        <i class="fa-solid fa-gear"></i> Configuración
                    </button>
                    <button class="nav-link" id="nav-customers-tab" data-bs-toggle="tab" data-bs-target="#nav-customers" type="button" role="tab" aria-controls="nav-customers" aria-selected="false">
                        <i class="fa-solid fa-user-group"></i> Clientes
                    </button>
                    <button class="nav-link" id="nav-planes-tab" data-bs-toggle="tab" data-bs-target="#nav-planes" type="button" role="tab" aria-controls="nav-planes" aria-selected="false">
                        <i class="fa-solid fa-layer-group"></i> Planes
                    </button>
                    <button class="nav-link" id="nav-rubros-tab" data-bs-toggle="tab" data-bs-target="#nav-rubros" type="button" role="tab" aria-controls="nav-rubros" aria-selected="false">
                        <i class="fa-solid fa-tags"></i> Rubros
                    </button>
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-overview" role="tabpanel" aria-labelledby="nav-overview-tab" tabindex="0">
                    <?= view('superadmin/tabOverview', ['superadminStats' => $superadminStats ?? []]) ?>
                </div>
                <div class="tab-pane fade" id="nav-config" role="tabpanel" aria-labelledby="nav-config-tab" tabindex="0">
                    <?= view('superadmin/tabConfig', [
                        'bookingEmail' => $bookingEmail ?? '',
                        'clientes' => $clientes ?? [],
                    ]) ?>
                </div>
                <div class="tab-pane fade" id="nav-customers" role="tabpanel" aria-labelledby="nav-customers-tab" tabindex="0">
                    <?= view('superadmin/tabCustomers', ['clientes' => $clientes ?? [], 'rubros' => $rubros ?? [], 'nextClienteCodigo' => $nextClienteCodigo ?? '112010001']) ?>
                </div>
                <div class="tab-pane fade" id="nav-planes" role="tabpanel" aria-labelledby="nav-planes-tab" tabindex="0">
                    <?= view('superadmin/tabPlanes', ['planes' => $planes ?? []]) ?>
                </div>
                <div class="tab-pane fade" id="nav-rubros" role="tabpanel" aria-labelledby="nav-rubros-tab" tabindex="0">
                    <?= view('superadmin/tabRubrosV2', ['rubros' => $rubros ?? [], 'rubroParametros' => $rubroParametros ?? []]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('footer') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/superadminConfig.js?v=" . time()) ?>"></script>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/superadminMasterData.js?v=" . time()) ?>"></script>
<script src="<?= base_url(PUBLIC_FOLDER . "assets/js/superadminClientes.js?v=" . time()) ?>"></script>
<?php echo $this->endSection() ?>
