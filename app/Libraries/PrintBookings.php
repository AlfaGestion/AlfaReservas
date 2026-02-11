<?php

namespace App\Libraries;

use Dompdf\Dompdf;

class PrintBookings
{
    public function renderBooking($data)
    {
        $name = time() . '_reserva_cancha_' . $data['telefono'] . '.pdf';
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('domPdf/bookingPdf', ['data' => $data]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return [
            'name' => $name,
            'content' => $dompdf->output(),
        ];
    }

    public function renderReports($data)
    {
        $name = time() . 'reporte.pdf';
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('domPdf/reportPdf', ['data' => $data]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return [
            'name' => $name,
            'content' => $dompdf->output(),
        ];
    }

    public function renderPaymentsReports($data)
    {
        $name = time() . 'reporte_de_ingresos_mercado_pago.pdf';
        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('domPdf/reportPaymentPdf', ['data' => $data]));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return [
            'name' => $name,
            'content' => $dompdf->output(),
        ];
    }
}
