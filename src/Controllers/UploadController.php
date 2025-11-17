<?php

namespace Src\Controllers;

use Src\Helpers\Response;

class UploadController extends BaseController
{
    public const MAX_FILE_SIZE = 2 * 1024 * 1024;
    public const UPLOADS_DIR = '/../../uploads/';

    private array $allowedMimeTypes = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'application/pdf' => 'pdf'
    ];

    public function store()
    {
        if (($_SERVER['CONTENT_TYPE'] ?? '') &&
            str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
            return $this->error(415, 'Use multipart/form-data for upload');
        }

        if (empty($_FILES['file'])) {
            return $this->error(422, 'file is required');
        }

        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->error(400, 'Upload error');
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return $this->error(422, 'Max 2MB');
        }

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($file['tmp_name']);

        if (!isset($this->allowedMimeTypes[$mimeType])) {
            return $this->error(422, 'Invalid mime type');
        }

        $fileName = bin2hex(random_bytes(8)) . '.' . $this->allowedMimeTypes[$mimeType];
        $destination = __DIR__ . self::UPLOADS_DIR . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return $this->error(500, 'Save failed');
        }

        return $this->ok(['path' => "/uploads/$fileName"], 201);
    }
}
