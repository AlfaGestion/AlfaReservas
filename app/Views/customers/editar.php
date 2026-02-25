<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar cliente</title>
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/theme.css") ?>">
    <link rel="stylesheet" href="<?= base_url(PUBLIC_FOLDER . "assets/css/styles.css") ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/9bae38f407.js" crossorigin="anonymous"></script>
    <link rel="icon" href="<?= base_url('alfa.png') ?>" type="image/png">

</head>

<body style="background-color: #f8f9fa;">
    <div class="container login-page d-flex justify-content-center align-items-center">
        <div class="login-box">

            <div class="login-box-body d-flex flex-column justify-content-center align-items-center">
                <div class="login-logo">
                    <a href="<?= base_url() ?>"><img src="<?= base_url('alfa.png') ?>" width="200px" alt="Alfa"></a>
                </div>

                <form action="<?= base_url('customers/editCustomer') ?>" method="POST">

                    <?php if (session('msg')) : ?>
                        <div class="alert alert-<?= session('msg.type') ?> alert-dismissible fade show" role="alert">
                            <small> <?= session('msg.body') ?> </small>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <h1 class="text-center" style="font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; color: #595959">Editar un cliente</h1>

                    <input type="hidden" value="<?= $customer['id'] ?>" name="idCustomer">

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="name" class="form-control" placeholder="Nombre" value="<?= $customer['name'] ?>">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="last_name" class="form-control" placeholder="Apellido" value="<?= $customer['last_name'] ?>">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="dni" class="form-control" placeholder="DNI" value="<?= $customer['dni'] ?>">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="phone" class="form-control" placeholder="Teléfono" value="<?= $customer['phone'] ?>">
                    </div>

                    <div class="form-group has-feedback mb-3 d-flex align-items-center justify-content-center">
                        <input type="text" name="city" class="form-control" placeholder="Localidad" value="<?= $customer['city'] ?>">
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="offer" role="switch" id="offer">
                        <label class="form-check-label" for="offer">Ofrecer ofertas</label>
                    </div>

                    <div class="row d-flex align-items-center justify-content-center flex-nowrap flex-row">
                        <div class="col d-flex align-items-end justify-content-end">
                            <a href="<?= base_url('abmAdmin') ?>" class="btn btn-block btn-flat me-2" style="background-color: #595959; color: #fff">Volver</a>
                            <button type="submit" class="btn btn-block btn-flat" style="background-color: #f39323;">Guardar</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script src="<?= base_url(PUBLIC_FOLDER . "assets/js/theme.js") ?>"></script>

</body>

</html>
