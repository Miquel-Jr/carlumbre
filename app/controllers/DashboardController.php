<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;

class DashboardController
{
    public function index()
    {
        (new AuthMiddleware())->handle();
        (new PermissionMiddleware('view_dashboard'))->handle();

        $menu = getUserMenu();  // Genera menú dinámico

        return view('dashboard', ['menu' => $menu]);
    }
}
