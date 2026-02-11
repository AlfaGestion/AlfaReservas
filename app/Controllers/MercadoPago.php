<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\MercadoPagoLibrary;
use App\Models\BookingSlotsModel;
use App\Models\BookingsModel;
use App\Models\CancelReservationsModel;
use App\Models\ConfigModel;
use App\Models\CustomersModel;
use App\Models\MercadoPagoModel;
use App\Models\RateModel;

class MercadoPago extends BaseController
{
    private function isClosedForDateField($date, $fieldId)
    {
        if (empty($date)) {
            return false;
        }
        $cancelModel = new CancelReservationsModel();
        $closures = $cancelModel->where('cancel_date', $date)->findAll();
        if (empty($closures)) {
            return false;
        }
        foreach ($closures as $c) {
            if (empty($c['field_id'])) {
                return true;
            }
            if (!empty($fieldId) && !empty($c['field_id']) && (int)$c['field_id'] === (int)$fieldId) {
                return true;
            }
        }
        return false;
    }

    private function sendBookingEmail($bookingId)
    {
        $configModel = new ConfigModel();
        $toRow = $configModel->where('clave', 'email_reservas')->first();
        $toEmail = $toRow['valor'] ?? '';
        if (!is_string($toEmail) || trim($toEmail) === '') {
            return;
        }

        $bookingsModel = new BookingsModel();
        $fieldsModel = new \App\Models\FieldsModel();
        $booking = $bookingsModel->getBooking($bookingId);
        if (!$booking) {
            return;
        }

        $fieldName = $fieldsModel->getField($booking['id_field'])['name'] ?? 'N/D';
        $fecha = $booking['date'] ? date('d/m/Y', strtotime($booking['date'])) : 'N/D';
        $horario = ($booking['time_from'] ?? '') . ' a ' . ($booking['time_until'] ?? '');
        $localidad = $booking['locality'] ?? '';

        $message = "Nueva reserva\n\n"
            . "Nombre: {$booking['name']}\n"
            . "Teléfono: {$booking['phone']}\n"
            . "Localidad: " . ($localidad !== '' ? $localidad : 'N/D') . "\n"
            . "Fecha: {$fecha}\n"
            . "Horario: {$horario}\n"
            . "Cancha: {$fieldName}\n";

        $email = \Config\Services::email();
        $caPath = ROOTPATH . 'cacert.pem';
        if (is_file($caPath)) {
            ini_set('openssl.cafile', $caPath);
            ini_set('openssl.capath', $caPath);
        }
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);
        $emailConfig = config('Email');
        $fromEmail = $emailConfig->fromEmail ?? '';
        $fromName = $emailConfig->fromName ?? 'Reservas';
        if (!is_string($fromEmail) || trim($fromEmail) === '') {
            $fromEmail = $toEmail;
        }

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($toEmail);
        $email->setSubject('Nueva reserva');
        $email->setMessage($message);

        if (!$email->send()) {
            log_message('error', 'No se pudo enviar email de reserva: ' . $email->printDebugger(['headers']));
        }
    }

    public function setPreference()
    {
        $rateModel = new RateModel();
        $rateRow = $rateModel->first();
        $data = $this->request->getJSON();
        $booking = $data->booking ?? null;
        $montoTotal = $data->amount ?? 0;
        $bookingSlotsModel = new BookingSlotsModel();

        if (!$rateRow || !isset($rateRow['value'])) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'No existe tasa de reserva configurada.'));
        }
        if (!$booking) {
            return $this->response->setJSON($this->setResponse(400, true, null, 'Faltan datos de la reserva.'));
        }

        $bookingDate = $booking->fecha ?? $booking['fecha'] ?? null;
        $bookingField = $booking->cancha ?? $booking['cancha'] ?? null;
        if ($this->isClosedForDateField($bookingDate, $bookingField)) {
            return $this->response->setJSON($this->setResponse(409, true, null, 'No se puede reservar: hay un cierre informado para esa fecha.'));
        }

        $rate = $rateRow['value'];
        $montoParcial = (floatval($montoTotal) * floatval($rate)) / 100;

        // Crear slot pendiente antes de generar la preferencia
        $slotData = [
            'date' => $booking->fecha ?? $booking['fecha'] ?? null,
            'id_field' => $booking->cancha ?? $booking['cancha'] ?? null,
            'time_from' => $booking->horarioDesde ?? $booking['horarioDesde'] ?? null,
            'time_until' => $booking->horarioHasta ?? $booking['horarioHasta'] ?? null,
            'status' => 'pending',
            'active' => 1,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $slotId = $bookingSlotsModel->insert($slotData, true);
        if (!$slotId) {
            return $this->response->setJSON($this->setResponse(409, true, null, 'El horario ya está ocupado o en proceso.'));
        }

        $mp = new MercadoPagoLibrary();
        $mp->setPreference('Pago total de cancha', $montoTotal, 1);
        $preferenceIdTotal = $mp->preferenceId;

        $mp = new MercadoPagoLibrary();
        $mp->setPreference('Reserva de cancha', $montoParcial, 1);
        $preferenceIdParcial = $mp->preferenceId;

        $preferences = [
            'preferenceIdTotal' => $preferenceIdTotal,
            'preferenceIdParcial' => $preferenceIdParcial,
        ];

        $bookingArr = json_decode(json_encode($booking), true);
        $bookingArr['preferenceIdParcial'] = $preferenceIdParcial;
        $bookingArr['preferenceIdTotal'] = $preferenceIdTotal;
        $bookingArr['slotId'] = $slotId;

        $intents = session()->get('mp_intents') ?? [];
        $intents[$preferenceIdParcial] = ['booking' => $bookingArr, 'paid_type' => 'parcial'];
        $intents[$preferenceIdTotal] = ['booking' => $bookingArr, 'paid_type' => 'total'];
        session()->set('mp_intents', $intents);

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $preferences, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function success()
    {
        $mercadoPagoModel = new MercadoPagoModel();
        $bookingsModel = new BookingsModel();
        $customersModel = new CustomersModel();
        $bookingSlotsModel = new BookingSlotsModel();

        $preferenceId = $this->request->getVar('preference_id');

        $existingBooking = null;
        $createdFromIntent = false;
        $booking = null;
        $mercadoPago = null;

        if (!empty($preferenceId)) {
            $paid = '';
            $approved = '';

            $data = [
                'collection_id' => $this->request->getVar('collection_id'),
                'collection_status' => $this->request->getVar('collection_status'),
                'payment_id' => $this->request->getVar('payment_id'),
                'status' => $this->request->getVar('status'),
                'external_reference' => $this->request->getVar('external_reference'),
                'payment_type' => $this->request->getVar('payment_type'),
                'merchant_order_id' => $this->request->getVar('merchant_order_id'),
                'preference_id' => $this->request->getVar('preference_id'),
                'site_id' => $this->request->getVar('site_id'),
                'processing_mode' => $this->request->getVar('processing_mode'),
                'merchant_account_id' => $this->request->getVar('merchant_account_id'),
            ];

            if ($data['status'] == 'approved') $approved = 1;

            $existingBooking = $bookingsModel->where('id_preference_parcial', $preferenceId)
                ->orWhere('id_preference_total', $preferenceId)
                ->first();

            if (!$existingBooking) {
                $intents = session()->get('mp_intents') ?? [];
                $intent = $intents[$preferenceId] ?? null;

                if ($intent) {
                    $bookingData = $intent['booking'];
                    $paidType = $intent['paid_type'] ?? 'parcial';

                    $paid = $paidType === 'total' ? $bookingData['total'] : $bookingData['parcial'];
                    $totalPayment = $paid == $bookingData['total'];

                    $phone = $bookingData['telefono'];
                    $customer = $customersModel->where('phone', $phone)->first();
                    if (!$customer) {
                        $customersModel->insert([
                            'name' => $bookingData['nombre'],
                            'phone' => $phone,
                            'offer' => 0,
                            'quantity' => 1,
                            'city' => $bookingData['localidad'] ?? null,
                        ]);
                        $customerId = $customersModel->getInsertID();
                    } else {
                        $customerId = $customer['id'];
                        $customersModel->update($customerId, [
                            'name' => $bookingData['nombre'],
                            'city' => $bookingData['localidad'] ?? null,
                        ]);
                        if (array_key_exists('quantity', $customer)) {
                            $customersModel->update($customerId, ['quantity' => $customer['quantity'] + 1]);
                        }
                    }

                    $existingBooking = $bookingsModel->where('date', $bookingData['fecha'])
                        ->where('id_field', $bookingData['cancha'])
                        ->where('time_from', $bookingData['horarioDesde'])
                        ->where('time_until', $bookingData['horarioHasta'])
                        ->where('annulled', 0)
                        ->first();

                    if (!$existingBooking) {
                        $slotId = $bookingData['slotId'] ?? null;
                        if (!$slotId) {
                            $slotData = [
                                'date' => $bookingData['fecha'],
                                'id_field' => $bookingData['cancha'],
                                'time_from' => $bookingData['horarioDesde'],
                                'time_until' => $bookingData['horarioHasta'],
                                'status' => 'confirmed',
                                'active' => 1,
                                'expires_at' => null,
                                'created_at' => date('Y-m-d H:i:s'),
                            ];

                            $slotId = $bookingSlotsModel->insert($slotData, true);
                            if (!$slotId) {
                                return view('mercadoPago/failure');
                            }
                        }

                        $bookingsModel->insert([
                            'date' => $bookingData['fecha'],
                            'id_field' => $bookingData['cancha'],
                            'time_from' => $bookingData['horarioDesde'],
                            'time_until' => $bookingData['horarioHasta'],
                            'name' => $bookingData['nombre'],
                            'phone' => $phone,
                            'locality' => $bookingData['localidad'] ?? null,
                            'payment' => $paid,
                            'approved' => 1,
                            'total' => $bookingData['total'],
                            'parcial' => $bookingData['parcial'],
                            'diference' => $bookingData['total'] - $paid,
                            'reservation' => $paid,
                            'total_payment' => $totalPayment,
                            'payment_method' => 'Mercado Pago',
                            'id_preference_parcial' => $bookingData['preferenceIdParcial'],
                            'id_preference_total' => $bookingData['preferenceIdTotal'],
                            'use_offer' => $bookingData['oferta'] ?? 0,
                            'booking_time' => date("Y-m-d H:i:s"),
                            'mp' => 1,
                            'annulled' => 0,
                            'id_customer' => $customerId,
                            'created_by_type' => 'CLIENTE',
                            'created_by_name' => 'CLIENTE',
                            'created_by_user_id' => null,
                        ]);
                        $bookingId = $bookingsModel->getInsertID();
                        $existingBooking = $bookingsModel->find($bookingId);
                        $bookingSlotsModel->update($slotId, [
                            'booking_id' => $bookingId,
                            'status' => 'confirmed',
                            'expires_at' => null,
                        ]);
                        $createdFromIntent = true;
                        $this->sendBookingEmail($bookingId);
                    }

                    unset($intents[$bookingData['preferenceIdParcial']]);
                    unset($intents[$bookingData['preferenceIdTotal']]);
                    session()->set('mp_intents', $intents);
                }
            }

            if (!$existingBooking) {
                return view('mercadoPago/failure');
            }

            if ($preferenceId == $existingBooking['id_preference_parcial']) {
                $paid = $existingBooking['parcial'];
            } else {
                $paid = $existingBooking['total'];
            }

            $total_payment = $paid == $existingBooking['total'];

        $customer = $customersModel->where('phone', $existingBooking['phone'])->first();
        $customerId = $customer ? $customer['id'] : null;
        if ($customerId) {
            $customersModel->update($customerId, [
                'name' => $existingBooking['name'],
                'city' => $existingBooking['locality'] ?? null,
            ]);
        }

            $queryBooking = [
                'mp' => 1,
                'total_payment' => $total_payment,
                'diference' => $existingBooking['total'] - $paid,
                'reservation' => $paid,
                'payment' => $paid,
                'approved' => 1
            ];
            if ($customerId !== null) {
                $queryBooking['id_customer'] = $customerId;
            }

            $bookingsModel->update($existingBooking['id'], $queryBooking);
            $bookingSlotsModel->where('booking_id', $existingBooking['id'])
                ->where('active', 1)
                ->set(['status' => 'confirmed', 'expires_at' => null])
                ->update();
            if (!$createdFromIntent && $customer && array_key_exists('quantity', $customer)) {
                $customersModel->update($customer['id'], ['quantity' => $customer['quantity'] + 1]);
            }

            $data['id_booking'] = $existingBooking['id'];
            $mercadoPagoModel->insert($data);

            $booking = $bookingsModel->find($existingBooking['id']);
            $mercadoPago =  $mercadoPagoModel->where('id_booking', $existingBooking['id'])->first();
        }

        if (!$existingBooking) {
            return view('mercadoPago/failure');
        }

        return view('mercadoPago/success', ['bookingId' => $existingBooking['id'], 'booking' => $booking, 'mercadoPago' => $mercadoPago]);
    }

    public function failure()
    {
        $mercadoPagoModel = new MercadoPagoModel();
        $bookingsModel = new BookingsModel();
        $bookingSlotsModel = new BookingSlotsModel();

        $preferenceId = $this->request->getVar('preference_id');


        if (!empty($preferenceId)) {
            $data = [
                'collection_id' => $this->request->getVar('collection_id'),
                'collection_status' => $this->request->getVar('collection_status'),
                'payment_id' => $this->request->getVar('payment_id'),
                'status' => $this->request->getVar('status'),
                'external_reference' => $this->request->getVar('external_reference'),
                'payment_type' => $this->request->getVar('payment_type'),
                'merchant_order_id' => $this->request->getVar('merchant_order_id'),
                'preference_id' => $this->request->getVar('preference_id'),
                'site_id' => $this->request->getVar('site_id'),
                'processing_mode' => $this->request->getVar('processing_mode'),
                'merchant_account_id' => $this->request->getVar('merchant_account_id'),
            ];

            $existingBooking = $bookingsModel->where('id_preference_parcial', $data['preference_id'])
                ->orWhere('id_preference_total', $data['preference_id'])
                ->first();

            if ($existingBooking) {
                if ($data['status'] != 'approved') {
                    // Si el usuario cierra el checkout o falla el pago, anulamos la reserva.
                    $bookingsModel->update($existingBooking['id'], ['approved' => 0, 'annulled' => 1]);
                    $bookingSlotsModel->where('booking_id', $existingBooking['id'])
                        ->where('active', 1)
                        ->set(['active' => 0, 'status' => 'expired'])
                        ->update();
                }
            }

            if ($existingBooking) {
                $data['id_booking'] = $existingBooking['id'];
                $mercadoPagoModel->insert($data);
            }
            if (!$existingBooking && $data['status'] != 'approved') {
                $intents = session()->get('mp_intents') ?? [];
                $intent = $intents[$data['preference_id']] ?? null;
                if ($intent && isset($intent['booking']['slotId'])) {
                    $bookingSlotsModel->update($intent['booking']['slotId'], ['active' => 0, 'status' => 'expired']);
                }
            }
        }

        return view('mercadoPago/failure');
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

    public function verPruebas()
    {
        return view('superadmin/reportes');
    }
}
