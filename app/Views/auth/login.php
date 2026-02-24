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
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">
    <style>
        :root {
            --alfa-blue: #0b63b6;
            --alfa-sky: #dff1ff;
            --alfa-white: #ffffff;
            --alfa-ink: #14324f;
        }
        .login-box > h1 {
            display: none;
        }
        .top-info {
            width: 100%;
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
        .top-info p {
            margin: .35rem 0 0;
            color: #355c7f;
        }
        .hero-icon {
            width:54px;
            height:54px;
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            background:rgba(9,132,227,.12);
            color:#0a74c2;
            font-size:1.4rem;
            margin:.2rem auto .5rem;
        }
        .login-logo {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .login-logo img {
            width: 132px;
            height: auto;
        }
        .login-box-body {
            width: 100%;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 20px;
            align-items: stretch;
        }
        .login-box-body form {
            width: 100%;
        }
        .hero-panel {
            background: linear-gradient(155deg, #eef7ff 0%, #dbedff 100%);
            border: 1px solid #cfe6fb;
            border-radius: 16px;
            padding: 1.4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-panel {
            background: #fff;
            border: 1px solid #d3e4f5;
            border-radius: 16px;
            padding: 1.2rem;
        }
        .hero-panel h2 {
            color: #0a4f90 !important;
            font-size: 1.95rem !important;
        }
        .hero-panel p {
            color: #355c7f !important;
            font-size: 1.05rem;
        }
        .form-panel .form-floating > .form-control {
            border-color: #bdd8f3;
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
        #btn-register {
            border-color: #86bfe9;
            color: #0a4f90;
        }
        #btn-register:hover {
            background: #e7f4ff;
            color: #0a4f90;
            border-color: #86bfe9;
        }
        @media (max-width: 767px) {
            .login-logo {
                justify-content: center;
            }
            .login-box-body {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }
    </style>

</head>

<body style="min-height:100vh;background:
radial-gradient(circle at 12% 18%, rgba(11,99,182,.20), transparent 28%),
radial-gradient(circle at 88% 8%, rgba(93,188,255,.28), transparent 22%),
#f3f9ff;">

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
                    <h2 style="font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;color:#0a4f90;font-size:1.65rem;font-weight:700;margin-bottom:.2rem;">Portal de Reservas</h2>
                    <p style="color:#3d5f7e;margin-bottom:0;">Gestiona turnos y disponibilidad en un solo lugar.</p>
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
</body>

</html>
