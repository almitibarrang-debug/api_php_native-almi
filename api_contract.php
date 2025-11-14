<?php
$api_contract = [
    [
        "endpoint" => "/api/v1/auth/login",
        "method" => "POST",
        "description" => "Autentikasi user menggunakan email dan password",
        "request_body" => [
            "email" => "string",
            "password" => "string"
        ],
        "response" => [
            "status" => "success",
            "token" => "string"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users",
        "method" => "GET",
        "description" => "Menampilkan daftar semua user",
        "request_body" => [],
        "response" => [
            "status" => "success",
            "data" => "array of users"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/health",
        "method" => "GET",
        "description" => "Digunakan untuk menampilkan status kesehatan server API",
        "request_body" => [],
        "response" => [
            "status" => "healthy",
            "uptime" => "string",
            "timestamp" => "datetime"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users/{id}",
        "method" => "GET",
        "description" => "Digunakan untuk menampilkan detail data user berdasarkan ID",
        "request_body" => [],
        "response" => [
            "status" => "success",
            "data" => [
                "id" => "integer",
                "name" => "string",
                "email" => "string"
            ]
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users",
        "method" => "POST",
        "description" => "Digunakan untuk menambahkan data user baru",
        "request_body" => [
            "name" => "string",
            "email" => "string",
            "password" => "string"
        ],
        "response" => [
            "status" => "success",
            "message" => "User created successfully",
            "data" => [
                "id" => "integer",
                "name" => "string",
                "email" => "string"
            ]
        ],
        "status_code" => 201,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users/{id}",
        "method" => "PUT",
        "description" => "Digunakan untuk memperbarui data user berdasarkan ID",
        "request_body" => [
            "name" => "string (optional)",
            "email" => "string (optional)",
            "password" => "string (optional)"
        ],
        "response" => [
            "status" => "success",
            "message" => "User updated successfully"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users/{id}",
        "method" => "DELETE",
        "description" => "Digunakan untuk  menghapus data user berdasarkan ID",
        "request_body" => [],
        "response" => [
            "status" => "success",
            "message" => "User deleted successfully"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/upload",
        "method" => "POST",
        "description" => "Digunakan untuk mengunggah file ke server",
        "request_body" => [
            "file" => "binary (multipart/form-data)"
        ],
        "response" => [
            "status" => "success",
            "file_url" => "string"
        ],
        "status_code" => 201,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/version",
        "method" => "GET",
        "description" => "Digunakan untuk menampilkan informasi versi API",
        "request_body" => [],
        "response" => [
            "status" => "success",
            "version" => "string",
            "release_date" => "datetime"
        ],
        "status_code" => 200,
        "version" => "v1"
    ]
];

header('Content-Type: application/json');
echo json_encode($api_contract, JSON_PRETTY_PRINT);
?>