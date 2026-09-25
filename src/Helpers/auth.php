<?php
// src/Helpers/auth.php

require_once dirname(__DIR__, 2) . '/config/config.php';

function is_authenticated(): bool {
    return !empty($_SESSION['user_id']);
}

function auth_user(): ?array {
    if (!is_authenticated()) {
        return null;
    }
    return [
        'id'           => $_SESSION['user_id'],
        'name'         => $_SESSION['user_name'] ?? '',
        'email'        => $_SESSION['user_email'] ?? '',
        'role'         => $_SESSION['user_role'] ?? 'candidate',
        'company_name' => $_SESSION['user_company'] ?? null,
        'headline'     => $_SESSION['user_headline'] ?? null,
        'avatar'       => $_SESSION['user_avatar'] ?? null,
    ];
}

function has_role($roles): bool {
    if (!is_authenticated()) {
        return false;
    }
    $currentRole = $_SESSION['user_role'] ?? '';
    if (is_array($roles)) {
        return in_array($currentRole, $roles, true);
    }
    return $currentRole === $roles;
}

function require_auth(): void {
    if (!is_authenticated()) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
        require_once __DIR__ . '/flash.php';
        set_flash('error', 'Please log in to access this page.');
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}

function require_role($roles): void {
    require_auth();
    if (!has_role($roles)) {
        require_once __DIR__ . '/flash.php';
        set_flash('error', 'Access denied. You do not have permission to view that section.');
        $role = $_SESSION['user_role'] ?? 'candidate';
        header('Location: ' . BASE_URL . '/' . $role . '/dashboard');
        exit;
    }
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']       = (int)$user['id'];
    $_SESSION['user_name']     = $user['name'];
    $_SESSION['user_email']    = $user['email'];
    $_SESSION['user_role']     = $user['role'];
    $_SESSION['user_company']  = $user['company_name'] ?? null;
    $_SESSION['user_headline'] = $user['headline'] ?? null;
    $_SESSION['user_avatar']   = $user['avatar'] ?? null;
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
