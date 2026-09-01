<?php
/**
 * Cloud Closet - Authentication & Session Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate or retrieve CSRF token
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Generate hidden input for CSRF
if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
    }
}

// Validate CSRF token
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
    }
}

// Flash messaging helpers
if (!function_exists('set_flash')) {
    function set_flash($type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type, // 'success', 'error', 'warning', 'info'
            'message' => $message
        ];
    }
}

if (!function_exists('get_flash')) {
    function get_flash() {
        if (isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('render_flash')) {
    function render_flash() {
        $flash = get_flash();
        if (!$flash) return '';
        
        $type = htmlspecialchars($flash['type']);
        $message = htmlspecialchars($flash['message']);
        $icon = 'fa-info-circle';
        if ($type === 'success') $icon = 'fa-check-circle';
        if ($type === 'error' || $type === 'danger') {
            $type = 'error';
            $icon = 'fa-exclamation-circle';
        }
        if ($type === 'warning') $icon = 'fa-triangle-exclamation';

        return '
        <div class="alert alert-' . $type . ' alert-dismissible" role="alert">
            <i class="fa-solid ' . $icon . ' alert-icon"></i>
            <div class="alert-content">' . $message . '</div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
        </div>';
    }
}
