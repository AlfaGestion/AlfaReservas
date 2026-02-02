<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\PrintBookings;
use App\Models\BookingsModel;
use App\Models\CustomersModel;
use App\Models\FieldsModel;
use App\Models\MercadoPagoModel;
use App\Models\PaymentsModel;
use App\Models\TimeModel;
use App\Models\UsersModel;
use CodeIgniter\I18n\Time;

class Bookings extends BaseController
{
    public function saveBooking()
    {
        $bookingsModel = new BookingsModel();
        $customersModel = new CustomersModel();

        $data = $this->request->getJSON();

        $queryBooking = [
            'date'                  => $data->fecha,
            'id_field'              => $data->cancha,
            'time_from'             => $data->horarioDesde,
            'time_until'            => $data->horarioHasta,
            'name'                  => $data->nombre,
            'phone'                 => $data->codigoArea . $data->telefono,
            'payment'               => $data->monto,
            'approved'              => 0,
            'total'                 => $data->total,
            'parcial'               => $data->parcial,
            'diference'             => $data->diferencia,
            'reservation'           => $data->reservacion,
            'total_payment'         => $data->pagoTotal,
            'payment_method'        => $data->metodoDePago,
            'id_preference_parcial' => $data->preferenceIdParcial,
            'id_preference_total'   => $data->preferenceIdTotal,
            'use_offer'             => $data->oferta,
            'booking_time'          => date("Y-m-d H:i:s"),
            'mp'                    => 0,
            'annulled'              => 0, // Aseguramos que este nuevo registro no esté anulado
        ];

        $queryCustomer = [
            'name'  => $data->nombre,
            'phone' => $data->codigoArea . $data->telefono,
            'offer' => 0,
        ];

        // Verificar si ya existe una reserva activa
        $existingBooking = $bookingsModel->where('date', $data->fecha)
            ->where('id_field', $data->cancha)
            ->where('time_from', $data->horarioDesde)
            ->where('time_until', $data->horarioHasta)
            ->where('annulled', 0) // Solo trae las no anuladas
            ->first();

        if ($existingBooking) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Ya existe una reserva activa para esa fecha, cancha y horario.'));
        }

        $existingCustomer = $customersModel->findAll();
        $exist = true;

        if ($existingCustomer) {
            foreach ($existingCustomer as $customer) {
                if ($customer['phone'] == $data->codigoArea . $data->telefono) {
                    $exist = false;

                    if ($data->oferta == 1) {
                        $offer = ['offer' => 0];
                        $customersModel->update($customer['id'], $offer);
                    }

                    break;
                }
            }
        }

        if ($exist) {
            $customersModel->insert($queryCustomer);
        }

        try {
            if (count($queryBooking) != 0) {
                $bookingsModel->insert($queryBooking);
                return $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
            }
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }



    public function getBookings($fecha)
    {
        $bookingsModel = new BookingsModel();
        $fieldsModel = new FieldsModel();
        $timeModel = new TimeModel();

        $time = $timeModel->getOpeningTime();

        if ($fecha != '') {
            $bookings = $bookingsModel->where('date', $fecha)->where('annulled', 0)->findAll();
        }

        $timeBookings = [];

        foreach ($bookings as $booking) {
            $found = false;

            foreach ($timeBookings as &$timeBooking) {
                if (intval($timeBooking['id_cancha']) === intval($booking['id_field'])) {
                    $indexFrom = array_search($booking['time_from'], $time);
                    $indexUntil = array_search($booking['time_until'], $time);

                    for ($currentTime = $indexFrom; $currentTime <= $indexUntil; $currentTime++) {
                        $timeBooking['time'][] = strval(sprintf("%02d", $time[$currentTime]));
                    }
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $reserva = [
                    'id_cancha' => $booking['id_field'],
                    'nombre_cancha' => $fieldsModel->getField($booking['id_field'])['name'],
                    'time' => [],
                ];

                $indexFrom = array_search($booking['time_from'], $time);
                $indexUntil = array_search($booking['time_until'], $time);

                for ($currentTime = $indexFrom; $currentTime <= $indexUntil; $currentTime++) {
                    $reserva['time'][] = strval(sprintf("%02d", $time[$currentTime]));
                }

                $timeBookings[] = $reserva;
            }
        }

        if ($bookings) {
            try {
                return $this->response->setJSON($this->setResponse(null, null, $timeBookings, 'Respuesta exitosa'));
            } catch (\Exception $e) {
                return $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
            }
        }
    }


    public function completePayment($id)
    {
        $bookingsModel = new BookingsModel();
        $paymentsModel = new PaymentsModel();
        $data = $this->request->getJSON();
        $booking = $bookingsModel->getBooking($id);

        // log_message('info', 'Datos recibidos: ' . print_r($idUser, true));

        $pagoTotal =  $data->pago + $booking['payment'] == $booking['total'] ? 1 : 0;

        $queryBookings = [
            'total_payment' => $pagoTotal,
            'payment' => $booking['payment'] + $data->pago,
            'diference' => $booking['total'] - ($booking['payment'] + $data->pago),
        ];

        $queryPayments = [
            'id_user' => $data->idUser,
            'id_booking' => $id,
            'id_customer' => $data->idCustomer,
            'amount' => $data->pago,
            'payment_method' => $data->medioPago,
            'date' => Time::now()->toDateString(),
            'created_at' => Time::now(),
        ];

        try {
            $bookingsModel->update($id, $queryBookings);
            $paymentsModel->insert($queryPayments);
            return  $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }


    public function getBooking($id)
    {
        $bookingsModel = new BookingsModel();
        $booking = $bookingsModel->getBooking($id);

        if ($booking) {
            try {
                return  $this->response->setJSON($this->setResponse(null, null, $booking, 'Respuesta exitosa'));
            } catch (\Exception $e) {
                return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
            }
        }
    }

    public function getReports()
    {
        $paymentsModel = new PaymentsModel();
        $data = $this->request->getJSON();

        // 1. Limpieza básica de filtros
        $user = (empty($data->user) || $data->user == '') ? 'all' : $data->user;

        // 2. Consulta con JOINs para traer datos de Usuario y Cliente de un solo golpe
        $query = $paymentsModel->select('
            payments.date, 
            payments.amount, 
            payments.id_user, 
            payments.payment_method, 
            payments.id_mercado_pago,
            users.user as nombre_usuario, 
            customers.name as nombre_cliente, 
            customers.phone as telefono_cliente
        ')
            ->join('users', 'users.id = payments.id_user', 'left')
            ->join('customers', 'customers.id = payments.id_customer', 'left')
            ->where('payments.date >=', $data->fechaDesde)
            ->where('payments.date <=', $data->fechaHasta);

        if ($user !== 'all') {
            $query->where('payments.id_user', $user);
        }

        $paymentsResult = $query->findAll();

        // 3. Formateo de salida (mucho más ligero)
        $payments = array_map(function ($p) {
            return [
                'fecha'           => date("d/m/Y", strtotime($p['date'])),
                'pago'            => $p['amount'],
                'usuario'         => $p['nombre_usuario'] ?? 'N/A',
                'idUsuario'       => $p['id_user'],
                'cliente'         => $p['nombre_cliente'] ?? 'N/A',
                'telefonoCliente' => $p['telefono_cliente'] ?? 'N/A',
                'metodoPago'      => $p['payment_method'],
                'idMercadoPago'   => $p['id_mercado_pago'],
            ];
        }, $paymentsResult);

        // 4. Respuesta
        try {
            return $this->response->setJSON($this->setResponse(null, null, $payments, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }

    public function cancelBooking()
    {
        $mercadoPagoModel = new MercadoPagoModel();
        $bookingsModel = new BookingsModel();
        $data = $this->request->getJSON();
        $idBooking = $data->idBooking;
        $mpPayment = $mercadoPagoModel->where('id_booking', $idBooking)->first();

        try {
            if (isset($mpPayment)) {
                $mercadoPagoModel->update($mpPayment['id'], ['annulled' => 1]);
            }
            $bookingsModel->update($idBooking, ['annulled' => 1]);

            return  $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function editBooking()
    {
        $bookingsModel = new BookingsModel();
        $data = $this->request->getJSON();
        $idBooking = $data->bookingId;

        $queryUpdate = [
            'id_field' => $data->cancha,
            'diference' => $data->diferencia,
            'date' => $data->fecha,
            'time_from' => $data->horarioDesde,
            'time_until' => $data->horarioHasta,
            'total_payment' => $data->pagoTotal,
            'parcial' => $data->parcial,
            'total' => $data->total,
        ];

        try {
            $bookingsModel->update($idBooking, $queryUpdate);

            return  $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function getMpPayments()
    {
        $bookingsModel = new BookingsModel();
        $data = $this->request->getJSON();

        $bookings = $bookingsModel->where('date >=', $data->fechaDesde)
            ->where('date <=', $data->fechaHasta)
            ->findAll();

        $reservations = [];

        foreach ($bookings as $booking) {
            $fecha = date("d/m/Y", strtotime($booking['date']));
            $reservation = intval($booking['reservation']);

            if (array_key_exists($fecha, $reservations)) {
                $reservations[$fecha] += $reservation;
            } else {
                $reservations[$fecha] = $reservation;
            }
        }

        $result = [];

        foreach ($reservations as $fecha => $pago) {
            $result[] = [
                'fecha' => $fecha,
                'reserva' => $pago
            ];
        }

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $result, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function confirmMP()
    {
        $bookingsModel = new BookingsModel();
        $data = $this->request->getJSON();

        try {
            $bookingsModel->update($data->bookingId, ['mp' => $data->confirm]);

            return $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function saveAdminBooking()
    {
        $bookingsModel = new BookingsModel();
        $data = $this->request->getJSON();
        $pagoTotal = $data->monto == $data->total ? 1 : 0;

        $queryBooking = [
            'date'            => $data->fecha,
            'id_field'        => $data->cancha,
            'time_from'       => $data->horarioDesde,
            'time_until'      => $data->horarioHasta,
            'name'            => $data->nombre,
            'phone'           => $data->codigoArea . $data->telefono,
            'payment'         => $data->monto,
            'total'           => $data->total,
            'description'     => $data->descripcion,
            'diference'       => $data->total - $data->monto,
            'total_payment'   => $pagoTotal,
            'payment_method'  => $data->metodoDePago,
            'approved'        => 1,
            'mp'              => 1,
            'annulled'        => 0,
        ];

        try {
            $bookingsModel->insert($queryBooking);

            return  $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function bookingPdf($bookingId)
    {
        $pdfLibrary = new PrintBookings();
        $bookingsModel = new BookingsModel();
        $mercadoPagoModel = new MercadoPagoModel();
        $fieldsModel = new FieldsModel();

        $booking = $bookingsModel->getBooking($bookingId);
        $mpPayment = $mercadoPagoModel->where('id_booking', $bookingId)->first();

        //Generar PDF
        $printData = [
            'nombre' => $booking['name'],
            'telefono' => $booking['phone'],
            'fecha' => $booking['date'],
            'horario' => $booking['time_from'] . 'hs' . ' a ' . $booking['time_until'] . 'hs',
            'cancha' => $fieldsModel->getField($booking['id_field'])['name'],
            'id_mercado_pago' => $mpPayment['payment_id'],
            'estado_pago' => $mpPayment['status'],
            'total_cancha' => '$' . $booking['total'],
            'pagado' => '$' . $booking['payment'],
            'saldo' => '$' . $booking['diference'],
            'detalle' => $booking['description'],
        ];

        $pdfLibrary->printBooking($printData);
    }

    public function generateReportPdf($user, $fechaDesde, $fechaHasta)
    {
        $usersModel = new UsersModel();
        $paymentsModel = new PaymentsModel();
        $customersModel = new CustomersModel();
        $pdfLibrary = new PrintBookings();

        $query = $paymentsModel->select('*')
            ->where('date >=', $fechaDesde)
            ->where('date <=', $fechaHasta);

        if ($user !== 'all') {
            $query->where('id_user', $user);
        }

        $paymentsResult = $query->findAll();

        $payments = [];

        foreach ($paymentsResult as $payment) {
            $pago = [
                'fecha' => date("d/m/Y", strtotime($payment['date'])),
                'pago' => $payment['amount'],
                'usuario' => $usersModel->getUserName($payment['id_user']) || 'No informado',
                'idUsuario' => $payment['id_user'],
                'cliente' => $customersModel->getCustomerName($payment['id_customer']),
                'telefonoCliente' => $customersModel->getCustomerPhone($payment['id_customer']),
                'metodoPago' => $payment['payment_method'],
                'idMercadoPago' => $payment['id_mercado_pago'],
            ];

            array_push($payments, $pago);
        }

        $pdfLibrary->printReports($payments);
    }

    public function generatePaymentsReportPdf($fechaDesde, $fechaHasta)
    {
        $bookingsModel = new BookingsModel();
        $pdfLibrary = new PrintBookings();

        $bookings = $bookingsModel->where('date >=', $fechaDesde)
            ->where('date <=', $fechaHasta)
            ->findAll();

        $reservations = [];

        foreach ($bookings as $booking) {
            $fecha = date("d/m/Y", strtotime($booking['date']));
            $reservation = intval($booking['reservation']);

            if (array_key_exists($fecha, $reservations)) {
                $reservations[$fecha] += $reservation;
            } else {
                $reservations[$fecha] = $reservation;
            }
        }

        $result = [];

        foreach ($reservations as $fecha => $pago) {
            $result[] = [
                'fecha' => $fecha,
                'reserva' => $pago
            ];
        }

        $pdfLibrary->printPaymentsReports($result);
    }


    public function setResponse($code = 200, $error = false, $data = null, $message = '')
    {
        $response = [
            'error' => $error,
            'code' => $code,
            'data' => $data,
            'message' => $message,
        ];

        return $response;
    }
}
