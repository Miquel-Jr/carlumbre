<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    public static function connect()
    {
        $config = require __DIR__ . '/../../config/database.php';

        try{
        
            return new PDO(
                "{$config['driver']}:host={$config['host']};dbname={$config['database']};charset={$config['charset']}",
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

        }catch(PDOException $e){
            error_log($e->getMessage());
            http_response_code(500);
            require dirname(__DIR__, 2) . '/resources/views/errors/database.php';
            exit;
        }
    }
}