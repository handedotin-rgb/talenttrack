<?php
// src/Helpers/flash.php

require_once dirname(__DIR__, 2) . '/config/config.php';

function set_flash(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type'    => $type, // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

function get_flash_messages(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function render_flash(): string {
    $messages = get_flash_messages();
    if (empty($messages)) {
        return '';
    }

    $html = '<div class="flash-container">';
    foreach ($messages as $msg) {
        $type = htmlspecialchars($msg['type']);
        $text = htmlspecialchars($msg['message']);
        
        $icon = match($type) {
            'success' => '&#10003;',
            'error'   => '&#9888;',
            'warning' => '&#9888;',
            default   => '&#8505;'
        };

        $html .= "
        <div class=\"alert alert-{$type}\" role=\"alert\">
            <div class=\"alert-icon\">{$icon}</div>
            <div class=\"alert-message\">{$text}</div>
            <button type=\"button\" class=\"alert-close\" onclick=\"this.parentElement.remove();\">&times;</button>
        </div>";
    }
    $html .= '</div>';
    return $html;
}
