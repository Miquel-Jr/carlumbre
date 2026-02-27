<?php

namespace App\Middleware;

class AuthMiddleware
{
    const TIMEOUT = 60 * 10;

    public function handle()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /');
            exit;
        }

        if (isset($_SESSION['last_activity'])) {
            $inactivity = time() - $_SESSION['last_activity'];

            if ($inactivity > self::TIMEOUT) {
                session_unset();
                session_destroy();
                header('Location: /?timeout=1');
                exit;
            }
        }

        $_SESSION['last_activity'] = time();
    }
}
