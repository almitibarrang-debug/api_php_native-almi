# API PHP Native

RESTful API sederhana menggunakan PHP Native. Mendukung fitur login JWT, CRUD user, upload aman, dan rate limiting.

**Server:** http://localhost/api_php_native-almi/public/api/v1

---

## Struktur Folder

```
API_PHP_NATIVE-ALMI/
│
├── config/
│   └── env.php
│
├── logs/
│
├── logs5/
│
├── public/
│   ├── .htaccess
│   ├── index.php
│   └── test.php
│
├── src/
│   ├── Config/
│   │   └── Database.php
│   │
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── BaseController.php
│   │   ├── HealthController.php
│   │   ├── JwtController.php
│   │   ├── UploadController.php
│   │   ├── UserController.php
│   │   └── VersionController.php
│   │
│   ├── Helpers/
│   │   ├── Jwt.php
│   │   └── Response.php
│   │
│   ├── Middlewares/
│   │   ├── AuthMiddleware.php
│   │   └── CorsMiddleware.php
│   │
│   ├── Repositories/
│   │   └── UserRepository.php
│   │
│   └── Validation/
│       └── Validator.php
│
├── uploads/
│
├── API PHP Native.postman_collection.json
├── api_contract.php
├── CHANGELOG.md
├── composer.json
├── jwt.php
├── openapi-life.yaml
└── README.md
```

---

## Fitur Utama

- ✅ Struktur proyek PHP Native
- ✅ Router manual (index.php)
- ✅ BaseController dan Response Helper
- ✅ Validator & Sanitasi Input
- ✅ CRUD Users dengan PDO dan Repository Pattern
- ✅ Authentication JWT (login & middleware)
- ✅ Upload file aman (validasi MIME dan size)
- ✅ Pagination dan Metadata
- ✅ Rate Limiting per IP/token
- ✅ CORS Middleware
- ✅ Testing dengan Postman
- ✅ Dokumentasi README dan OpenAPI-lite

---

## API Endpoints

### 1. Health Check
**GET** `/api/v1/health`
- **Description:** Mengecek status server API
- **Response:** Server aktif
- **Status Code:** 200

### 2. Authentication

#### Login
**POST** `/api/v1/auth/login`
- **Description:** Login user dan mendapatkan token JWT
- **Request Body:**
  ```json
  {
    "email": "almi@example.com",
    "password": "almi"
  }
  ```
- **Response:** Login berhasil, token JWT dikembalikan
- **Status Code:** 200

### 3. User Management

#### Get All Users
**GET** `/api/v1/users`
- **Description:** Menampilkan daftar semua user (perlu token)
- **Headers:** `Authorization: Bearer <token>`
- **Status Code:** 200

#### Get User by ID
**GET** `/api/v1/users/{id}`
- **Description:** Menampilkan detail user berdasarkan ID
- **Parameters:** `id` (integer)
- **Status Code:** 200

#### Create User
**POST** `/api/v1/users`
- **Description:** Menambahkan user baru (perlu token admin)
- **Request Body:**
  ```json
  {
    "name": "almi",
    "email": "almi@example.com",
    "password": "almi",
    "role": "user"
  }
  ```
- **Status Code:** 201

#### Update User
**PUT** `/api/v1/users/{id}`
- **Description:** Mengupdate data user
- **Request Body:**
  ```json
  {
    "name": "string (optional)",
    "email": "string (optional)",
    "role": "string (optional)"
  }
  ```
- **Status Code:** 200

#### Delete User
**DELETE** `/api/v1/users/{id}`
- **Description:** Menghapus user berdasarkan ID
- **Status Code:** 200

### 4. File Upload
**POST** `/api/v1/upload`
- **Description:** Upload file (gambar/pdf, max 2MB)
- **Content-Type:** multipart/form-data
- **Request Body:** file (binary)
- **Status Code:** 201

### 5. Version Info
**GET** `/api/v1/version`
- **Description:** Menampilkan informasi versi API
- **Response:**
  ```json
  {
    "status": "success",
    "version": "string",
    "release_date": "datetime"
  }
  ```
- **Status Code:** 200

---

## Changelog

### [1.0.0] - 2025-11-12

#### Added
- Struktur proyek PHP Native
- Router manual (index.php)
- BaseController dan Response Helper
- Validator & Sanitasi Input
- CRUD Users dengan PDO dan Repository Pattern
- Authentication JWT (login & middleware)
- Upload file aman (validasi MIME dan size)
- Pagination dan Metadata
- Rate Limiting per IP/token
- CORS Middleware
- Testing dengan Postman
- Dokumentasi README dan OpenAPI-lite
- Changelog versi awal

---

## JWT Example

```php
$header = ['typ' => 'JWT', 'alg' => 'HS256'];
$payload = [
    'sub' => 14,
    'name' => 'almi',
    'role' => 'admin',
    'iat' => time(),
    'exp' => time() + 3600
];
$secret = 'mysecretkey';

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$header64 = base64UrlEncode(json_encode($header));
$payload64 = base64UrlEncode(json_encode($payload));
$signature = base64UrlEncode(hash_hmac('sha256', "$header64.$payload64", $secret, true));

$jwt = "$header64.$payload64.$signature";
echo $jwt;
```

---

## Postman Collection

File `API PHP Native.postman_collection.json` dapat digunakan untuk testing API di Postman dengan semua endpoint yang sudah dikonfigurasi.

---

## OpenAPI Documentation

File `openapi-life.yaml` berisi dokumentasi lengkap API dalam format OpenAPI 3.0.0 yang dapat dibuka di Swagger Editor atau tools OpenAPI lainnya.

---

**Repository:** https://github.com/almitibarrang-debug/api_php_native-almi