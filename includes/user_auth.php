<?php
/**
 * User Role Authentication Guard
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if (!isset($_SESSION['user_id'])) {
    set_flash('error', 'Please log in to access your customer dashboard.');
    header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Fetch up-to-date user profile
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id LIMIT 1");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $current_user = $stmt->fetch();

    if (!$current_user) {
        unset($_SESSION['user_id'], $_SESSION['full_name'], $_SESSION['email']);
        set_flash('error', 'User account not found. Please log in again.');
        header("Location: ../login.php");
        exit();
    }

    if ($current_user['status'] === 'blocked') {
        unset($_SESSION['user_id'], $_SESSION['full_name'], $_SESSION['email']);
        set_flash('error', 'Your account has been suspended. Please contact customer support.');
        header("Location: ../login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Authentication check error.");
}
