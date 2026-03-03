<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Client;
use App\Models\Whatsapp;
use App\Models\Service;
use App\Models\Notification;

class WhatsappController
{

    protected $whatsappModel;
    protected $serviceModel;
    protected $clientModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->whatsappModel = new Whatsapp();
        $this->serviceModel = new Service();
        $this->clientModel = new Client();
        $this->notificationModel = new Notification();
    }

    public function send()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();
        $redirectPath = '/services';
        $validation = $this->validateSelection($_POST['service_id'] ?? null, $_POST['client_ids'] ?? []);

        if (!$validation['ok']) {
            $_SESSION['error'] = $validation['error'];
            return redirect($redirectPath);
        }

        $service = $validation['service'];
        $clients = $validation['clients'];
        $finalMessage = trim($_POST['final_message'] ?? '');
        $serviceId = $service['id'] ?? null;

        $totalSelected = count($clients);
        $successCount = 0;
        $errorCount = 0;
        $failedMessages = [];

        foreach ($clients as $client) {
            $sendResult = $this->sendToClient($client, $finalMessage, $serviceId);

            if ($sendResult['success']) {
                $successCount++;
            } else {
                $errorCount++;
                $failedMessages[] = $sendResult['error'];
            }
        }

        $failedListText = '';
        if (!empty($failedMessages)) {
            $failedListText = "No enviados:\n- " . implode("\n- ", $failedMessages);
        }

        if ($successCount === 0) {
            $_SESSION['error'] = "No se pudo enviar WhatsApp a los {$totalSelected} clientes seleccionados." . ($failedListText ? "\n\n{$failedListText}" : '');
        } elseif ($errorCount > 0) {
            $_SESSION['success'] = "Envío parcial: {$successCount} enviado(s), {$errorCount} con error.";
            $_SESSION['error'] = $failedListText ?: 'Algunos mensajes no se pudieron enviar.';
        } else {
            $_SESSION['success'] = "Mensajes de WhatsApp enviados correctamente a {$successCount} cliente(s).";
        }

        return redirect($redirectPath);
    }

    private function validateSelection($serviceId, $selectedClientIds): array
    {
        $result = ['ok' => false, 'error' => 'No se recibió el servicio a enviar por WhatsApp.'];

        if (!$serviceId) {
            return $result;
        }

        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            $result['error'] = 'Servicio no encontrado para envío por WhatsApp.';
        } elseif (!is_array($selectedClientIds) || empty($selectedClientIds)) {
            $result['error'] = 'Debes seleccionar al menos un cliente para enviar WhatsApp.';
        } else {
            $clients = $this->clientModel->findByIds($selectedClientIds);
            if (empty($clients)) {
                $result['error'] = 'No se encontraron clientes válidos para el envío.';
            } else {
                $result = ['ok' => true, 'service' => $service, 'clients' => $clients];
            }
        }

        return $result;
    }

    private function sendToClient(array $client, string $messageTemplate = '', $serviceId = null): array
    {
        $clientName = trim($client['name'] ?? '') ?: 'Cliente';
        $clientId = $client['id'] ?? null;
        $numberTo = preg_replace('/\D+/', '', $client['phone'] ?? '');

        if (empty($numberTo)) {
            $errorMsg = "El cliente {$clientName} no tiene teléfono válido.";

            if ($clientId) {
                $this->notificationModel->create([
                    'client_id' => $clientId,
                    'service_id' => $serviceId,
                    'phone_number' => $client['phone'] ?? 'N/A',
                    'message_content' => $messageTemplate,
                    'status' => 'failed',
                    'error_message' => $errorMsg
                ]);
            }

            return ['success' => false, 'error' => $errorMsg];
        }

        $message = $this->buildMessage($clientName, $messageTemplate);
        $result = $this->whatsappModel->sendMessage($message, $numberTo);

        $notificationData = [
            'client_id' => $clientId,
            'service_id' => $serviceId,
            'phone_number' => '51' . $numberTo,
            'message_content' => $message,
            'status' => ($result['success'] ?? false) ? 'sent' : 'failed',
            'error_message' => ($result['success'] ?? false) ? null : ($result['error'] ?? 'Error desconocido'),
            'whatsapp_message_id' => $result['message_id'] ?? null,
            'sent_at' => ($result['success'] ?? false) ? date('Y-m-d H:i:s') : null
        ];

        if ($clientId) {
            $this->notificationModel->create($notificationData);
        }

        if ($result['success'] ?? false) {
            return ['success' => true, 'error' => null];
        }

        $statusCode = $result['status_code'] ?? 'N/A';
        $errorMessage = $result['error'] ?? 'Error desconocido al enviar mensaje.';

        return ['success' => false, 'error' => "{$clientName}: HTTP {$statusCode} - {$errorMessage}"];
    }

    private function buildMessage(string $clientName, string $messageTemplate = ''): string
    {
        return str_replace('{cliente}', $clientName, $messageTemplate);
    }
}
