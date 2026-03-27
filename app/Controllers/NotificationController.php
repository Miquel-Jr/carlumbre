<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Notification;
use App\Models\Client;
use App\Models\WarrantyValidity;
use App\Models\Whatsapp;

class NotificationController
{
    protected $notificationModel;
    protected $clientModel;
    protected $whatsappModel;
    protected $warrantyValidityModel;

    private const VIEW_NOTIFICATIONS = 'notifications/index';
    private const ROUTE_NOTIFICATIONS = '/notifications';

    public function __construct()
    {
        $this->notificationModel = new Notification();
        $this->clientModel = new Client();
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
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_notifications'))->handle();

        $notificationId = $_GET['id'] ?? null;
        if (!$notificationId) {
            $_SESSION['error'] = 'ID de notificación no proporcionado.';
        } elseif (!$notification = $this->notificationModel->find($notificationId)) {
            $_SESSION['error'] = 'Notificación no encontrada.';
        } else {
            $phoneNumber = preg_replace('/\D+/', '', $notification['phone_number']);
            if (empty($phoneNumber)) {
                $_SESSION['error'] = 'Número de teléfono inválido en la notificación.';
            } else {
                $result = $this->whatsappModel->sendMessage($notification['message_content'], $phoneNumber);

                if ($result['success'] ?? false) {
                    $this->notificationModel->update($notificationId, [
                        'status' => 'sent',
                        'error_message' => null,
                        'whatsapp_message_id' => $result['message_id'] ?? null,
                        'sent_at' => date('Y-m-d H:i:s')
                    ]);
                    $_SESSION['success'] = 'Mensaje reenviado correctamente.';
                } else {
                    $this->notificationModel->update($notificationId, [
                        'status' => 'failed',
                        'error_message' => $result['error'] ?? 'Error desconocido al reenviar.'
                    ]);
                    $_SESSION['error'] = 'No se pudo reenviar el mensaje: ' . ($result['error'] ?? 'Error desconocido.');
                }
            }
        }

        return redirect(self::ROUTE_NOTIFICATIONS);
    }

    public function updateMessage()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_notifications'))->handle();

        $notificationId = $_POST['id'] ?? null;
        $messageContent = trim((string) ($_POST['message_content'] ?? ''));

        if (!$notificationId) {
            $_SESSION['error'] = 'ID de notificación no proporcionado.';
            return redirect(self::ROUTE_NOTIFICATIONS);
        }

        $notification = $this->notificationModel->find($notificationId);
        if (!$notification) {
            $_SESSION['error'] = 'Notificación no encontrada.';
            return redirect(self::ROUTE_NOTIFICATIONS);
        }

        if ($messageContent === '') {
            $_SESSION['error'] = 'El mensaje no puede estar vacío.';
            return redirect(self::ROUTE_NOTIFICATIONS);
        }

        $this->notificationModel->update($notificationId, [
            'message_content' => $messageContent,
        ]);

        $_SESSION['success'] = 'Mensaje actualizado correctamente.';
        return redirect(self::ROUTE_NOTIFICATIONS);
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_notifications'))->handle();

        $notificationId = $_GET['id'] ?? null;
        if (!$notificationId) {
            $_SESSION['error'] = 'ID de notificación no proporcionado.';
        } elseif (!$this->notificationModel->find($notificationId)) {
            $_SESSION['error'] = 'Notificación no encontrada.';
        } else {
            $this->notificationModel->delete($notificationId);
            $_SESSION['success'] = 'Notificación eliminada correctamente.';
        }

        return redirect(self::ROUTE_NOTIFICATIONS);
    }
}
