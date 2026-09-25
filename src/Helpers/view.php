<?php
// src/Helpers/view.php

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/flash.php';

function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function render_view(string $viewPath, array $data = [], string $layout = 'main'): void {
    // Extract variables into local scope
    extract($data);

    // Capture view content
    $fullViewFile = ROOT_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $viewPath . '.php';
    if (!file_exists($fullViewFile)) {
        die("View file not found: views/{$viewPath}.php");
    }

    ob_start();
    require $fullViewFile;
    $content = ob_get_clean();

    // If no layout requested, output directly
    if ($layout === 'none') {
        echo $content;
        return;
    }

    // Render with layout
    $layoutFile = ROOT_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';
    if (!file_exists($layoutFile)) {
        // Fallback to simple wrapper
        require_once ROOT_PATH . '/views/layouts/header.php';
        echo $content;
        require_once ROOT_PATH . '/views/layouts/footer.php';
        return;
    }

    require $layoutFile;
}

function stage_badge(string $stage): string {
    $stages = RECRUITMENT_STAGES;
    $stageData = $stages[$stage] ?? [
        'label' => ucfirst($stage),
        'color' => 'slate'
    ];

    $label = htmlspecialchars($stageData['label']);
    $color = htmlspecialchars($stageData['color']);

    return "<span class=\"badge badge-{$color}\">{$label}</span>";
}

function format_salary(?int $min, ?int $max, string $currency = 'USD'): string {
    $symbol = match($currency) {
        'EUR' => '€',
        'GBP' => '£',
        'INR' => '₹',
        default => '$'
    };

    if (!$min && !$max) {
        return 'Competitive / DOE';
    }

    $fmt = function($num) use ($symbol) {
        if ($num >= 1000) {
            return $symbol . round($num / 1000) . 'k';
        }
        return $symbol . number_format($num);
    };

    if ($min && $max) {
        return $fmt($min) . ' - ' . $fmt($max);
    }
    if ($min) {
        return 'From ' . $fmt($min);
    }
    return 'Up to ' . $fmt($max);
}

function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $m = floor($diff / 60);
        return $m . ' min' . ($m > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $h = floor($diff / 3600);
        return $h . ' hour' . ($h > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $d = floor($diff / 86400);
        return $d . ' day' . ($d > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

function audit_log(PDO $db, ?int $userId, string $action, string $entityType, ?int $entityId, ?string $details = null): void {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $entityType, $entityId, $details, $ip]);
    } catch (Exception $e) {
        // Silently capture logging exceptions to prevent disrupting main transactions
        error_log('Audit log error: ' . $e->getMessage());
    }
}
