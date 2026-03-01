<?php

namespace App\Models;

use App\Core\Whatsapp as CoreWhatsapp;

class Whatsapp
{
    protected $url;
    protected $token;

    public function __construct()
    {
        $connection = CoreWhatsapp::connect();

        if (!isset($connection['url'], $connection['token'])) {
            throw new \Exception('Configuración de WhatsApp inválida.');
        }

        $this->url = $connection['url'];
        $this->token = $connection['token'];
    }

    public function sendMessage(string $message, string $numberTo): ?array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $numberTo,
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
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log("cURL Error: {$error}");
            return null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            error_log("WhatsApp API Error ({$httpCode}): {$response}");
            return null;
        }

        return json_decode($response, true);
    }
}
