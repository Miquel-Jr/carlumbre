<?php
if (!function_exists('view')) {
    function view(string $path, array $data = [])
    {
        extract($data);

        ob_start();
        require __DIR__ . "/../../resources/views/{$path}.php";
        $content = ob_get_clean();

        $headTags = [
            '<meta name="theme-color" content="#000000">',
            '<meta name="msapplication-navbutton-color" content="#000000">',
            '<meta name="apple-mobile-web-app-status-bar-style" content="black">',
            '<link rel="shortcut icon" href="/assets/carlumbre/Icon.jpeg">',
            '<link rel="icon" type="image/jpeg" href="/assets/carlumbre/Icon.jpeg">',
            '<link rel="apple-touch-icon" href="/assets/carlumbre/Icon.jpeg">',
        ];

        foreach ($headTags as $headTag) {
            if (stripos($content, $headTag) === false && stripos($content, '</head>') !== false) {
                $content = preg_replace('/<\/head>/i', "    {$headTag}\n</head>", $content, 1);
            }
        }

        echo $content;
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

if (!function_exists('firstAccessibleUrl')) {
    function firstAccessibleUrl(?string $default = '/dashboard'): string
    {
        $menuItems = menu();
        $firstItem = reset($menuItems);

        if (is_array($firstItem) && !empty($firstItem['url'])) {
            return (string) $firstItem['url'];
        }

        return $default ?? '/';
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

if (!function_exists('permissionLabels')) {
    function permissionLabels(): array
    {
        $path = __DIR__ . '/../../config/permissions.php';

        if (!file_exists($path)) {
            return [];
        }

        $labels = require $path;
        return is_array($labels) ? $labels : [];
    }
}

if (!function_exists('permissionLabel')) {
    function permissionLabel(string $permission): string
    {
        $labels = permissionLabels();

        if (isset($labels[$permission])) {
            return $labels[$permission];
        }

        $readable = str_replace('_', ' ', trim($permission));
        return ucfirst($readable);
    }
}
