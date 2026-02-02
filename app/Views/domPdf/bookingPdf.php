<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos de la reserva</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        div {
            margin-top: 20px;
        }

        img {
            max-width: 100%;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin: 10px 0;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <div>
        <img src="<?= base_url(PUBLIC_FOLDER . "assets/images/logo_pdf.png") ?>" style="width: 300px;" alt="Logo">
    </div>

    <ul>
        <li><strong>Nombre:</strong> <?= $data['nombre'] ?></li>
        <li><strong>Teléfono:</strong> <?= $data['telefono'] ?></li>
        <li><strong>Fecha:</strong> <?= $data['fecha'] ?></li>
        <li><strong>Horario:</strong> <?= $data['horario'] ?></li>
        <li><strong>Cancha:</strong> <?= $data['cancha'] ?></li>
        <li></li>
        <li><strong>Id de pago de Mercado Pago:</strong> <?= $data['id_mercado_pago'] ?> </li>
        <li><strong>Estado del pago:</strong> <?= $data['estado_pago'] ?></li>
        <li></li>
        <li><strong>Valor total de la cancha:</strong> <?= $data['total_cancha'] ?></li>
        <li><strong>Pagado:</strong> <?= $data['pagado'] ?></li>
        <li><strong>Restan:</strong> <?= $data['saldo'] ?></li>
        <li><strong>Detalle:</strong> <?= $data['detalle']  == '' || $data['detalle'] == null ? 'Reserva' : $data['detalle'] ?></li>
    </ul>

    <ul>
        <li>Sr cliente, al abonar una reserva (sea de manera parcial o total) asume el compromiso y la responsabilidad de la asistencia.
            Caso contrario no hay devoluciones de dinero y los movimientos de reserva quedarán sujetos a disponibilidad.</li>
            <br>
        <li>Así mismo, en caso de llegar tarde a la cancha, el tiempo de juego será hasta la hora reservada. Si excepciones.</li>
    </ul>
</body>

</html>