<?php

namespace App\Core;

class Whatsapp
{
    public static function connect()
    {
        $config = require __DIR__ . '/../../config/whatsapp.php';

        try {

            return [
                "url" => "https://graph.facebook.com/{$config['WA_API_VERSION']}/{$config['WA_PHONE_NUMBER_ID']}/messages",
                "token" => $config['WA_ACCESS_TOKEN']
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            http_response_code(500);
            require dirname(__DIR__, 2) . '/resources/views/errors/whatsapp.php';
            exit;
        }
    }
}
