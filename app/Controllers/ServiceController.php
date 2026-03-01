<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Service;

class ServiceController
{

    protected $serviceModel;

    public function __construct()
    {
        $this->serviceModel = new Service();
    }

    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $services = $this->serviceModel->all();

        return view('services/index', [
            'menu' => menu(),
            'services' => $services
        ]);
    }

    public function create()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();
        return view('services/create');
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
            return redirect('/services/create');
        }

        $this->serviceModel->create([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'status' => $status
        ]);

        $_SESSION['success'] = 'Servicio registrado correctamente.';
        return redirect('/services');
    }

    public function edit()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            return view('error/nopage');
        }

        $service = $this->serviceModel->find($id);
        if (!$service) {
            return view('error/nopage');
        }

        return view('services/edit', [
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
            return redirect('/services/edit?id=' . $id);
        }

        $service = $this->serviceModel->find($id);
        if (!$service) {
            $_SESSION['error'] = 'Servicio no encontrado.';
            return redirect('/services/edit?id=' . $id);
        }

        $this->serviceModel->update($id, [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'status' => $status
        ]);

        $_SESSION['success'] = 'Servicio actualizado correctamente.';
        return redirect('/services');
    }

    public function delete()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_services'))->handle();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            return view('error/nopage');
        }
        $this->serviceModel->delete($_GET['id'] ?? null);
        $_SESSION['success'] = 'Servicio eliminado correctamente.';
        return redirect('/services');
    }
}
