<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Client;
use App\Models\Service;

class ServiceController
{
    private const ERROR_NOPAGE_VIEW = 'errors/nopage';
    private const VIEW_SERVICES = 'services/index';
    private const VIEW_CREATE_SERVICE = 'services/create';
    private const VIEW_EDIT_SERVICE = 'services/edit';
    private const ROUTE_SERVICES = '/services';

    protected $serviceModel;
    protected $clientModel;

    public function __construct()
    {
        $this->serviceModel = new Service();
        $this->clientModel = new Client();
    }

    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $services = $this->serviceModel->all();
        $clients = $this->clientModel->all();

        return view(self::VIEW_SERVICES, [
            'menu' => menu(),
            'services' => $services,
            'clients' => $clients
        ]);
    }

    public function create()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();
        return view(self::VIEW_CREATE_SERVICE);
    }

    public function store()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $status = $_POST['status'] ?? 1;

        if (empty($name) || !is_numeric($price) || $price < 0) {
            $_SESSION['error'] = 'Por favor, complete los campos obligatorios correctamente.';
            return redirect(self::VIEW_CREATE_SERVICE);
        }

        $this->serviceModel->create([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'status' => $status
        ]);

        $_SESSION['success'] = 'Servicio registrado correctamente.';
        return redirect(self::ROUTE_SERVICES);
    }

    public function edit()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            return view(self::ERROR_NOPAGE_VIEW);
        }

        $service = $this->serviceModel->find($id);
        if (!$service) {
            return view(self::ERROR_NOPAGE_VIEW);
        }

        return view(self::VIEW_EDIT_SERVICE, [
            'service' => $service
        ]);
    }

    public function update()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $status = $_POST['status'] ?? 1;

        if (!$id || empty($name) || !is_numeric($price) || $price < 0) {
            $_SESSION['error'] = 'Por favor, complete los campos obligatorios correctamente.';
            return redirect(self::VIEW_EDIT_SERVICE . '?id=' . $id);
        }

        $service = $this->serviceModel->find($id);
        if (!$service) {
            $_SESSION['error'] = 'Servicio no encontrado.';
            return redirect(self::VIEW_EDIT_SERVICE . '?id=' . $id);
        }

        $this->serviceModel->update($id, [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'status' => $status
        ]);

        $_SESSION['success'] = 'Servicio actualizado correctamente.';
        return redirect(self::ROUTE_SERVICES);
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            return view(self::ERROR_NOPAGE_VIEW);
        }
        $this->serviceModel->delete($_GET['id'] ?? null);
        $_SESSION['success'] = 'Servicio eliminado correctamente.';
        return redirect(self::ROUTE_SERVICES);
    }
}
