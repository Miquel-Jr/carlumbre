<?php

return [
    'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'] ?? getenv('CLOUDINARY_CLOUD_NAME'),
    'api_key' => $_ENV['CLOUDINARY_API_KEY'] ?? getenv('CLOUDINARY_API_KEY'),
    'api_secret' => $_ENV['CLOUDINARY_API_SECRET'] ?? getenv('CLOUDINARY_API_SECRET'),
];
