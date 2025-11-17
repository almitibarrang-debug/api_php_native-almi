<?php

namespace Src\Middlewares;

class CorsMiddleware
{
    public static function handle(array $cfg): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        $allowedOrigins = $cfg['app']['allowed_origins'] ?? [];

        if ($allowedOrigins && in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Vary: Origin');
        } elseif (empty($allowedOrigins)) {
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    }
}