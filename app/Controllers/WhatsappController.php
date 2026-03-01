<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Whatsapp;
use App\Models\Service;

class WhatsappController
{

    protected $whatsappModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->whatsappModel = new Whatsapp();
        $this->serviceModel = new Service();
    }

    public function send()
    {
        (new AuthMiddleware())->handle();
        // (new PermissionMiddleware('send_whatsapp'))->handle();

        $clientName = 'Cliente';
        $service = [
            'name' => 'Cambio de Aceite',
            'description' => 'Servicio de cambio de aceite para tu vehículo.',
            'price' => 49.99
        ];
        $additionalMessage = 'Recuerda traer tu vehículo a tiempo para un mejor servicio.';
        $phone = '+51910376043';

        $message = "¡Hola $clientName! 👋\n\nDesde CarLumbre queremos ofrecerte nuestro servicio de:\n\n🔧 $service[name]\n\n$service[description]\n\n💰 Precio: €$service[price]\n\n$additionalMessage\n\n¿Te interesa? Contactanos para agendar tu cita.\n\n📱 WhatsApp: $phone";
        $numberTo = '51910376043';

        $this->whatsappModel->sendMessage($message, $numberTo);

        $services = $this->serviceModel->all();

        $_SESSION['success'] = 'Mensaje de WhatsApp enviado correctamente.';
        return view('services/index', [
            'menu' => menu(),
            'services' => $services
        ]);
    }
}
