<?php
/**
 * Vendor Role Authentication Guard
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if (!isset($_SESSION['vendor_id'])) {
    set_flash('error', 'Please log in to access the Vendor Portal.');
    header("Location: ../vendor/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Fetch up-to-date vendor profile
try {
    $stmt = $pdo->prepare("SELECT * FROM vendors WHERE vendor_id = :vendor_id LIMIT 1");
    $stmt->execute([':vendor_id' => $_SESSION['vendor_id']]);
    $current_vendor = $stmt->fetch();

    if (!$current_vendor) {
        unset($_SESSION['vendor_id'], $_SESSION['vendor_name'], $_SESSION['vendor_email']);
        set_flash('error', 'Vendor account not found. Please log in.');
        header("Location: ../vendor/login.php");
        exit();
    }

    if ($current_vendor['status'] === 'blocked') {
        unset($_SESSION['vendor_id'], $_SESSION['vendor_name'], $_SESSION['vendor_email']);
        set_flash('error', 'Your vendor boutique account has been suspended. Please contact support.');
        header("Location: ../vendor/login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Vendor authentication check error.");
}
