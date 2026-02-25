<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingreso | Alfa Reservas</title>
    <!-- <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/login-page.css") ?>"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/9bae38f407.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root { --alfa-blue:#0c63b5; --alfa-blue-strong:#0a4c8a; }
        body.login-screen {
            min-height:100vh; margin:0; font-family:'Manrope',sans-serif;
            background:
                radial-gradient(1200px 500px at -10% -10%, rgba(12,99,181,.22), transparent 55%),
                radial-gradient(1000px 380px at 110% 0%, rgba(40,160,255,.22), transparent 55%),
                linear-gradient(180deg,#f2f8ff 0%,#ecf4ff 100%);
            color:#163555;
        }
        .login-shell { max-width:1080px; margin:0 auto; min-height:100vh; padding:28px 20px; display:flex; align-items:center; justify-content:center; }
        .login-card { width:100%; border-radius:24px; overflow:hidden; border:1px solid #c8dff4; background:rgba(255,255,255,.88); box-shadow:0 26px 60px rgba(18,59,98,.18); backdrop-filter:blur(3px); }
        .login-grid { display:grid; grid-template-columns:1.08fr .92fr; min-height:620px; }
        .showcase { padding:42px; background:linear-gradient(158deg, rgba(9,80,145,.93) 0%, rgba(12,104,190,.9) 60%, rgba(78,179,255,.82) 100%); color:#eff8ff; position:relative; }
        .showcase:before { content:\"\"; position:absolute; inset:0; background:radial-gradient(300px 220px at 100% 0%, rgba(255,255,255,.2), transparent 70%),radial-gradient(420px 280px at 0% 100%, rgba(255,255,255,.15), transparent 65%); pointer-events:none; }
        .brand-kicker { font-size:.82rem; letter-spacing:.12em; text-transform:uppercase; opacity:.86; margin-bottom:14px; position:relative; }
        .showcase h1 { font-family:'Space Grotesk',sans-serif; font-size:clamp(2rem,2.8vw,3rem); line-height:1.05; margin:0 0 16px; position:relative; }
        .showcase p { font-size:1rem; max-width:480px; margin:0 0 24px; opacity:.95; position:relative; }
        .feature-list { list-style:none; padding:0; margin:0; display:grid; gap:10px; position:relative; }
        .feature-list li { display:flex; align-items:center; gap:10px; font-weight:600; font-size:.95rem; }
        .feature-list i { width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.2); font-size:.75rem; }
        .logo-wrap { margin-top:32px; position:relative; }
        .logo-wrap img { width:90px; height:auto; border-radius:50%; background:rgba(255,255,255,.16); padding:10px; }
        .access-panel { padding:42px 36px; display:flex; flex-direction:column; justify-content:center; background:linear-gradient(180deg, rgba(255,255,255,.9) 0%, #fff 100%); }
        .panel-header { margin-bottom:18px; }
        .panel-title { margin:0; font-family:'Space Grotesk',sans-serif; font-size:1.6rem; color:#133e66; }
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
            max-width:1040px;
            width:100%;
            border-radius:24px;
            border:1px solid #c8dff4;
            background:rgba(255,255,255,.88);
            box-shadow:0 26px 60px rgba(18,59,98,.18);
            backdrop-filter:blur(3px);
            padding:0 !important;
            overflow:hidden;
        }
        .top-info { display:none; }
        .login-logo { display:none !important; }
        .login-box > h1 { display:none; }
        .login-box-body {
            display:grid !important;
            grid-template-columns:1.08fr .92fr;
            align-items:stretch;
            width:100%;
            min-height:620px;
            margin:0 !important;
            padding:0 !important;
        }
        .hero-panel {
            margin:0 !important;
            padding:42px !important;
            border:0 !important;
            border-radius:0 !important;
            background:linear-gradient(158deg, rgba(9,80,145,.93) 0%, rgba(12,104,190,.9) 60%, rgba(78,179,255,.82) 100%) !important;
            color:#eff8ff !important;
            text-align:left !important;
            display:flex;
            flex-direction:column;
            justify-content:flex-start;
        }
        .hero-icon { display:none !important; }
        .hero-top {
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:22px;
        }
        .hero-top img {
            width:42px;
            height:42px;
            border-radius:50%;
            background:rgba(255,255,255,.95);
            padding:4px;
            border:1px solid rgba(255,255,255,.75);
        }
        .hero-top span {
            font-size:.8rem;
            letter-spacing:.12em;
            text-transform:uppercase;
            font-weight:700;
            color:#d9edff;
        }
        .hero-panel h2 {
            font-family:'Space Grotesk',sans-serif !important;
            font-size:clamp(2rem,2.8vw,3rem) !important;
            line-height:1.05 !important;
            color:#eff8ff !important;
            margin:0 0 12px !important;
            position:relative;
        }
        .hero-panel p {
            color:#e6f3ff !important;
            font-size:1rem !important;
            margin:0 0 20px !important;
        }
        .hero-points {
            list-style:none;
            margin:0;
            padding:0;
            display:grid;
            gap:10px;
        }
        .hero-points li {
            display:flex;
            align-items:center;
            gap:10px;
            font-weight:600;
            color:#eaf6ff;
            font-size:.97rem;
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
            margin-top:24px;
            padding:10px 14px;
            border-radius:10px;
            background:rgba(255,255,255,.16);
            border:1px solid rgba(255,255,255,.2);
            color:#ecf7ff;
            font-size:.85rem;
            width:max-content;
        }
        .form-panel {
            margin:0 !important;
            border:0 !important;
            border-radius:0 !important;
            padding:42px 36px !important;
            background:linear-gradient(180deg, rgba(255,255,255,.9) 0%, #fff 100%) !important;
            display:flex;
            flex-direction:column;
            justify-content:center;
        }
        .form-panel > h3 {
            margin:0 0 4px !important;
            font-family:'Space Grotesk',sans-serif !important;
            font-size:1.6rem !important;
            color:#133e66 !important;
            text-align:left !important;
        }
        .form-panel > h3::after {
            content:'Ingresa con tu cuenta para administrar clientes y reservas.';
            display:block;
            margin-top:6px;
            font-family:'Manrope',sans-serif;
            font-weight:500;
            font-size:.92rem;
            color:#4f6f8e;
        }
        .form-panel .row.mt-3 { margin-top:.5rem !important; }
        .form-panel .row.mt-3 .col { padding:0 !important; }
        .form-panel .form-floating>.form-control { min-height:58px; background:#f7fbff; border:1px solid #bed7ee; border-radius:12px; }
        .form-panel .form-floating>.form-control:focus { background:#fff; border-color:#77b4e8; box-shadow:0 0 0 .24rem rgba(17,123,210,.16); }
        .form-panel .form-floating>label { color:#4f6f8e; }
        body.theme-dark.login-screen { background:radial-gradient(900px 420px at -5% -10%, rgba(33,127,218,.3), transparent 60%),radial-gradient(900px 300px at 110% 0%, rgba(72,172,255,.2), transparent 58%),linear-gradient(180deg,#0e1d2d 0%,#102133 100%) !important; }
        body.theme-dark .login-card { border-color:#355578; background:rgba(16,37,58,.82); box-shadow:0 28px 55px rgba(2,9,18,.58); }
        body.theme-dark .showcase { background:linear-gradient(158deg, rgba(14,69,119,.94) 0%, rgba(18,90,154,.9) 62%, rgba(38,138,216,.82) 100%); }
        body.theme-dark .access-panel { background:linear-gradient(180deg, rgba(17,37,57,.95) 0%, rgba(15,31,47,.97) 100%); }
        body.theme-dark .panel-title { color:#dceeff; }
        body.theme-dark .panel-subtitle { color:#9fc0df; }
        body.theme-dark .form-floating>.form-control { background:#1b334a; border-color:#4f7294; color:#e8f3ff; }
        body.theme-dark .form-floating>.form-control:focus { background:#223f5b; border-color:#78b3e3; box-shadow:0 0 0 .22rem rgba(93,167,225,.18); }
        body.theme-dark .form-floating>label { color:#accae5 !important; }
        body.theme-dark #btn-register { background:rgba(23,63,98,.6); border-color:#628db1; color:#d2e8ff; }
        body.theme-dark #btn-register:hover { background:rgba(39,90,133,.75); border-color:#7caad0; }
        body.theme-dark .login-box { border-color:#355578; background:rgba(16,37,58,.82); box-shadow:0 28px 55px rgba(2,9,18,.58); }
        body.theme-dark .hero-panel { background:linear-gradient(158deg, rgba(14,69,119,.94) 0%, rgba(18,90,154,.9) 62%, rgba(38,138,216,.82) 100%) !important; }
        body.theme-dark .hero-top img { background:rgba(230,243,255,.96); border-color:rgba(185,217,245,.95); }
        body.theme-dark .form-panel { background:linear-gradient(180deg, rgba(17,37,57,.95) 0%, rgba(15,31,47,.97) 100%) !important; }
        body.theme-dark .form-panel>h3 { color:#dceeff !important; }
        body.theme-dark .form-panel>h3::after { color:#9fc0df; }
        body.theme-dark .form-panel .form-floating>.form-control { background:#1b334a; border-color:#4f7294; color:#e8f3ff; }
        body.theme-dark .form-panel .form-floating>.form-control:focus { background:#223f5b; border-color:#78b3e3; box-shadow:0 0 0 .22rem rgba(93,167,225,.18); }
        body.theme-dark .form-panel .form-floating>label { color:#accae5 !important; }
        @media (max-width:900px){ .login-grid{grid-template-columns:1fr;} .showcase,.access-panel{padding:28px 24px;} .showcase h1{font-size:2rem;} }
        @media (max-width:900px){ .login-box-body{grid-template-columns:1fr; min-height:0;} .hero-panel,.form-panel{padding:28px 24px !important;} }
    </style>

</head>

<body class="login-screen">

    <div class="container login-page d-flex justify-content-center align-items-center" style="min-height:100vh;">

        <div class="login-box d-flex justify-content-center flex-column align-items-center" style="max-width:920px;width:100%;background:#fff;border:1px solid #cfe6fb;border-radius:18px;box-shadow:0 18px 40px rgba(16,65,116,.14);padding:2rem;">
            <div class="top-info">
                <h2>ALFA RESERVAS</h2>
            </div>
            <div class="login-logo">
                <a href="<?= base_url() ?>"><img src="<?= base_url('alfa.png') ?>" width="180px" alt="Alfa"></a>
            </div>
            <h1 style="font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; color: #595959">Inicio de sesión</h1>


            <div class="login-box-body">
                <div class="text-center mb-0 hero-panel">
                    <div class="hero-icon">
                        <i class="fa-regular fa-calendar-check"></i>
                    </div>
                    <div class="hero-top">
                        <img src="<?= base_url('alfa.png') ?>" alt="Alfa">
                        <span>Plataforma Alfa</span>
                    </div>
                    <h2 style="font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;color:#0a4f90;font-size:1.65rem;font-weight:700;margin-bottom:.2rem;">Portal de Reservas</h2>
                    <p style="color:#3d5f7e;margin-bottom:0;">Gestiona turnos y disponibilidad en un solo lugar.</p>
                    <ul class="hero-points">
                        <li>Gestion de turnos en tiempo real</li>
                        <li>Panel multi-rubro y multi-cliente</li>
                        <li>Acceso centralizado para administradores</li>
                    </ul>
                    <div class="hero-note">Escalable para canchas, pedidos y mas rubros.</div>
                </div>
                <form action="/auth/login" method="POST" class="form-panel">
                    <h3 class="h5 mb-3 text-center" style="color:#0a4f90;font-weight:700;">Iniciar sesión</h3>
                    <?php if (!empty($redirectPath)) : ?>
                        <input type="hidden" name="redirect" value="<?= esc($redirectPath) ?>">
                    <?php endif; ?>

                    <?php if (session('msg')) : ?>
                        <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
                            <small> <?= session('msg.body') ?> </small>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                            <button type="submit" class="btn btn-block btn-flat btn-main" id="btn-login">Ingresar</button>
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
