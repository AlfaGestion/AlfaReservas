<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingreso | TURNOK</title>
    <!-- <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/login-page.css") ?>"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/9bae38f407.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
    <link rel="icon" href="<?= base_url('favicon-32x32.png?v=20260317a') ?>" sizes="32x32" type="image/png">
    <style>
        @font-face {
            font-family:'Quicksand';
            src:url('<?= base_url(PUBLIC_FOLDER . "assets/fonts/turnok/Quicksand-Regular.ttf") ?>') format('truetype');
            font-weight:400;
            font-style:normal;
        }
        @font-face {
            font-family:'Quicksand';
            src:url('<?= base_url(PUBLIC_FOLDER . "assets/fonts/turnok/Quicksand-Medium.ttf") ?>') format('truetype');
            font-weight:500;
            font-style:normal;
        }
        @font-face {
            font-family:'Quicksand';
            src:url('<?= base_url(PUBLIC_FOLDER . "assets/fonts/turnok/Quicksand-SemiBold.ttf") ?>') format('truetype');
            font-weight:600;
            font-style:normal;
        }
        @font-face {
            font-family:'Quicksand';
            src:url('<?= base_url(PUBLIC_FOLDER . "assets/fonts/turnok/Quicksand-Bold.ttf") ?>') format('truetype');
            font-weight:700;
            font-style:normal;
        }
        :root { --alfa-blue:#165ECC; --alfa-blue-strong:#11499d; --turnok-lime:#E3F50D; --turnok-orange:#FFA042; --turnok-sky:#B1D4F0; --turnok-ink:#1E1E1E; --turnok-cream:#F7F3E7; }
        body.login-screen {
            min-height:100vh; margin:0; font-family:'Quicksand',sans-serif;
            background:
                radial-gradient(1200px 500px at -10% -10%, rgba(22,94,204,.14), transparent 55%),
                radial-gradient(1000px 380px at 110% 0%, rgba(255,160,66,.09), transparent 55%),
                linear-gradient(180deg,#f5f1e6 0%,#edf2f8 100%);
            color:#1e1e1e;
        }
        .login-screen,
        .login-screen * {
            font-family:'Quicksand',sans-serif !important;
        }
        .login-shell { max-width:1080px; margin:0 auto; min-height:100vh; padding:12px 20px; display:flex; align-items:center; justify-content:center; }
        .login-card { width:100%; border-radius:24px; overflow:hidden; border:1px solid #c8dff4; background:rgba(255,255,255,.88); box-shadow:0 26px 60px rgba(18,59,98,.18); backdrop-filter:blur(3px); }
        .login-grid { display:grid; grid-template-columns:1.08fr .92fr; min-height:620px; }
        .showcase { padding:42px; background:linear-gradient(158deg, rgba(9,80,145,.93) 0%, rgba(12,104,190,.9) 60%, rgba(78,179,255,.82) 100%); color:#eff8ff; position:relative; }
        .showcase:before { content:\"\"; position:absolute; inset:0; background:radial-gradient(300px 220px at 100% 0%, rgba(255,255,255,.2), transparent 70%),radial-gradient(420px 280px at 0% 100%, rgba(255,255,255,.15), transparent 65%); pointer-events:none; }
        .brand-kicker { font-size:.82rem; letter-spacing:.12em; text-transform:uppercase; opacity:.86; margin-bottom:14px; position:relative; }
        .showcase h1 { font-family:'Quicksand',sans-serif; font-size:clamp(2rem,2.8vw,3rem); line-height:1.05; margin:0 0 16px; position:relative; }
        .showcase p { font-size:1rem; max-width:480px; margin:0 0 24px; opacity:.95; position:relative; }
        .feature-list { list-style:none; padding:0; margin:0; display:grid; gap:10px; position:relative; }
        .feature-list li { display:flex; align-items:center; gap:10px; font-weight:600; font-size:.95rem; }
        .feature-list i { width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.2); font-size:.75rem; }
        .logo-wrap { margin-top:32px; position:relative; }
        .logo-wrap img { width:90px; height:auto; border-radius:50%; background:rgba(255,255,255,.16); padding:10px; }
        .access-panel { padding:42px 36px; display:flex; flex-direction:column; justify-content:center; background:linear-gradient(180deg, rgba(255,255,255,.9) 0%, #fff 100%); }
        .panel-header { margin-bottom:18px; }
        .panel-title { margin:0; font-family:'Quicksand',sans-serif; font-size:1.6rem; color:#133e66; }
        .panel-subtitle { margin:4px 0 0; color:#4f6f8e; font-size:.92rem; }
        .form-floating>.form-control { border:1px solid #bed7ee; background:#f7fbff; border-radius:12px; min-height:58px; color:#113a61; }
        .form-floating>.form-control:focus { background:#fff; border-color:#77b4e8; box-shadow:0 0 0 .24rem rgba(17,123,210,.16); }
        .form-floating>label { color:#4f6f8e; }
        .btn-main { background:linear-gradient(135deg,var(--alfa-blue) 0%,var(--alfa-blue-strong) 100%); color:#fff; border:0; font-weight:700; border-radius:12px; min-height:48px; }
        .btn-main:hover { color:#fff; filter:brightness(1.03); }
        #btn-register { border-color:#9fc7e7; color:#0d4f87; border-radius:12px; min-height:48px; font-weight:600; background:#f5faff; }
        #btn-register:hover { background:#e7f3ff; border-color:#7bb2df; color:#0b4578; }
        .login-page { min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-box {
            max-width:1080px;
            width:100%;
            border-radius:28px;
            border:1px solid rgba(110,164,224,.52);
            background:
                radial-gradient(circle at 94% 18%, rgba(177,212,240,.18), transparent 18%),
                linear-gradient(135deg, #f7fbff 0%, #eef5fc 100%);
            box-shadow:0 26px 64px rgba(8,18,35,.12);
            backdrop-filter:blur(8px);
            padding:0 !important;
            overflow:hidden;
            position:relative;
        }
        .login-box::before {
            content:'';
            position:absolute;
            inset:0;
            background-image:
                linear-gradient(rgba(177,212,240,.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(177,212,240,.08) 1px, transparent 1px);
            background-size:32px 32px;
            opacity:.22;
            pointer-events:none;
        }
        .top-info { display:none; }
        .login-logo { display:none !important; }
        .login-box > h1 { display:none; }
        .login-box-body {
            display:grid !important;
            grid-template-columns:1.1fr .9fr;
            align-items:stretch;
            width:100%;
            min-height:470px;
            margin:0 !important;
            padding:0 !important;
            position:relative;
            z-index:1;
        }
        .hero-panel {
            margin:0 !important;
            padding:28px 34px 18px !important;
            border:0 !important;
            border-radius:0 !important;
            background:transparent !important;
            color:#eff8ff !important;
            text-align:left !important;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            background:linear-gradient(145deg, #275b9f 0%, #214f8f 100%) !important;
        }
        .hero-icon { display:none !important; }
        .hero-top {
            display:flex;
            flex-direction:column;
            align-items:flex-start;
            gap:4px;
            margin-bottom:18px;
        }
        .hero-top img {
            width:222px;
            height:auto;
            display:block;
            margin-left:-18px;
        }
        .hero-top span {
            font-size:1.05rem;
            letter-spacing:-.01em;
            text-transform:none;
            font-weight:700;
            color:#f7f3e7;
            opacity:.98;
        }
        .hero-panel h2 {
            font-family:'Quicksand',sans-serif !important;
            font-size:clamp(1.95rem,2.5vw,2.75rem) !important;
            line-height:1.04 !important;
            color:#f7f3e7 !important;
            margin:0 0 10px !important;
            position:relative;
            font-weight:700;
            letter-spacing:-.03em;
            text-shadow:0 6px 22px rgba(8,18,35,.22);
        }
        .hero-panel p {
            color:#dbeafb !important;
            font-size:.94rem !important;
            margin:0 0 14px !important;
            max-width:420px;
        }
        .hero-points {
            list-style:none;
            margin:0;
            padding:0;
            display:grid;
            gap:9px;
        }
        .hero-points li {
            display:flex;
            align-items:center;
            gap:10px;
            font-weight:600;
            color:#edf6ff;
            font-size:.9rem;
        }
        .hero-points li::before {
            content:'';
            width:8px;
            height:8px;
            border-radius:50%;
            background:#c6e6ff;
            box-shadow:0 0 0 4px rgba(198,230,255,.2);
        }
        .hero-note {
            margin-top:12px;
            padding:9px 14px;
            border-radius:999px;
            background:rgba(247,243,231,.1);
            border:1px solid rgba(177,212,240,.22);
            color:#ecf7ff;
            font-size:.76rem;
            letter-spacing:.04em;
            text-transform:uppercase;
            width:max-content;
        }
        .hero-bottom {
            margin-top:14px;
            padding-top:12px;
            border-top:1px solid rgba(214,232,249,.36);
        }
        .hero-stats {
            display:grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap:8px;
        }
        .hero-stat {
            padding:9px 11px 10px;
            border-radius:16px;
            background:linear-gradient(180deg, rgba(84,138,212,.16) 0%, rgba(66,112,184,.18) 100%);
            border:1px solid rgba(177,212,240,.2);
            box-shadow:inset 0 1px 0 rgba(255,255,255,.08);
        }
        .hero-stat strong {
            display:block;
            color:#f7f3e7;
            font-size:.95rem;
            line-height:1;
            margin-bottom:5px;
            font-weight:700;
        }
        .hero-stat span {
            display:block;
            color:#d9ecff;
            font-size:.62rem;
            line-height:1.25;
            text-transform:uppercase;
            letter-spacing:.08em;
            font-weight:700;
        }
        .form-panel {
            margin:14px 14px 14px 0 !important;
            border:1px solid rgba(177,212,240,.82) !important;
            border-radius:34px !important;
            padding:30px 28px !important;
            background:linear-gradient(180deg, rgba(255,255,255,.98) 0%, rgba(250,252,255,.98) 100%) !important;
            backdrop-filter:blur(12px);
            box-shadow:0 20px 38px rgba(17,73,157,.10), inset 0 1px 0 rgba(255,255,255,.8);
            display:flex;
            flex-direction:column;
            justify-content:center;
            max-width:400px;
            width:100%;
            align-self:center;
            justify-self:center;
        }
        .form-panel > h3 {
            margin:0 0 4px !important;
            font-family:'Quicksand',sans-serif !important;
            font-size:1.08rem !important;
            color:#ffffff !important;
            text-align:left !important;
            font-weight:700;
            letter-spacing:-.02em;
        }
        .form-panel > h3::after {
            content:'Ingresa con tu cuenta para administrar clientes y reservas.';
            display:block;
            margin-top:5px;
            font-family:'Quicksand',sans-serif;
            font-weight:500;
            font-size:.82rem;
            line-height:1.35;
            color:rgba(255,255,255,.82);
        }
        .form-kicker {
            display:inline-flex;
            align-items:center;
            gap:8px;
            margin-bottom:12px;
            padding:6px 11px;
            border-radius:999px;
            background:rgba(22,94,204,.06);
            border:1px solid rgba(177,212,240,.55);
            color:#e6f1fb;
            font-size:.62rem;
            text-transform:uppercase;
            letter-spacing:.1em;
            font-weight:700;
            width:max-content;
        }
        .form-kicker::before {
            content:'';
            width:8px;
            height:8px;
            border-radius:50%;
            background:var(--turnok-lime);
            box-shadow:0 0 0 4px rgba(227,245,13,.14);
        }
        .form-panel .row.mt-3 { margin-top:.5rem !important; }
        .form-panel .row.mt-3 .col { padding:0 !important; }
        .setup-hint {
            margin-bottom: 12px;
            padding: 10px 12px;
            border-radius: 14px;
            background: rgba(177,212,240,.12);
            border: 1px solid rgba(177,212,240,.32);
            color: #ffffff;
            font-size: .84rem;
            line-height: 1.35;
        }
        .form-panel .form-floating>.form-control { min-height:56px; background:#24415f; border:1px solid rgba(93,142,196,.84); border-radius:14px; color:#ffffff; font-size:1rem; padding-top:1.2rem; padding-bottom:.52rem; }
        .form-panel .form-floating>.form-control:focus { background:#2a4968; border-color:#8ec2f3; box-shadow:0 0 0 .22rem rgba(114,167,234,.14); }
        .form-panel .form-floating>label { color:rgba(255,255,255,.78); font-size:.94rem; }
        .form-panel .form-floating>.form-control::placeholder { color:transparent; }
        .btn-main { background:linear-gradient(135deg, #f2efe6 0%, #d9e7f7 100%); color:#11499d; border:0; font-weight:800; border-radius:14px; min-height:46px; font-size:1rem; box-shadow:none; }
        .btn-main:hover { color:#11499d; filter:brightness(1.02); }
        #btn-register { background:transparent; border-color:rgba(177,212,240,.42); color:#ffffff; border-radius:14px; min-height:46px; font-size:.98rem; }
        #btn-register:hover { background:rgba(177,212,240,.08); border-color:rgba(147,191,235,.74); color:#ffffff; }
        body.theme-dark.login-screen {
            background:
                radial-gradient(1100px 460px at -10% -10%, rgba(22,94,204,.18), transparent 55%),
                radial-gradient(1000px 360px at 110% 0%, rgba(255,160,66,.05), transparent 55%),
                linear-gradient(180deg,#0c1826 0%,#0f2133 100%);
            color:#dbe9f8;
        }
        body.theme-dark .login-box {
            border-color:rgba(53,111,191,.7);
            background:
                radial-gradient(circle at 88% 82%, rgba(255,160,66,.10), transparent 24%),
                linear-gradient(135deg, #15345d 0%, #114587 52%, #0c2137 100%);
            box-shadow:0 24px 54px rgba(2,9,18,.38);
        }
        body.theme-dark .hero-panel { background:transparent !important; }
        body.theme-dark .hero-top span,
        body.theme-dark .hero-panel p,
        body.theme-dark .hero-note,
        body.theme-dark .hero-points li {
            color:#eef6ff !important;
        }
        body.theme-dark .hero-note {
            background:rgba(247,243,231,.10);
            border-color:rgba(177,212,240,.24);
        }
        body.theme-dark .form-panel {
            border:1px solid rgba(86,126,172,.4) !important;
            background:linear-gradient(180deg, rgba(12,33,54,.96) 0%, rgba(11,28,46,.98) 100%) !important;
            box-shadow:0 20px 38px rgba(6,14,24,.32), inset 0 1px 0 rgba(255,255,255,.04);
        }
        body.theme-dark .form-panel > h3 { color:#f7f3e7 !important; }
        body.theme-dark .form-panel > h3::after { color:#9fb9d2; }
        body.theme-dark .form-kicker {
            background:rgba(247,243,231,.06);
            border:1px solid rgba(177,212,240,.12);
            color:#d8e7f6;
        }
        body.theme-dark .form-panel .form-floating>.form-control {
            background:#1e3a58;
            border-color:rgba(93,142,196,.84);
            color:#eef6ff;
        }
        body.theme-dark .setup-hint {
            background: rgba(177,212,240,.08);
            border-color: rgba(177,212,240,.18);
            color: #eef6ff;
        }
        body.theme-dark .form-panel .form-floating>.form-control:focus { background:#254463; }
        body.theme-dark .form-panel .form-floating>label { color:#aac2da; }
        body.theme-dark #btn-register {
            background:transparent;
            color:#dbe9f8;
        }
        body.theme-dark #btn-register:hover {
            background:rgba(177,212,240,.08);
            color:#ffffff;
        }
        @media (max-width:900px){ .login-box-body{grid-template-columns:1fr; min-height:0;} .hero-panel{padding:34px 28px 22px !important;} .hero-bottom{padding-top:20px;} .hero-stats{grid-template-columns:1fr;} .form-panel{margin:0 24px 24px !important; padding:32px 24px !important; max-width:none;} }
    </style>

</head>

<body class="login-screen">
    <script>
        (function () {
            try {
                if (localStorage.getItem('alfa_theme') === 'dark') {
                    document.body.classList.add('theme-dark');
                }
            } catch (e) {}
        })();
    </script>

    <div class="container login-page d-flex justify-content-center align-items-center" style="min-height:100vh;">

        <div class="login-box d-flex justify-content-center flex-column align-items-center" style="max-width:980px;width:100%;border:1px solid rgba(177,212,240,.8);border-radius:18px;box-shadow:0 18px 40px rgba(30,30,30,.12);padding:0;">
            <div class="top-info">
                <h2>TURNOK</h2>
            </div>
            <div class="login-logo">
                <a href="<?= base_url() ?>"><img src="<?= base_url(PUBLIC_FOLDER . "assets/images/logo.png") ?>" width="180px" alt="TURNOK"></a>
            </div>
            <h1 style="color:#595959">Inicio de sesión</h1>


            <div class="login-box-body">
                <div class="text-center mb-0 hero-panel">
                    <div class="hero-icon">
                        <i class="fa-regular fa-calendar-check"></i>
                    </div>
                    <div class="hero-top">
                        <span>Ordena tu agenda con</span>
                        <img src="<?= base_url(PUBLIC_FOLDER . 'assets/images/logo-shadow.png') ?>" alt="TURNOK">
                    </div>
                    <h2>Portal de Reservas</h2>
                    <p>Gestiona turnos y disponibilidad en un solo lugar.</p>
                    <ul class="hero-points">
                        <li>Gestion de turnos en tiempo real</li>
                        <li>Panel multi-rubro y multi-cliente</li>
                        <li>Acceso centralizado para administradores</li>
                    </ul>
                    <div class="hero-note">Escalable para canchas, pedidos y mas rubros.</div>
                    <div class="hero-bottom">
                        <div class="hero-stats">
                            <div class="hero-stat">
                                <strong>24/7</strong>
                                <span>Acceso online</span>
                            </div>
                            <div class="hero-stat">
                                <strong>Multi</strong>
                                <span>Rubros y sedes</span>
                            </div>
                            <div class="hero-stat">
                                <strong>Simple</strong>
                                <span>Gestion central</span>
                            </div>
                        </div>
                    </div>
                </div>
                <form action="/auth/login" method="POST" class="form-panel">
                    <div class="form-kicker">Acceso admin</div>
                    <h3 class="h5 mb-3 text-center" style="color:#0a4f90;font-weight:700;">Iniciar sesión</h3>
                    <?php if (!empty($redirectPath)) : ?>
                        <input type="hidden" name="redirect" value="<?= esc($redirectPath) ?>">
                    <?php endif; ?>
                    <?php if (session('post_register_setup') && !empty($redirectPath)) : ?>
                        <input type="hidden" name="onboarding_setup" value="1">
                    <?php endif; ?>

                    <?php if (session('msg')) : ?>
                        <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
                            <small> <?= session('msg.body') ?> </small>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (session('post_register_setup') && !empty($redirectPath)) : ?>
                        <div class="setup-hint">
                            Tu cuenta ya fue creada. Ingresa y te llevamos directo a <strong>configurar tu sitio</strong>.
                        </div>
                    <?php endif; ?>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="account" name="account" placeholder="Cuenta o email" required>
                        <label for="account">Cuenta o email</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Contrasena" required>
                        <label for="password">Contrasena</label>
                    </div>

                    <div class="row mt-3">
                        <!-- <div class="col">
                            <div class="checkbox icheck">
                                <label class="">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                        <label class="form-check-label" for="flexCheckDefault">Recordarme </label>
                                    </div>
                                </label>
                            </div>
                        </div> -->

                        <div class="col d-grid gap-2">
                            <?php if (session('post_register_setup') && !empty($redirectPath)) : ?>
                                <button type="submit" class="btn btn-block btn-flat btn-main" id="btn-login">Ingresar y configurar mi sitio</button>
                            <?php else : ?>
                                <button type="submit" class="btn btn-block btn-flat btn-main" id="btn-login">Ingresar</button>
                            <?php endif; ?>
                            <a href="<?= base_url('customers/register') ?>" class="btn mt-2" id="btn-register">Darse de alta</a>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>
</body>

</html>
