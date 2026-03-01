<?php

namespace App\Middleware;

class GuestMiddleware
{
    public function handle()
    {
        if (!empty($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }
    }
}
