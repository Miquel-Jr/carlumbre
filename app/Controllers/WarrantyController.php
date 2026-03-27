<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\WarrantyValidity;

class WarrantyController
{
    private WarrantyValidity $warrantyModel;

    public function __construct()
    {
        $this->warrantyModel = new WarrantyValidity();
    }

    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_billing'))->handle();

        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';

        $warranties = $this->warrantyModel->all($search !== '' ? $search : null, $status !== '' ? $status : null);

        return view('warranties/index', [
            'warranties' => $warranties,
            'currentStatus' => $status,
        ]);
    }
}
