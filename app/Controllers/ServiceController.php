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
        $search = trim($_GET['search'] ?? '');
        $services = $this->serviceModel->all($search !== '' ? $search : null);
        $clients = $this->clientModel->all($search !== '' ? $search : null);

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
        $hasWarranty = (int) ($_POST['has_warranty'] ?? 0) === 1 ? 1 : 0;
        $warrantyTimeBaseRaw = trim($_POST['warranty_time_base'] ?? '');
        $warrantyTimeBase = null;

        if (empty($name) || !is_numeric($price) || $price < 0) {
            $_SESSION['error'] = 'Por favor, complete los campos obligatorios correctamente.';
            return redirect(self::VIEW_CREATE_SERVICE);
        }

        if ($warrantyTimeBaseRaw !== '') {
            if (!ctype_digit($warrantyTimeBaseRaw) || (int) $warrantyTimeBaseRaw < 1) {
                $_SESSION['error'] = 'El tiempo base de garantía debe ser un número entero mayor a 0.';
                return redirect(self::VIEW_CREATE_SERVICE);
            }
            $warrantyTimeBase = (int) $warrantyTimeBaseRaw;
        }

        if ($hasWarranty === 0) {
            $warrantyTimeBase = null;
        }

        $this->serviceModel->create([
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'status' => $status,
            'has_warranty' => $hasWarranty,
            'warranty_time_base' => $warrantyTimeBase
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
        $hasWarranty = (int) ($_POST['has_warranty'] ?? 0) === 1 ? 1 : 0;
        $warrantyTimeBaseRaw = trim($_POST['warranty_time_base'] ?? '');
        $warrantyTimeBase = null;

        if (!$id || empty($name) || !is_numeric($price) || $price < 0) {
            $_SESSION['error'] = 'Por favor, complete los campos obligatorios correctamente.';
            return redirect(self::VIEW_EDIT_SERVICE . '?id=' . $id);
        }

        if ($warrantyTimeBaseRaw !== '') {
            if (!ctype_digit($warrantyTimeBaseRaw) || (int) $warrantyTimeBaseRaw < 1) {
                $_SESSION['error'] = 'El tiempo base de garantía debe ser un número entero mayor a 0.';
                return redirect(self::VIEW_EDIT_SERVICE . '?id=' . $id);
            }
            $warrantyTimeBase = (int) $warrantyTimeBaseRaw;
        }

        if ($hasWarranty === 0) {
            $warrantyTimeBase = null;
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
            'status' => $status,
            'has_warranty' => $hasWarranty,
            'warranty_time_base' => $warrantyTimeBase
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
