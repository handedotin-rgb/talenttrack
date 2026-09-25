<?php
// views/layouts/auth.php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/src/Helpers/view.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Authentication - ' . APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎯</text></svg>">
</head>
<body style="background: #f1f5f9; display: flex; flex-direction: column; min-height: 100vh;">


<div class="main-wrapper" style="align-items: center; justify-content: center; padding: 2.5rem 1rem;">
    <?= render_flash() ?>
    <div style="margin-bottom: 1.5rem; text-align: center;">
        <a href="<?= BASE_URL ?>/" class="brand-logo" style="display: inline-flex;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="6"></circle>
                <circle cx="12" cy="12" r="2"></circle>
            </svg>
            <span style="font-size: 1.5rem;"><?= APP_NAME ?></span>
        </a>
    </div>

    <?= $content ?>
</div>

<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
