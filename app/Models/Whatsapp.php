<?php

namespace App\Models;

use App\Core\Whatsapp as CoreWhatsapp;

class WhatsappConfigurationException extends \Exception {}

class Whatsapp
{
    protected $url;
    protected $token;

    public function __construct()
    {
        $connection = CoreWhatsapp::connect();

        if (!isset($connection['url'], $connection['token'])) {
            throw new WhatsappConfigurationException('Configuración de WhatsApp inválida.');
        }

        $this->url = $connection['url'];
        $this->token = $connection['token'];
    }

    public function sendMessage(string $message, string $numberTo): array
    {
        if (empty($this->url) || empty($this->token)) {
            return [
                'success' => false,
                'status_code' => null,
                'error' => 'Configuración de WhatsApp incompleta (URL o token vacío).'
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => '51' . $numberTo,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];

        $ch = curl_init($this->url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer {$this->token}"
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            return $this->handleCurlError($ch);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $decodedResponse = json_decode($response, true);

        return $this->parseApiResponse($response, $decodedResponse, $httpCode);
    }

    private function handleCurlError($ch): array
    {
        $error = curl_error($ch);
        error_log("cURL Error: {$error}");
        return [
            'success' => false,
            'status_code' => null,
            'error' => "cURL Error: {$error}"
        ];
    }

    private function parseApiResponse($response, $decodedResponse, $httpCode): array
    {
        $result = [];

        if ($decodedResponse === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log("WhatsApp API invalid JSON response ({$httpCode}): {$response}");
            $result = [
                'success' => false,
                'status_code' => $httpCode,
                'error' => 'Respuesta inválida de la API de WhatsApp.',
                'raw_response' => $response
            ];
        } elseif ($httpCode >= 400) {
            error_log("WhatsApp API Error ({$httpCode}): {$response}");

            $apiError = $decodedResponse['error'] ?? [];
            $apiMessage = $apiError['message'] ?? 'Error al enviar mensaje por WhatsApp.';
            $apiCode = $apiError['code'] ?? null;
            $apiSubcode = $apiError['error_subcode'] ?? null;

            if ((int)$apiCode === 190 || (int)$apiSubcode === 463) {
                $apiMessage = 'Token de acceso de WhatsApp expirado o inválido.';
            }

            $result = [
                'success' => false,
                'status_code' => $httpCode,
                'error' => $apiMessage,
                'response' => $decodedResponse
            ];
        } elseif (!isset($decodedResponse['messages'][0]['id'])) {
            error_log("WhatsApp API response without message id ({$httpCode}): {$response}");
            $result = [
                'success' => false,
                'status_code' => $httpCode,
                'error' => 'La API no confirmó el envío del mensaje.',
                'response' => $decodedResponse
            ];
        } else {
            $result = [
                'success' => true,
                'status_code' => $httpCode,
                'message_id' => $decodedResponse['messages'][0]['id'],
                'response' => $decodedResponse
            ];
        }

        return $result;
    }
}
