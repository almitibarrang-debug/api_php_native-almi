<?php
return [
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=apiphp;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
    ],
    'app' => [
        'env' => 'local',
        'debug' => true,
        'base_url' => 'https://localhost/api-php-native_william/public',
        'jwt_secret' => 'CHANGE_ME_to_a_long_random_secret_>=32_chars',
        'allowed_origins' => ['http://localhost:3000','http://localhost']
    ]
    ];