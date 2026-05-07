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
    private const STATUS_PENDING = 'pending';
    private const STATUS_SENT = 'sent';

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
        $this->prepare();
    }

    public function prepare()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $validation = $this->validateSelection($_POST['service_id'] ?? null, $_POST['client_id'] ?? null);
        $finalMessage = trim((string) ($_POST['final_message'] ?? ''));

        $success = false;
        $statusCode = 422;
        $payload = ['message' => 'No se pudo preparar el envío por WhatsApp.'];

        if (!$validation['ok']) {
            $payload['message'] = $validation['error'];
        } elseif ($finalMessage === '') {
            $payload['message'] = 'Debes ingresar el mensaje final antes de abrir WhatsApp.';
        } else {
            $service = $validation['service'];
            $serviceId = $service['id'] ?? null;

            $dispatchData = $this->buildDispatchData($validation['clients'], $finalMessage, $serviceId);
            $failedListText = $dispatchData['failed_list_text'];

            if ($dispatchData['success_count'] === 0) {
                $payload['message'] = "No se pudo abrir WhatsApp para los {$dispatchData['total_selected']} clientes seleccionados." . ($failedListText ? "\n\n{$failedListText}" : '');
            } else {
                $dispatchItem = $dispatchData['dispatch_items'][0] ?? null;

                if (!$dispatchItem) {
                    $statusCode = 500;
                    $payload['message'] = 'No se pudo generar el enlace de WhatsApp.';
                } else {
                    $success = true;
                    $statusCode = 200;
                    $payload = [
                        'message' => 'Chat generado correctamente para el cliente seleccionado.',
                        'url' => $dispatchItem['url'],
                        'notification_id' => $dispatchItem['notification_id']
                    ];
                }
            }
        }

        $this->respondJson($success, $payload, $statusCode);
    }

    public function confirm()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $requestValidation = $this->validateConfirmRequest($_POST);
        if (!$requestValidation['ok']) {
            $this->respondJson(false, ['message' => $requestValidation['message']], $requestValidation['status']);
        }

        $notificationId = $requestValidation['notification_id'];
        $wasSent = $requestValidation['was_sent'];

        $updated = $this->updateConfirmationStatus($notificationId, $wasSent);
        if (!$updated) {
            $this->respondJson(false, ['message' => 'No se pudo actualizar la notificación.'], 500);
        }

        $this->respondJson(true, [
            'message' => $wasSent
                ? 'Notificación marcada como enviada.'
                : 'Notificación marcada como pendiente de envío.'
        ]);
    }

    private function buildDispatchData(array $clients, string $finalMessage, $serviceId): array
    {
        $dispatchData = [
            'total_selected' => count($clients),
            'success_count' => 0,
            'error_count' => 0,
            'failed_messages' => [],
            'dispatch_items' => [],
            'failed_list_text' => '',
        ];

        foreach ($clients as $client) {
            $sendResult = $this->prepareClientDispatch($client, $finalMessage, $serviceId);

            if ($sendResult['success']) {
                $dispatchData['success_count']++;
                $dispatchData['dispatch_items'][] = $sendResult['dispatch'];
                continue;
            }

            $dispatchData['error_count']++;
            $dispatchData['failed_messages'][] = $sendResult['error'];
        }

        if (!empty($dispatchData['failed_messages'])) {
            $dispatchData['failed_list_text'] = "No enviados:\n- " . implode("\n- ", $dispatchData['failed_messages']);
        }

        return $dispatchData;
    }

    private function validateSelection($serviceId, $selectedClientId): array
    {
        $result = ['ok' => false, 'error' => 'No se recibió el servicio a enviar por WhatsApp.'];

        if (!$serviceId) {
            return $result;
        }

        $selectedClientId = (int) $selectedClientId;

        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            $result['error'] = 'Servicio no encontrado para envío por WhatsApp.';
        } elseif ($selectedClientId <= 0) {
            $result['error'] = 'Debes seleccionar un cliente para abrir WhatsApp.';
        } else {
            $client = $this->clientModel->find($selectedClientId);
            if (!$client) {
                $result['error'] = 'No se encontró un cliente válido para el envío.';
            } else {
                $result = ['ok' => true, 'service' => $service, 'clients' => [$client]];
            }
        }

        return $result;
    }

    private function prepareClientDispatch(array $client, string $messageTemplate = '', $serviceId = null): array
    {
        $clientName = trim($client['name'] ?? '') ?: 'Cliente';
        $clientId = $client['id'] ?? null;
        $rawPhone = (string) ($client['phone'] ?? '');
        $numberTo = preg_replace('/\D+/', '', $rawPhone);

        if (strlen($numberTo) === 11 && str_starts_with($numberTo, '51')) {
            $numberTo = substr($numberTo, 2);
        }

        $message = $this->buildMessage($clientName, $messageTemplate);

        if (!preg_match('/^\d{9}$/', $numberTo)) {
            $errorMsg = "El cliente {$clientName} no tiene un teléfono válido de 9 dígitos.";
            $this->registerFailedDispatch($clientId, $serviceId, $rawPhone, $message, $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }

        $dispatch = $this->whatsappModel->buildChatUrl($message, $numberTo);

        if (!($dispatch['success'] ?? false)) {
            $errorMsg = $dispatch['error'] ?? "No se pudo generar el enlace de WhatsApp para {$clientName}.";
            $this->registerFailedDispatch($clientId, $serviceId, $rawPhone, $message, $errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }

        $notificationData = [
            'client_id' => $clientId,
            'service_id' => $serviceId,
            'phone_number' => $dispatch['phone_number'],
            'message_content' => $message,
            'status' => self::STATUS_PENDING,
            'error_message' => null,
            'whatsapp_message_id' => null,
            'sent_at' => null
        ];

        $notificationId = null;

        if ($clientId) {
            $notificationId = $this->notificationModel->create($notificationData);
        }

        return [
            'success' => true,
            'dispatch' => [
                'client_name' => $clientName,
                'phone_number' => $dispatch['phone_number'],
                'message' => $message,
                'url' => $dispatch['url'],
                'notification_id' => $notificationId,
            ],
        ];
    }

    private function registerFailedDispatch($clientId, $serviceId, string $rawPhone, string $message, string $errorMsg): void
    {
        if (!$clientId) {
            return;
        }

        $this->notificationModel->create([
            'client_id' => $clientId,
            'service_id' => $serviceId,
            'phone_number' => $rawPhone !== '' ? $rawPhone : 'N/A',
            'message_content' => $message,
            'status' => 'failed',
            'error_message' => $errorMsg
        ]);
    }

    private function buildMessage(string $clientName, string $messageTemplate = ''): string
    {
        return str_replace('{cliente}', $clientName, $messageTemplate);
    }

    private function validateConfirmRequest(array $request): array
    {
        $result = [
            'ok' => false,
            'status' => 422,
            'message' => 'No se pudo validar la confirmación.'
        ];

        $notificationId = (int) ($request['notification_id'] ?? 0);
        $wasSentRaw = $request['was_sent'] ?? null;
        $wasSent = filter_var($wasSentRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($notificationId <= 0) {
            $result['message'] = 'Notificación inválida.';
        } elseif ($wasSent === null) {
            $result['message'] = 'Respuesta inválida de confirmación.';
        } elseif (!$this->notificationModel->find($notificationId)) {
            $result['status'] = 404;
            $result['message'] = 'No se encontró la notificación a confirmar.';
        } else {
            $result = [
                'ok' => true,
                'status' => 200,
                'message' => '',
                'notification_id' => $notificationId,
                'was_sent' => $wasSent
            ];
        }

        return $result;
    }

    private function updateConfirmationStatus(int $notificationId, bool $wasSent): bool
    {
        $status = $wasSent ? self::STATUS_SENT : self::STATUS_PENDING;

        return $this->notificationModel->update($notificationId, [
            'status' => $status,
            'error_message' => null,
            'sent_at' => $wasSent ? date('Y-m-d H:i:s') : null,
        ]);
    }

    private function respondJson(bool $success, array $payload = [], int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(array_merge(['success' => $success], $payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
