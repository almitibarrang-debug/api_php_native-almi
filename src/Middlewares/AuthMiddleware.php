<?php

namespace Src\Middlewares;

use Src\Helpers\Response;
use Src\Helpers\Jwt;

class AuthMiddleware
{
    public static function user(array $cfg)
    {
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/Bearer\s+(.*)/', $authorizationHeader, $matches)) {
            Response::jsonError(401, 'Missing token');
        }

        $payload = Jwt::verify($matches[1], $cfg['app']['jwt_secret']);
        if (!$payload) {
            Response::jsonError(401, 'Invalid/expired token');
        }

        return $payload;
    }

    public static function admin(array $cfg)
    {
        $payload = self::user($cfg);

        if (($payload['role'] ?? 'user') !== 'admin') {
            Response::jsonError(403, 'Forbidden');
        }

        return $payload;
    }
}