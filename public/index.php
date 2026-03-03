<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

use Dotenv\Dotenv;

// 🔹 Solo cargar .env si existe (local)
$envPath = dirname(__DIR__) . '/.env';

if (file_exists($envPath)) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

use App\Core\Router;

$router = new Router();

require_once dirname(__DIR__) . '/routes/web.php';

$router->resolve();
