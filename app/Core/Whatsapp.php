<?php

namespace App\Core;

class Whatsapp
{
    public static function config(): array
    {
        $config = require __DIR__ . '/../../config/whatsapp.php';

        return [
            'business_phone' => preg_replace('/\D+/', '', (string) ($config['WA_BUSINESS_PHONE'] ?? '')),
            'country_code' => preg_replace('/\D+/', '', (string) ($config['WA_DEFAULT_COUNTRY_CODE'] ?? '51')) ?: '51',
        ];
    }
}
