<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Notification;
use App\Models\WarrantyValidity;
use App\Models\Whatsapp;

class NotificationController
{
    private const ERROR_NOTIFICATION_ID_REQUIRED = 'ID de notificación no proporcionado.';
    private const ERROR_NOTIFICATION_NOT_FOUND = 'Notificación no encontrada.';

    protected $notificationModel;
    protected $whatsappModel;
    protected $warrantyValidityModel;

    private const VIEW_NOTIFICATIONS = 'notifications/index';
    private const ROUTE_NOTIFICATIONS = '/notifications';

    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->whatsappModel = new Whatsapp();
        $this->warrantyValidityModel = new WarrantyValidity();
    }

    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_notifications'))->handle();

        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;

        $this->warrantyValidityModel->createExpiredWarrantyReminders($this->notificationModel);

        $notifications = $this->notificationModel->all($search, $status);
        $statistics = $this->notificationModel->getStatistics();

        return view(self::VIEW_NOTIFICATIONS, [
            'menu' => menu(),
            'notifications' => $notifications,
            'statistics' => $statistics,
            'currentStatus' => $status
        ]);
    }

    public function resend()
    {
        return $this->prepareResend();
    }

    public function prepareResend()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_notifications'))->handle();

        $notificationId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $success = false;
        $statusCode = 422;
        $payload = ['message' => 'No se pudo preparar la notificación.'];

        if ($notificationId <= 0) {
            $payload['message'] = self::ERROR_NOTIFICATION_ID_REQUIRED;
        } elseif (!$notification = $this->notificationModel->find($notificationId)) {
            $payload['message'] = self::ERROR_NOTIFICATION_NOT_FOUND;
        } else {
            $phoneNumber = preg_replace('/\D+/', '', $notification['phone_number']);
            if (empty($phoneNumber)) {
                $payload['message'] = 'Número de teléfono inválido en la notificación.';
            } else {
                $dispatch = $this->whatsappModel->buildChatUrl($notification['message_content'], $phoneNumber);

                if ($dispatch['success'] ?? false) {
                    $this->notificationModel->update($notificationId, [
                        'status' => 'pending',
                        'error_message' => null,
                        'whatsapp_message_id' => null,
                        'sent_at' => null
                    ]);

                    $success = true;
                    $statusCode = 200;
                    $payload = [
                        'message' => 'Chat preparado correctamente.',
                        'url' => $dispatch['url'],
                        'notification_id' => $notificationId,
                    ];
                } else {
                    $this->notificationModel->update($notificationId, [
                        'status' => 'failed',
                        'error_message' => $dispatch['error'] ?? 'Error desconocido al abrir el chat.'
                    ]);
                    $payload['message'] = 'No se pudo abrir el chat: ' . ($dispatch['error'] ?? 'Error desconocido.');
                }
            }
        }

        if (isset($_POST['id']) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest')) {
            $this->respondJson($success, $payload, $statusCode);
        }

        $_SESSION[$success ? 'success' : 'error'] = $payload['message'];

        if ($success && !empty($payload['url'])) {
            return redirect($payload['url']);
        }

        return redirect(self::ROUTE_NOTIFICATIONS);
    }

    public function confirmDelivery()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_notifications'))->handle();

        $notificationId = (int) ($_POST['notification_id'] ?? 0);
        $wasSent = filter_var($_POST['was_sent'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $success = false;
        $statusCode = 422;
        $payload = ['message' => 'No se pudo confirmar la notificación.'];

        if ($notificationId <= 0) {
            $payload['message'] = self::ERROR_NOTIFICATION_ID_REQUIRED;
        } elseif ($wasSent === null) {
            $payload['message'] = 'Respuesta inválida de confirmación.';
        } elseif (!$this->notificationModel->find($notificationId)) {
            $statusCode = 404;
            $payload['message'] = self::ERROR_NOTIFICATION_NOT_FOUND;
        } else {
            $updated = $this->notificationModel->update($notificationId, [
                'status' => $wasSent ? 'sent' : 'pending',
                'error_message' => null,
                'sent_at' => $wasSent ? date('Y-m-d H:i:s') : null,
            ]);

            if (!$updated) {
                $statusCode = 500;
                $payload['message'] = 'No se pudo actualizar la notificación.';
            } else {
                $success = true;
                $statusCode = 200;
                $payload['message'] = $wasSent
                    ? 'Notificación marcada como enviada.'
                    : 'Notificación marcada como pendiente de envío.';
            }
        }

        $this->respondJson($success, $payload, $statusCode);
    }

    public function updateMessage()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_notifications'))->handle();

        $notificationId = $_POST['id'] ?? null;
        $messageContent = trim((string) ($_POST['message_content'] ?? ''));

        if (!$notificationId) {
            $_SESSION['error'] = self::ERROR_NOTIFICATION_ID_REQUIRED;
        } elseif (!$notification = $this->notificationModel->find($notificationId)) {
            $_SESSION['error'] = self::ERROR_NOTIFICATION_NOT_FOUND;
        } elseif (in_array($notification['status'] ?? '', ['sent', 'opened'], true)) {
            $_SESSION['error'] = 'No se puede editar el mensaje de una notificación enviada.';
        } elseif ($messageContent === '') {
            $_SESSION['error'] = 'El mensaje no puede estar vacío.';
        } else {
            $this->notificationModel->update($notificationId, [
                'message_content' => $messageContent,
            ]);

            $_SESSION['success'] = 'Mensaje actualizado correctamente.';
        }

        return redirect(self::ROUTE_NOTIFICATIONS);
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_notifications'))->handle();

        $notificationId = $_GET['id'] ?? null;
        if (!$notificationId) {
            $_SESSION['error'] = self::ERROR_NOTIFICATION_ID_REQUIRED;
        } elseif (!$this->notificationModel->find($notificationId)) {
            $_SESSION['error'] = self::ERROR_NOTIFICATION_NOT_FOUND;
        } else {
            $this->notificationModel->delete($notificationId);
            $_SESSION['success'] = 'Notificación eliminada correctamente.';
        }

        return redirect(self::ROUTE_NOTIFICATIONS);
    }

    private function respondJson(bool $success, array $payload = [], int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(array_merge(['success' => $success], $payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
