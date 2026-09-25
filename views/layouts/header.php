<?php
// views/layouts/header.php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/src/Helpers/auth.php';
require_once dirname(__DIR__, 2) . '/src/Helpers/view.php';

$currentUser = auth_user();
$currentRole = $currentUser['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME . ' - Recruitment Tracking') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎯</text></svg>">
</head>
<body>


<!-- Main Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/" class="brand-logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="6"></circle>
                <circle cx="12" cy="12" r="2"></circle>
            </svg>
            <span><?= APP_NAME ?></span>
            <?php if ($currentRole): ?>
                <span class="brand-badge"><?= ucfirst($currentRole) ?></span>
            <?php endif; ?>
        </a>

        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>/jobs">Browse Jobs</a></li>

            <?php if ($currentRole === 'candidate'): ?>
                <li><a href="<?= BASE_URL ?>/candidate/dashboard">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/candidate/applications">My Applications</a></li>
                <li><a href="<?= BASE_URL ?>/candidate/interviews">Interviews</a></li>
                <li><a href="<?= BASE_URL ?>/candidate/profile">Profile & Resume</a></li>
            <?php elseif ($currentRole === 'recruiter'): ?>
                <li><a href="<?= BASE_URL ?>/recruiter/dashboard">Recruiter Desk</a></li>
                <li><a href="<?= BASE_URL ?>/recruiter/jobs">Manage Jobs</a></li>
                <li><a href="<?= BASE_URL ?>/recruiter/pipeline">Recruitment Pipeline</a></li>
            <?php elseif ($currentRole === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>/admin/dashboard">Analytics</a></li>
                <li><a href="<?= BASE_URL ?>/admin/users">Users & Approvals</a></li>
                <li><a href="<?= BASE_URL ?>/admin/jobs">All Jobs</a></li>
                <li><a href="<?= BASE_URL ?>/admin/categories">Categories</a></li>
                <li><a href="<?= BASE_URL ?>/admin/audit-logs">Audit Log</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-user">
            <?php if ($currentUser): ?>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div class="user-avatar" title="<?= e($currentUser['name']) ?> (<?= ucfirst($currentRole) ?>)">
                        <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 0.88rem; font-weight: 700;"><?= e($currentUser['name']) ?></span>
                        <span style="font-size: 0.75rem; color: var(--slate-500);"><?= ucfirst($currentRole) ?></span>
                    </div>
                    <a href="<?= BASE_URL ?>/logout" class="btn btn-secondary btn-sm" style="margin-left: 0.5rem;">
                        Sign Out
                    </a>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login" class="btn btn-secondary btn-sm">Sign In</a>
                <a href="<?= BASE_URL ?>/register" class="btn btn-primary btn-sm">Get Started</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="main-wrapper">
<?= render_flash() ?>
