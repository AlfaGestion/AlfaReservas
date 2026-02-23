<?php echo $this->extend('templates/dashboard_panel') ?>

<?php echo $this->section('title') ?>
<title>Clientes</title>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>

<?php if (session('msg')) : ?>
    <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
        <small> <?= session('msg.body') ?> </small>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <nav>
                <div class="nav nav-tabs mt-3" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-customers-tab" data-bs-toggle="tab" data-bs-target="#nav-customers" type="button" role="tab" aria-controls="nav-customers" aria-selected="true">
                        <i class="fa-solid fa-user"></i> Clientes
                    </button>
                </div>
            </nav>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-customers" role="tabpanel" aria-labelledby="nav-customers-tab" tabindex="0">
                    <?= view('superadmin/tabCustomers', ['clientes' => $clientes ?? [], 'rubros' => $rubros ?? [], 'nextClienteCodigo' => $nextClienteCodigo ?? '112010001']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $this->endSection() ?>

<?php echo $this->section('footer') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('scripts') ?>
<?php echo $this->endSection() ?>
