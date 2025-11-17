<?php

namespace Src\Controllers;

use Src\Helpers\Response;
use Src\Helpers\Jwt;

class JwtController extends BaseController
{
    public function verify()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->error(401, 'Token not provided');
        }

        $token = substr($authHeader, 7);

        $decoded = Jwt::decode($token);
        if (!$decoded) {
            return $this->error(400, 'Invalid token format');
        }

        $payload = Jwt::verify($token, $this->cfg['app']['jwt_secret']);

        if (!$payload) {
            $isExpired = isset($decoded['payload']['exp']) && $decoded['payload']['exp'] < time();
            $message = $isExpired ? 'Token expired' : 'Invalid token';
            return $this->error(401, $message);
        }

        Response::json([
            'valid' => true,
            'payload' => $payload,
            'expires_at' => date('Y-m-d H:i:s', $payload['exp']),
            'current_time' => date('Y-m-d H:i:s'),
            'is_expired' => false
        ]);
    }

    public function check()
    {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true) ?? [];

        if (empty($input['token'])) {
            return $this->error(422, 'Token required');
        }

        $token = $input['token'];

        $decoded = Jwt::decode($token);
        if (!$decoded) {
            return $this->error(400, 'Invalid token format');
        }

        $payload = Jwt::verify($token, $this->cfg['app']['jwt_secret']);
        $isExpired = isset($decoded['payload']['exp']) && $decoded['payload']['exp'] < time();

        Response::json([
            'valid' => $payload !== false,
            'is_expired' => $isExpired,
            'payload' => $decoded['payload'],
            'expires_at' => isset($decoded['payload']['exp']) ? date('Y-m-d H:i:s', $decoded['payload']['exp']) : null,
            'current_time' => date('Y-m-d H:i:s'),
            'message' => $payload ? 'Token is valid' : ($isExpired ? 'Token expired' : 'Invalid token')
        ]);
    }
}