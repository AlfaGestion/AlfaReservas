<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-base-url" content="<?= env('app.apiBaseURL', base_url()) ?>">
    <meta name="app-web-base-url" content="<?= base_url() ?>">
    <?php echo $this->renderSection('title') ?>
    <title>Home</title>

    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script> -->
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/styles.css") ?>">
    <script src="https://kit.fontawesome.com/9bae38f407.js" crossorigin="anonymous"></script>

</head>

<body>
    <?php echo $this->renderSection('navbar') ?>
    <?php
        $tenantLogoUrl = trim((string) (session()->get('tenant_logo_url') ?? ''));
        if ($tenantLogoUrl === '') {
            $cuentaCode = trim((string) (session()->get('cuenta') ?? ''));
            if ($cuentaCode !== '' && preg_match('/^[A-Za-z0-9_]+$/', $cuentaCode) === 1) {
                $paths = [
                    rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . $cuentaCode . DIRECTORY_SEPARATOR,
                    rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . $cuentaCode . DIRECTORY_SEPARATOR,
                ];
                foreach ($paths as $p) {
                    if (!is_dir($p)) {
                        continue;
                    }
                    $matches = glob($p . '{logo,LOGO}.*', GLOB_BRACE) ?: [];
                    foreach ($matches as $m) {
                        if (!is_file($m)) {
                            continue;
                        }
                        $ext = strtolower((string) pathinfo($m, PATHINFO_EXTENSION));
                        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                            continue;
                        }
                        $tenantLogoUrl = base_url(str_replace('\\', '/', trim(str_replace(rtrim(FCPATH, '/\\'), '', $m), '/\\'))) . '?v=' . (@filemtime($m) ?: time());
                        break 2;
                    }
                }
            }
        }
        $navbarLogo = $tenantLogoUrl !== '' ? $tenantLogoUrl : base_url('alfa.png');
    ?>
    <nav class="navbar navbar-expand-lg" style="background-color: #ffffff;">
        <div class="container-fluid d-flex justify-content-center align-items-center flex-row">
            <div class="d-flex justify-content-center align-items-center flex-row">
                
                <div class="mx-auto d-lg-none"> <!-- Centra en dispositivos moviles -->
                    <a class="navbar-brand" href="<?= base_url() ?>">
                        <img src="<?= esc($navbarLogo) ?>" width="84" alt="Logo">
                    </a>
                </div>

                <div class="mx-auto d-none d-lg-block"> <!-- Centra en pantalla grande -->
                    <a class="navbar-brand" href="<?= base_url() ?>">
                        <img src="<?= esc($navbarLogo) ?>" width="110" alt="Logo">
                    </a>
                </div>

                <?php if (session()->logueado) : ?>
                    <span class="me-1"><?= session()->name ?></span>
                    <a href="<?= base_url('auth/logOut') ?>" class="btn btn-danger me-1" type="button" id=""><i class="fa-solid fa-plug-circle-xmark"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>


    <?php echo $this->renderSection('content') ?>


    <?php echo $this->renderSection('footer') ?>

    <div class="container-fluid">
        <footer class="my-4" style="background-color: #5a5a5a;">
            <?php if (session()->logueado) : ?>
                <ul class="nav justify-content-center border-bottom pb-3 mb-3">
                    <?php $panelPath = trim((string) (session()->get('admin_panel_path') ?? '/abmAdmin')); ?>
                    <li class="nav-item"><a href="<?= base_url('auth/logOut') ?>" class="nav-link px-2 text-muted">Cerrar sesiÃ³n</a></li>
                    <li class="nav-item"><a href="<?= base_url(ltrim($panelPath, '/')) ?>" class="nav-link px-2 text-muted">Panel</a></li>
                </ul>
            <?php else : ?>
                <ul class="nav justify-content-center border-bottom pb-3 mb-3">
                    <li class="nav-item"><a href="<?= base_url('auth/login') ?>" class="nav-link px-2 text-muted">Ingreso Admin</a></li>
                    <li class="nav-item"><a class="nav-link px-2 text-muted">-</a></li>
                    <li class="nav-item"><a href="<?= base_url('customers/register') ?>" class="nav-link px-2 text-muted">Registro Clientes</a></li>
                </ul>
            <?php endif; ?>

            <div class="link d-flex justify-content-center align-items-center">
                <a href="https://alfagestion.com.ar/" target="_blank" class="text-center text-muted">Â© 2023 - Alfanet</a>
            </div>
        </footer>
    </div>

    <?php echo $this->renderSection('scripts') ?>

    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/config.js?v=" . time()) ?>"></script>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
    <script>
        let sessionUserId = <?= json_encode(session()->id_user) ?>;
        let sessionUserLogued = <?= json_encode(session()->logueado) ?>;
        let sessionUserSuperadmin = <?= json_encode(session()->superadmin) ?>;
    </script>
</body>

</html>

