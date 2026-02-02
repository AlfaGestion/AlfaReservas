<?php

namespace App\Libraries;

use Dompdf\Dompdf;

class PrintBookings
{
    public function printBooking($data)
    {
        $name = time() . '_reserva_cancha_' . $data['telefono'] . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $name . '"');

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('domPdf/bookingPdf', ['data' => $data]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($name);
        exit();
    }

    public function printReports($data)
    {
        $name = time() . 'reporte.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $name . '"');

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('domPdf/reportPdf', ['data' => $data]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($name);
        exit();
    }

    public function printPaymentsReports($data)
    {
        $name = time() . 'reporte_de_ingresos_mercado_pago.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $name . '"');

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('domPdf/reportPaymentPdf', ['data' => $data]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($name);
        exit();
    }
}
