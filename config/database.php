<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}
return [
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST'),
    'database' => $_ENV['DB_NAME'] ?? getenv('DB_NAME'),
    'username' => $_ENV['DB_USER'] ?? getenv('DB_USER'),
    'password' => $_ENV['DB_PASS'] ?? getenv('DB_PASS'),
    'charset' => 'utf8mb4'
];
