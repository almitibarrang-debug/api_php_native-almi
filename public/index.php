<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

spl_autoload_register(function ($className) {
    $basePath = __DIR__;
    $className = str_replace('\\', '/', $className);
    $possiblePaths = ["$basePath/src/$className.php", "$basePath/$className.php"];

    foreach ($possiblePaths as $filePath) {
        if (file_exists($filePath)) {
            require $filePath;
            return;
        }
    }
});

$config = require __DIR__ . '/config/env.php';

use Src\Helpers\Response;
use Src\Middlewares\CorsMiddleware;

CorsMiddleware::handle($config);
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

const RATE_LIMIT_MAX_REQUESTS = 5;
const RATE_LIMIT_WINDOW_SECONDS = 60;

$clientIp = $_SERVER['REMOTE_ADDR'];
$storagePath = sys_get_temp_dir() . '/rate_limit_' . md5($clientIp);

$rateLimitData = ['count' => 1, 'start' => time()];
if (file_exists($storagePath)) {
    $decodedData = json_decode(file_get_contents($storagePath), true);
    if (is_array($decodedData) && isset($decodedData['start'], $decodedData['count'])) {
        if (time() - $decodedData['start'] < RATE_LIMIT_WINDOW_SECONDS) {
            $rateLimitData['count'] = $decodedData['count'] + 1;
            $rateLimitData['start'] = $decodedData['start'];
        }
    }
}

@file_put_contents($storagePath, json_encode($rateLimitData));

if ($rateLimitData['count'] > RATE_LIMIT_MAX_REQUESTS) {
    $retryAfter = RATE_LIMIT_WINDOW_SECONDS - (time() - $rateLimitData['start']);
    header('Retry-After: ' . $retryAfter);
    Response::jsonError(429, 'Too Many Requests');
    exit;
}

$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$requestPath = '/' . trim(str_replace($basePath, '', $requestUri), '/');
$requestMethod = $_SERVER['REQUEST_METHOD'];

$routes = [
    ['GET', '/api/v1/health', 'Src\\Controllers\\HealthController@show'],
    ['GET', '/api/v1/version', 'Src\\Controllers\\VersionController@show'],
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

function matchRoute(array $routes, string $method, string $path)
{
    foreach ($routes as $route) {
        [$routeMethod, $routePath, $handler] = $route;

        if ($routeMethod !== $method) {
            continue;
        }

        $routeRegex = preg_replace('#\{[^\}]+\}#', '([\w-]+)', $routePath);
        if (preg_match('#^' . $routeRegex . '$#', $path, $matches)) {
            array_shift($matches);
            return [$handler, $matches];
        }
    }

    return [null, null];
}

[$handlerSpec, $routeParams] = matchRoute($routes, $requestMethod, $requestPath);

if (!$handlerSpec) {
    Response::jsonError(404, 'Route not found');
    exit;
}

[$controllerClass, $actionMethod] = explode('@', $handlerSpec);
if (!class_exists($controllerClass) || !method_exists($controllerClass, $actionMethod)) {
    Response::jsonError(405, 'Method not allowed');
    exit;
}

call_user_func_array([new $controllerClass($config), $actionMethod], $routeParams);
