<?php
/**
 * Admin Role Authentication Guard
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if (!isset($_SESSION['admin_id'])) {
    set_flash('error', 'Administrator authentication required.');
    header("Location: ../admin/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Fetch up-to-date admin profile
try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = :admin_id LIMIT 1");
    $stmt->execute([':admin_id' => $_SESSION['admin_id']]);
    $current_admin = $stmt->fetch();

    if (!$current_admin || $current_admin['status'] !== 'active') {
        unset($_SESSION['admin_id'], $_SESSION['full_name'], $_SESSION['email'], $_SESSION['role']);
        set_flash('error', 'Unauthorized administrative access. Please log in.');
        header("Location: ../admin/login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Admin authentication check error.");
}
