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

        return view('dashboard');
    }
}
