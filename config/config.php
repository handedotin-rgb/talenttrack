<?php
// config/config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application Information
define('APP_NAME', 'TalentTrack');
define('APP_TAGLINE', 'Full-Cycle Recruitment & Candidate Pipeline Platform');
define('APP_VERSION', '1.0.0');

// Base Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads');
define('RESUMES_PATH', UPLOADS_PATH . DIRECTORY_SEPARATOR . 'resumes');
define('AVATARS_PATH', UPLOADS_PATH . DIRECTORY_SEPARATOR . 'avatars');

// Detect Base URL & Protocol (Supporting Cloud Reverse Proxies like Render, Cloudflare, etc.)
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
);
$protocol = $isHttps ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptDir = ($scriptName === '/' || $scriptName === '\\' || $scriptName === '.') ? '' : rtrim($scriptName, '/');

// BASE_URL is root-relative (e.g. "" or "/subfolder"), ensuring CSS/JS/images NEVER suffer mixed-content blocks
define('BASE_URL', $scriptDir);
define('FULL_BASE_URL', rtrim($protocol . $host . $scriptDir, '/'));

// Allowed Upload MIME types & extensions
define('ALLOWED_RESUME_EXTENSIONS', ['pdf', 'doc', 'docx']);
define('MAX_FILE_SIZE_BYTES', 5 * 1024 * 1024); // 5 MB

// Database Drivers: 'sqlite' or 'mysql'
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'sqlite');

// SQLite config
define('SQLITE_FILE', ROOT_PATH . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'talenttrack.sqlite');

// MySQL config (for production or XAMPP)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_DATABASE', getenv('DB_DATABASE') ?: 'talenttrack');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

// Email OTP Verification Configuration (Live Gmail SMTP Active)
define('EMAIL_MOCK',       (bool)(getenv('EMAIL_MOCK') !== false ? (getenv('EMAIL_MOCK') === 'true' || getenv('EMAIL_MOCK') === '1') : false));
define('SMTP_HOST',        getenv('SMTP_HOST')        ?: 'smtp.gmail.com');
define('SMTP_PORT',        (int)(getenv('SMTP_PORT')  ?: 465));
define('SMTP_USER',        getenv('SMTP_USER')        ?: 'handedotin@gmail.com');
define('SMTP_PASS',        getenv('SMTP_PASS')        ?: 'tqfnxgfgbyqkyhgw');
define('SMTP_FROM',        getenv('SMTP_FROM')        ?: 'handedotin@gmail.com');
define('SMTP_FROM_NAME',   getenv('SMTP_FROM_NAME')   ?: APP_NAME);
define('RESEND_API_KEY',   getenv('RESEND_API_KEY')   ?: '');

// Recruitment Pipeline Stages
define('RECRUITMENT_STAGES', [
    'applied'     => ['label' => 'Applied', 'color' => 'slate', 'order' => 1, 'desc' => 'Candidate application submitted.'],
    'screening'   => ['label' => 'Screening', 'color' => 'blue', 'order' => 2, 'desc' => 'Resume and qualifications under review.'],
    'interview'   => ['label' => 'Interviewing', 'color' => 'purple', 'order' => 3, 'desc' => 'Interview round scheduled or in-progress.'],
    'assessment'  => ['label' => 'Technical Assessment', 'color' => 'amber', 'order' => 4, 'desc' => 'Skill assessment or test assignment requested.'],
    'offer'       => ['label' => 'Offer Extended', 'color' => 'emerald', 'order' => 5, 'desc' => 'Formal offer letter extended to candidate.'],
    'hired'       => ['label' => 'Hired', 'color' => 'green', 'order' => 6, 'desc' => 'Candidate has accepted the offer.'],
    'rejected'    => ['label' => 'Rejected', 'color' => 'rose', 'order' => 7, 'desc' => 'Application was not moved forward.'],
]);
