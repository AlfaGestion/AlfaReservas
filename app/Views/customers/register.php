<?php
$formatRubroLabel = static function (?string $descripcion): string {
    $valor = strtolower(trim((string) $descripcion));
    return match ($valor) {
        'cancha', 'canchas' => '🏟 Canchas',
        'peluqueria', 'peluquería' => '💇 Peluquería',
        'consultorio', 'consultorios' => '🏥 Consultorio',
        'gimnasio', 'gimnasios' => '🏋 Gimnasio',
        'comida', 'restaurante', 'restaurantes', 'pedidos' => '🍽 Pedidos',
        default => trim((string) $descripcion) !== '' ? (string) $descripcion : 'Otro',
    };
};
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Alfa Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/9bae38f407.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <style>
        :root {
            --alfa-blue: #0b63b6;
            --alfa-sky: #dff1ff;
            --bg-main: #f3f9ff;
            --text-main: #1e3550;
            --card-bg: #ffffff;
            --card-border: #cfe6fb;
            --hero-bg-start: #eef7ff;
            --hero-bg-end: #dbedff;
            --form-border: #d3e4f5;
        }
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 18%, rgba(11,99,182,.20), transparent 28%),
                radial-gradient(circle at 88% 8%, rgba(93,188,255,.28), transparent 22%),
                var(--bg-main);
            color: var(--text-main);
        }
        body.theme-dark {
            --bg-main: #0f1f2f;
            --text-main: #dbe9f8;
            --card-bg: #182d42;
            --card-border: #2d4b67;
            --hero-bg-start: #16324a;
            --hero-bg-end: #1e3d58;
            --form-border: #325372;
        }
        .register-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 0;
        }
        .register-box {
            max-width: 980px;
            width: 100%;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(16,65,116,.14);
            padding: 2rem;
            position: relative;
        }
        .top-info {
            text-align: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #d8e9f8;
        }
        .top-info h2 {
            margin: 0;
            color: #0a4f90;
            font-weight: 700;
            letter-spacing: .5px;
        }
        .register-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .register-logo img {
            width: 132px;
            height: auto;
        }
        .register-body {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 20px;
        }
        .hero-panel {
            background: linear-gradient(155deg, var(--hero-bg-start) 0%, var(--hero-bg-end) 100%);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 1.4rem;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            text-align: left;
        }
        .hero-icon {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(9,132,227,.12);
            color: #0a74c2;
            font-size: 1.4rem;
            margin: .2rem auto .5rem;
        }
        .hero-panel h2 {
            color: #0a4f90;
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: .35rem;
        }
        .hero-panel p {
            color: #3d5f7e;
            margin-bottom: .75rem;
        }
        body.theme-dark .hero-panel h2,
        body.theme-dark .top-info h2,
        body.theme-dark .plan-title,
        body.theme-dark .price-line,
        body.theme-dark .btn-outline-main {
            color: #c7e4ff;
        }
        body.theme-dark .hero-panel p,
        body.theme-dark .plan-price,
        body.theme-dark .calc-label,
        body.theme-dark .small.text-muted {
            color: #a8c5e0 !important;
        }
        body.theme-dark .form-panel h3 {
            color: #e6f4ff !important;
        }
        .pricing-grid {
            display: grid;
            gap: 10px;
            margin-bottom: 12px;
        }
        .plan-card {
            border: 1px solid #b7d7f4;
            border-radius: 12px;
            background: #f8fcff;
            padding: 10px 12px;
            cursor: pointer;
            position: relative;
            transition: all .18s ease;
        }
        .plan-card.selected {
            border-color: #0b63b6;
            background: #e9f4ff;
            box-shadow: 0 0 0 2px rgba(11,99,182,.12);
        }
        .plan-title {
            font-weight: 700;
            color: #0a4f90;
        }
        .plan-price {
            color: #355c7f;
            font-size: .95rem;
        }
        .plan-check {
            position: absolute;
            right: 10px;
            top: 10px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 1px solid #8ec2ec;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            background: #fff;
        }
        .plan-card.selected .plan-check {
            background: #0b63b6;
            border-color: #0b63b6;
            color: #fff;
        }
        body.theme-dark .plan-card {
            background: #1c2f43;
            border-color: #3f6383;
        }
        body.theme-dark .plan-card.selected {
            background: #244767;
            border-color: #72a9d6;
            box-shadow: 0 0 0 2px rgba(114,169,214,.22);
        }
        body.theme-dark .plan-check {
            background: #1b344d;
            border-color: #5b87ac;
        }
        body.theme-dark .plan-card.selected .plan-check {
            background: #4f93c7;
            border-color: #4f93c7;
        }
        .calc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        .calc-label {
            font-size: .82rem;
            color: #3d5f7e;
            margin-bottom: 4px;
        }
        .price-line {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #9cc7ea;
            color: #0a4f90;
            font-weight: 700;
        }
        .form-panel {
            background: var(--card-bg);
            border: 1px solid var(--form-border);
            border-radius: 16px;
            padding: 1.2rem;
        }
        .form-panel h1,
        .form-panel .form-group,
        .form-panel .row.d-flex.align-items-center.justify-content-center.flex-nowrap.flex-row {
            display: none !important;
        }
        .form-panel .form-floating > .form-control {
            border-color: #bdd8f3;
        }
        body.theme-dark .form-panel .form-control,
        body.theme-dark .form-panel .form-select,
        body.theme-dark .form-panel .input-group-text {
            background: #10273d;
            border-color: #3f6486;
            color: #dbe9f8;
        }
        body.theme-dark .form-panel .form-floating > label,
        body.theme-dark .form-label {
            color: #b9d4ed;
        }
        .form-panel .form-floating > .form-control:focus {
            border-color: #7ab6ea;
            box-shadow: 0 0 0 .2rem rgba(11, 99, 182, .12);
        }
        .btn-main {
            background-color: var(--alfa-blue);
            color: #fff;
            border: 0;
            font-weight: 600;
        }
        .btn-main:hover {
            background-color: #09559d;
            color: #fff;
        }
        .btn-outline-main {
            border-color: #86bfe9;
            color: #0a4f90;
        }
        .btn-outline-main:hover {
            background: #e7f4ff;
            color: #0a4f90;
            border-color: #86bfe9;
        }
        @media (max-width: 767px) {
            .register-body {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container register-shell">
        <div class="register-box">
            <div class="top-info">
                <h2>ALFA RESERVAS</h2>
            </div>

            <div class="register-logo">
                <a href="<?= base_url() ?>"><img src="<?= base_url('alfa.png') ?>" alt="Alfa"></a>
            </div>

            <div class="register-body">
                <div class="hero-panel">
                    <div class="hero-icon">
                        <i class="fa-regular fa-id-card"></i>
                    </div>
                    <h2>Planes disponibles</h2>
                    <p>Selecciona un plan y ajusta precios por cantidad de servicios y usuarios.</p>

                    <div class="pricing-grid" id="pricingGrid">
                        <div class="plan-card selected" data-plan="Basico" data-base="12000" data-service="1800" data-user="900">
                            <span class="plan-check"><i class="fa-solid fa-check"></i></span>
                            <div class="plan-title">Basico</div>
                            <div class="plan-price">$12.000 base mensual</div>
                        </div>
                        <div class="plan-card" data-plan="Pro" data-base="24000" data-service="3200" data-user="1500">
                            <span class="plan-check"><i class="fa-solid fa-check"></i></span>
                            <div class="plan-title">Pro</div>
                            <div class="plan-price">$24.000 base mensual</div>
                        </div>
                        <div class="plan-card" data-plan="Premium" data-base="42000" data-service="5200" data-user="2500">
                            <span class="plan-check"><i class="fa-solid fa-check"></i></span>
                            <div class="plan-title">Premium</div>
                            <div class="plan-price">$42.000 base mensual</div>
                        </div>
                    </div>

                    <div class="calc-grid">
                        <div>
                            <div class="calc-label">Cantidad de servicios</div>
                            <input type="number" min="1" step="1" value="1" id="qtyServicios" class="form-control form-control-sm">
                        </div>
                        <div>
                            <div class="calc-label">Cantidad de usuarios</div>
                            <input type="number" min="1" step="1" value="1" id="qtyUsuarios" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="small text-muted">
                        Formula: Base + (Servicios x valor servicio) + (Usuarios x valor usuario)
                    </div>
                    <div class="price-line" id="priceLine">Total estimado: $14.700 / mes</div>
                </div>

                <form action="" method="POST" class="form-panel">

                <!-- legacy-form removed -->

                    <?php if (session('msg')) : ?>
                        <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
                            <small> <?= session('msg.body') ?> </small>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <h3 class="h5 mb-3 text-center" style="color:#0a4f90;font-weight:700;">Darse de alta</h3>
                    <input type="hidden" name="plan" id="plan" value="<?= esc(old('plan', 'Basico')) ?>">
                    <input type="hidden" name="cantidad_servicios" id="cantidad_servicios" value="<?= esc(old('cantidad_servicios', '1')) ?>">
                    <input type="hidden" name="cantidad_usuarios" id="cantidad_usuarios" value="<?= esc(old('cantidad_usuarios', '1')) ?>">

                    <div class="form-floating mb-3">
                        <input type="text" name="name" class="form-control" id="name" placeholder="Nombre y apellido" value="<?= esc(old('name')) ?>" required>
                        <label for="name">Nombre y apellido</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="razon_social" class="form-control" id="razon_social" placeholder="Razon social" value="<?= esc(old('razon_social')) ?>" required>
                        <label for="razon_social">Razon social</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-1">Link completo</label>
                        <div class="small text-muted mb-2" id="full_link_label">-</div>
                        <div class="input-group">
                            <span class="input-group-text">/</span>
                            <input type="text" name="link_path" class="form-control" id="link_path" placeholder="..." value="<?= esc(old('link_path')) ?>" autocomplete="off">
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="dni" class="form-control" id="dni" placeholder="DNI" value="<?= esc(old('dni')) ?>" required>
                        <label for="dni">DNI</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="city" class="form-control" id="city" placeholder="Localidad" value="<?= esc(old('city')) ?>" required>
                        <label for="city">Localidad</label>
                    </div>

                    <div class="mb-3">
                        <label for="id_rubro" class="form-label mb-1">Rubro</label>
                        <select name="id_rubro" id="id_rubro" class="form-select" required>
                            <option value="">Seleccionar rubro</option>
                            <?php foreach (($rubros ?? []) as $rubro) : ?>
                                <option value="<?= esc($rubro['id']) ?>" <?= (string) old('id_rubro') === (string) ($rubro['id'] ?? '') ? 'selected' : '' ?>>
                                    <?= esc($formatRubroLabel($rubro['descripcion'] ?? null)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="email" placeholder="Email" value="<?= esc(old('email')) ?>" required>
                        <label for="email">Email</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Contrasena" required>
                        <label for="password">Contrasena</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="phone" class="form-control" id="phone" placeholder="Telefono" value="<?= esc(old('phone')) ?>" required>
                        <label for="phone">Telefono</label>
                    </div>

                    <div class="d-grid gap-2 mt-2 mb-2">
                        <button type="submit" class="btn btn-main" id="btn-register-save">Registrar</button>
                        <a href="<?= base_url('auth/login') ?>" class="btn btn-outline-main">Volver</a>
                    </div>

                    <h1 style="color: #595959; font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;" class="text-center">Registrate y accedé a descuentos</h1>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="name" class="form-control" placeholder="Nombre">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="last_name" class="form-control" placeholder="Apellido">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="dni" class="form-control" placeholder="DNI">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="city" class="form-control" placeholder="Localidad">
                    </div>

                    <div class="d-flex justify-content-center align-items-center flex-row" style="width: 100%;">
                        <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center" style="width: 30%;">
                            <input type="text" name="areaCode" class="form-control" placeholder="Código de área">
                        </div>
                        <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center" style="width: 70%;">
                            <input type="text" name="phone" class="form-control" placeholder="Teléfono">
                        </div>
                    </div>


                    <div class="row d-flex align-items-center justify-content-center flex-nowrap flex-row">
                        <div class="col d-flex align-items-end justify-content-end">
                            <a href="<?= base_url('abmAdmin') ?>" style="background-color: #595959; color: #ffffff" class="btn btn-block btn-flat me-2">Volver</a>
                            <button type="submit" class="btn btn-block btn-flat" style="background-color: #f39323;" id="btn-login">Registrar</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const razonSocialInput = document.getElementById('razon_social');
            const linkPathInput = document.getElementById('link_path');
            const fullLinkLabel = document.getElementById('full_link_label');
            const baseWeb = <?= json_encode(rtrim((string) env('app.baseURL', base_url('/')), '/')) ?>;
            let linkEdited = false;

            const normalizeKey = (value) => {
                return (value || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '_')
                    .replace(/^_+|_+$/g, '')
                    .slice(0, 90);
            };

            const normalizePath = (value) => {
                return normalizeKey((value || '').replace(/^\/+/, ''));
            };

            const updateBaseAndLinkPreview = (forceAuto = false) => {
                if (!razonSocialInput || !linkPathInput || !fullLinkLabel) {
                    return;
                }

                const key = normalizeKey(razonSocialInput.value);

                if (!linkEdited || forceAuto) {
                    linkPathInput.value = key;
                }

                const path = normalizePath(linkPathInput.value);
                linkPathInput.value = path;
                fullLinkLabel.textContent = path ? (baseWeb + '/' + path) : '-';
            };

            const cards = Array.from(document.querySelectorAll('.plan-card'));
            const qtyServicios = document.getElementById('qtyServicios');
            const qtyUsuarios = document.getElementById('qtyUsuarios');
            const planHidden = document.getElementById('plan');
            const serviciosHidden = document.getElementById('cantidad_servicios');
            const usuariosHidden = document.getElementById('cantidad_usuarios');
            const priceLine = document.getElementById('priceLine');

            function money(n) {
                return new Intl.NumberFormat('es-AR').format(n);
            }

            function getSelectedCard() {
                return cards.find((c) => c.classList.contains('selected')) || cards[0];
            }

            function updatePrice() {
                const selected = getSelectedCard();
                const base = parseInt(selected.dataset.base || '0', 10);
                const perService = parseInt(selected.dataset.service || '0', 10);
                const perUser = parseInt(selected.dataset.user || '0', 10);
                const services = Math.max(1, parseInt(qtyServicios.value || '1', 10));
                const users = Math.max(1, parseInt(qtyUsuarios.value || '1', 10));
                const total = base + (services * perService) + (users * perUser);

                qtyServicios.value = services;
                qtyUsuarios.value = users;
                planHidden.value = selected.dataset.plan || 'Basico';
                serviciosHidden.value = String(services);
                usuariosHidden.value = String(users);
                priceLine.textContent = 'Total estimado: $' + money(total) + ' / mes';
            }

            cards.forEach((card) => {
                card.addEventListener('click', () => {
                    cards.forEach((c) => c.classList.remove('selected'));
                    card.classList.add('selected');
                    updatePrice();
                });
            });

            qtyServicios.addEventListener('input', updatePrice);
            qtyUsuarios.addEventListener('input', updatePrice);
            updatePrice();
            if (razonSocialInput) {
                razonSocialInput.addEventListener('input', updateBaseAndLinkPreview);
            }
            if (linkPathInput) {
                linkPathInput.addEventListener('input', function () {
                    linkEdited = true;
                    updateBaseAndLinkPreview();
                });
            }
            updateBaseAndLinkPreview();

            document.querySelectorAll('.form-panel .form-group input, #btn-login').forEach(function (el) {
                el.disabled = true;
            });
        });
    </script>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
</body>

</html>
