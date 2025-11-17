# API PHP Native

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