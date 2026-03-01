<?php
if (!function_exists('view')) {
    function view(string $path, array $data = [])
    {
        extract($data);
        require __DIR__ . "/../../resources/views/{$path}.php";
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url)
    {
        header("Location: {$url}");
        exit;
    }
}

// Devuelve los items de menú que el usuario tiene permiso de ver
if (!function_exists('menu')) {
    function menu(): array
    {
        $allMenu = require __DIR__ . '/../../config/menu.php';
        $userPermissions = $_SESSION['user']['permissions'] ?? [];

        return array_filter($allMenu, function ($item) use ($userPermissions) {
            return in_array($item['permission'], $userPermissions);
        });
    }
}

// Devuelve true si la ruta actual coincide con la URL del item
if (!function_exists('isActive')) {
    function isActive(string $url): bool
    {
        $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return $current === $url;
    }
}
