<?php

spl_autoload_register(function ($c) {
    $p = __DIR__ . '/../';
    $c = str_replace('\\', '/', $c);
    $paths = ["$p/src/$c.php", "$p/$c.php"];
    foreach ($paths as $f) {
        if (file_exists($f)) {
            require $f;
        }
    }
});

$cfg = require __DIR__ . '/../config/env.php';

use Src\Helpers\Response;
use Src\Middlewares\CorsMiddleware;

// CORS preflight
CorsMiddleware::handle($cfg);
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// === RATE LIMITING ===
$ip = $_SERVER['REMOTE_ADDR'];
$limit = 5;          // jumlah request
$window = 60;         // dalam detik (1 menit)
$storage = sys_get_temp_dir() . '/rate_limit_' . md5($ip);

// ambil data lama
$data = ['count' => 0, 'start' => time()];
if (file_exists($storage)) {
    $data = json_decode(file_get_contents($storage), true);
    if (time() - $data['start'] < $window) {
        $data['count']++;
    } else {
        // reset jendela waktu
        $data = ['count' => 1, 'start' => time()];
    }
} else {
    $data = ['count' => 1, 'start' => time()];
}

// simpan data
file_put_contents($storage, json_encode($data));

// jika melewati batas
if ($data['count'] > $limit) {
    header('Retry-After: ' . ($window - (time() - $data['start'])));
    Response::jsonError(429, 'Too Many Requests');
    exit;
}

// === ROUTING ===
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$base = dirname($_SERVER['SCRIPT_NAME']);
$path = '/' . trim(str_replace($base, '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Debug
error_log("URI: " . $uri);
error_log("Base: " . $base);
error_log("Path: " . $path);
error_log("Method: " . $method);

// Routes map
$routes = [
    ['GET', '/api/v1/health', 'Src\\Controllers\\HealthController@show'],
    ['GET', '/api/v1/version', 'Controllers\\VersionController@show'],
    ['POST', '/api/v1/auth/login', 'Src\\Controllers\\AuthController@login'],
    ['GET', '/api/v1/auth/verify', 'Src\\Controllers\\JwtController@verify'],
    ['POST', '/api/v1/auth/check', 'Src\\Controllers\\JwtController@check'],
    ['GET', '/api/v1/users', 'Src\\Controllers\\UserController@index'],
    ['GET', '/api/v1/users/{id}', 'Src\\Controllers\\UserController@show'],
    ['POST', '/api/v1/users', 'Src\\Controllers\\UserController@store'],
    ['PUT', '/api/v1/users/{id}', 'Src\\Controllers\\UserController@update'],
    ['DELETE', '/api/v1/users/{id}', 'Src\\Controllers\\UserController@destroy'],
    ['POST', '/api/v1/upload', 'Src\\Controllers\\UploadController@store']
];

// Match route
function matchRoute($routes, $method, $path) {
    foreach ($routes as $r) {
        [$m, $p, $h] = $r;
        if ($m !== $method) {
            continue;
        }
        $regex = preg_replace('#\{[^\}]+\}#', '([\w-]+)', $p);
        if (preg_match('#^' . $regex . '$#', $path, $mch)) {
            array_shift($mch);
            return [$h, $mch];
        }
    }
    return [null, null];
}

[$handler, $params] = matchRoute($routes, $method, $path);

if (!$handler) {
    Response::jsonError(404, 'Route not found');
}

[$class, $action] = explode('@', $handler);
if (!method_exists($class, $action)) {
    Response::jsonError(405, 'Method not allowed');
}

call_user_func_array([new $class($cfg), $action], $params);
