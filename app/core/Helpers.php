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

if (!function_exists('getUserMenu')) {
  function getUserMenu(): array
  {
    $menu = require __DIR__ . '/../../config/menu.php';
    $userPermissions = $_SESSION['user']['permissions'] ?? [];

    // Filtra solo las opciones que el usuario puede ver
    return array_filter($menu, function ($item) use ($userPermissions) {
      return in_array($item['permission'], $userPermissions);
    });
  }
}
