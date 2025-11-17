<?php

namespace Src\Controllers;

use Src\Config\Database;
use Src\Helpers\Response;
use Src\Helpers\Jwt;
use PDO;

class AuthController extends BaseController
{
    public function login()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($input['email']) || empty($input['password'])) {
            return $this->error(422, 'Email & password required');
        }

        $db = Database::conn($this->cfg);
        $statement = $db->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ?');
        $statement->execute([$input['email']]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($input['password'], $user['password_hash'])) {
            return $this->error(401, 'Invalid credentials');
        }

        $payload = [
            'sub' => $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + 3600
        ];

        $token = Jwt::sign($payload, $this->cfg['app']['jwt_secret']);

        Response::json(['token' => $token]);
    }
}