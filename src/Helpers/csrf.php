<?php
// src/Helpers/csrf.php

require_once dirname(__DIR__, 2) . '/config/config.php';

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            require_once __DIR__ . '/flash.php';
            set_flash('error', 'Security token expired or invalid. Please try submitting again.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/')));
            exit;
        }
    }
    return true;
}
