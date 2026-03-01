<?php
return [
    'WA_PHONE_NUMBER_ID' => $_ENV['WA_PHONE_NUMBER_ID'] ?? getenv('WA_PHONE_NUMBER_ID'),
    'WA_ACCESS_TOKEN'    => $_ENV['WA_ACCESS_TOKEN'] ?? getenv('WA_ACCESS_TOKEN'), // permanente
    'WA_API_VERSION'     => 'v21.0',
    'WA_WEBHOOK_VERIFY'  => $_ENV['WA_WEBHOOK_VERIFY'] ?? getenv('WA_WEBHOOK_VERIFY'),
];
