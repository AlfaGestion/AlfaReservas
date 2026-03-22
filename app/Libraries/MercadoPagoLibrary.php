<?php

namespace App\Libraries;

use App\Models\MercadoPagoKeysModel;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoLibrary
{
    public ?string $preferenceId = null;

    public function setPreference(string $bookingTitle, float $bookingAmount, int $quantity): void
    {
        $mpKeysModel = new MercadoPagoKeysModel();
        $mpKeys = $mpKeysModel->first();

        if (empty($mpKeys) || empty($mpKeys['access_token'])) {
            throw new \Exception('Mercado Pago Access Token no encontrado.');
        }

        if ($bookingAmount <= 0) {
            throw new \Exception('El monto de la reserva debe ser un valor positivo.');
        }

        $this->ensureCaBundle();
        MercadoPagoConfig::setAccessToken((string) $mpKeys['access_token']);

        try {
            $envBaseUrl = getenv('MP_BACK_URL_BASE');
            $appConfig = config('App');
            $baseUrl = rtrim($envBaseUrl ?: $appConfig->baseURL, '/') . '/';

            $request = [
                'items' => [[
                    'title' => $bookingTitle,
                    'quantity' => $quantity,
                    'unit_price' => (float) $bookingAmount,
                    'currency_id' => 'ARS',
                ]],
                'back_urls' => [
                    'success' => $baseUrl . 'payment/success',
                    'failure' => $baseUrl . 'payment/failure',
                ],
                'auto_return' => 'approved',
                'binary_mode' => true,
            ];

            $client = new PreferenceClient();
            $preference = $client->create($request);

            if (empty($preference->id)) {
                error_log('FALLO MP: Preference ID es NULL. Objeto completo: ' . print_r($preference, true));
                throw new \Exception('La API de Mercado Pago devolvio una preferencia invalida.');
            }

            $this->preferenceId = $preference->id;
        } catch (MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $apiContent = $apiResponse ? json_encode($apiResponse->getContent()) : 'sin detalle';

            throw new \Exception('Error API Mercado Pago: ' . $apiContent, 0, $e);
        } catch (\Exception $e) {
            throw new \Exception('Error al crear la preferencia de pago: ' . $e->getMessage(), 0, $e);
        }
    }

    private function ensureCaBundle(): void
    {
        $caFile = ini_get('curl.cainfo');
        if (!$caFile) {
            $caFile = ini_get('openssl.cafile');
        }
        if (!$caFile) {
            $candidate = 'C:\\php\\cacert.pem';
            if (is_file($candidate)) {
                $caFile = $candidate;
            }
        }

        if ($caFile && is_file($caFile)) {
            ini_set('curl.cainfo', $caFile);
            ini_set('openssl.cafile', $caFile);
            putenv("CURL_CA_BUNDLE={$caFile}");
            putenv("SSL_CERT_FILE={$caFile}");
        }
    }
}
