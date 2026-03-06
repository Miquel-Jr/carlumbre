<?php

namespace App\Controllers;

use App\Core\Database;
use App\Middleware\GuestMiddleware;
use PDO;

class AuthController
{
    public function showLogin()
    {
        (new GuestMiddleware())->handle();
        return view('auth/login');
    }

    public function login()
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$_POST['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($_POST['password'], $user['password'])) {
            $_SESSION['error'] = 'Credenciales incorrectas';
            return redirect('/');
        }

        $permissions = $pdo->prepare('
            SELECT p.name 
            FROM permissions p
            JOIN role_permission rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ');

        $permissions->execute([$user['role_id']]);
        $permissions = $permissions->fetchAll(PDO::FETCH_COLUMN);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'role_id' => $user['role_id'],
            'permissions' => $permissions
        ];

        $targetUrl = firstAccessibleUrl('/dashboard');
        return redirect($targetUrl);
    }

    public function logout()
    {
        session_destroy();
        header('Location: /');
        exit;
    }
}
