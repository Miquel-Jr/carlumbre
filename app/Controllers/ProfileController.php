<?php

namespace App\Controllers;

use App\Core\Database;
use App\Middleware\AuthMiddleware;
use PDO;

class ProfileController
{
    const PROFILE_ROUTE = '/profile';

    public function index()
    {
        (new AuthMiddleware())->handle();

        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            $_SESSION['error'] = 'Sesión inválida. Inicia sesión nuevamente.';
            return redirect('/logout');
        }

        $db = Database::connect();
        $stmt = $db->prepare('SELECT id, name, email FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = 'No se encontró el usuario autenticado.';
            return redirect('/logout');
        }

        return view('profile/index', ['user' => $user]);
    }

    public function updatePassword()
    {
        (new AuthMiddleware())->handle();

        $userId = $_SESSION['user']['id'] ?? null;

        if (!$userId) {
            $_SESSION['error'] = 'Sesión inválida. Inicia sesión nuevamente.';
            return redirect('/logout');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!$currentPassword || !$newPassword || !$confirmPassword) {
            $_SESSION['error'] = 'Todos los campos de contraseña son obligatorios.';
            return redirect(self::PROFILE_ROUTE);
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
            return redirect(self::PROFILE_ROUTE);
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'La confirmación de contraseña no coincide.';
            return redirect(self::PROFILE_ROUTE);
        }

        $db = Database::connect();
        $stmt = $db->prepare('SELECT password FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = 'No se encontró el usuario autenticado.';
            return redirect('/logout');
        }

        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['error'] = 'La contraseña actual es incorrecta.';
            return redirect(self::PROFILE_ROUTE);
        }

        if (password_verify($newPassword, $user['password'])) {
            $_SESSION['error'] = 'La nueva contraseña no puede ser igual a la actual.';
            return redirect(self::PROFILE_ROUTE);
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateStmt = $db->prepare('UPDATE users SET password = :password WHERE id = :id');
        $updateStmt->execute([
            'password' => $newHash,
            'id' => $userId
        ]);

        $_SESSION['success'] = 'Contraseña actualizada correctamente.';
        return redirect(self::PROFILE_ROUTE);
    }
}
