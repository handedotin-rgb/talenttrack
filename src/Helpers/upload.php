<?php
// src/Helpers/upload.php

require_once dirname(__DIR__, 2) . '/config/config.php';

function handle_file_upload(
    array $file,
    string $targetDir,
    array $allowedExtensions = ALLOWED_RESUME_EXTENSIONS,
    int $maxBytes = MAX_FILE_SIZE_BYTES
): string {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid file upload parameter.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file was uploaded.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('File size exceeded upload limits.');
        default:
            throw new RuntimeException('Unknown file upload error.');
    }

    if ($file['size'] > $maxBytes) {
        $maxMb = round($maxBytes / (1024 * 1024), 1);
        throw new RuntimeException("File size must not exceed {$maxMb}MB.");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions, true)) {
        throw new RuntimeException('Invalid file format. Allowed formats: ' . implode(', ', $allowedExtensions));
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Generate cryptographically unique safe filename
    $safeName = sprintf('%s_%s.%s', bin2hex(random_bytes(8)), time(), $ext);
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to save uploaded file to storage.');
    }

    return $safeName;
}
