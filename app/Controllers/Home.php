<?php

namespace App\Controllers;

use App\Models\BookingsModel;
use App\Models\CustomersModel;
use App\Models\FieldsModel;
use App\Models\MercadoPagoModel;
use App\Models\OffersModel;
use App\Models\TimeModel;
use DateInterval;
use DateTime;

class Home extends BaseController
{
    public function index()
    {
        $offersModel = new OffersModel();

        $currentDate = date("Y-m-d");
        $existingOffer = $offersModel->first();

        if (isset($existingOffer)) {


            if ($existingOffer['expiration_date'] == $currentDate) {
                $update = [
                    'value' => 0,
                    'description' => '',
                    'expiration_date' => '',
                ];

                $offersModel->update($existingOffer['id'], $update);
            }
        }

        $oferta = $offersModel->first();

        $fieldsModel = new FieldsModel();
        $fields = $fieldsModel->where('disabled', 0)->findAll();

        // dd($fields);

        $timeModel = new TimeModel();
        $openingTime = $timeModel->getOpeningTime();
        $isSunday = $timeModel->first()['is_sunday'];


        // $time = [];

        // if ($openingTime) {
        //     if ($openingTime[0]['from']) {
        //         $from = $openingTime[0]['from'] - 1;
        //         $until = $openingTime[0]['until'];

        //         while ($from != $until) {
        //             $from++;
        //             if($from == '24') $from = '00';

        //             array_push($time, $from);
        //         }
        //     }
        // }

        // if ($openingTime) {
        //     if ($openingTime[0]['from_cut']) {
        //         $from_cut = $openingTime[0]['from_cut'] - 1;
        //         $until_cut = $openingTime[0]['until_cut'];

        //         while ($from_cut != $until_cut) {
        //             $from_cut++;
        //             array_push($time, $from_cut);
        //         }
        //     }
        // }

        return view('index', ['fields' => $fields, 'time' => $openingTime, 'oferta' => $oferta, 'esDomingo' => $isSunday]);
    }

    // public function deleteRejected()
    // {
    //     $bookingsModel = new BookingsModel();
    //     $mercadoPagoModel = new MercadoPagoModel();

    //     try {
    //         $mercadoPagoModel->where('status', 'rejected')->delete();
    //         $bookingsModel->where('approved', 0)
    //             ->orWhere('approved', null)->delete();
    //         return  $this->response->setJSON($this->setResponse(null, null, null, 'Respuesta exitosa'));
    //     } catch (\Exception $e) {
    //         return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
    //     }
    // }

    public function deleteRejected()
    {
        $bookingsModel = new BookingsModel();
        $mercadoPagoModel = new MercadoPagoModel();
        // Necesitas cargar el modelo de la tabla que causa el error
        $paymentsModel = new \App\Models\PaymentsModel();

        $nueva_hora = date("Y-m-d H:i:s", strtotime("-5 minutes"));

        try {
            // 1. Buscamos las reservas candidatas
            $bookingsCandidates = $bookingsModel->groupStart()
                ->where('approved', 0)
                ->orWhere('approved', null)
                ->groupEnd()
                ->where('booking_time <', $nueva_hora)
                ->findAll();

            if (empty($bookingsCandidates)) {
                return $this->response->setJSON($this->setResponse(null, null, null, 'Sin registros para limpiar'));
            }

            $idsToDelete = [];
            foreach ($bookingsCandidates as $booking) {
                // Verificamos en MP si hay pago aprobado para no borrar por error
                $hasApproved = $mercadoPagoModel->where('id_booking', $booking['id'])
                    ->where('status', 'approved')
                    ->first();
                if (!$hasApproved) {
                    $idsToDelete[] = $booking['id'];
                }
            }

            if (!empty($idsToDelete)) {
                // ORDEN DE BORRADO (Hijos primero, Padre al final)

                // 1. Borrar de la tabla 'payments' (la que dio el error ahora)
                $paymentsModel->whereIn('id_booking', $idsToDelete)->delete();

                // 2. Borrar de la tabla 'mercado_pago'
                $mercadoPagoModel->whereIn('id_booking', $idsToDelete)->delete();

                // 3. Finalmente borrar la reserva 'bookings'
                $bookingsModel->delete($idsToDelete);
            }

            return $this->response->setJSON($this->setResponse(null, null, null, 'Limpieza exitosa de todas las tablas'));
        } catch (\Exception $e) {
            // Devuelve el error detallado para seguir debugueando si falta otra tabla
            return $this->response->setJSON($this->setResponse(500, true, null, $e->getMessage()));
        }
    }


    public function infoReserva()
    {
        $data = $this->request->getJSON();
        $fieldsModel = new FieldsModel();

        $datosReserva = [
            'fecha'        => $data->fecha,
            'cancha'       => $fieldsModel->getField($data->cancha)['name'],
            'horarioDesde' => $data->horarioDesde,
            'horarioHasta' => $data->horarioHasta,
            'nombre'       => $data->nombre,
            'telefono'     => $data->telefono,
            'codigoArea'   => $data->codigoArea,
        ];

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $datosReserva, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
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
