<?php
/**
 * Vendor Logout
 */

session_start();

unset($_SESSION['vendor_id']);
unset($_SESSION['vendor_name']);
unset($_SESSION['business_name']);
unset($_SESSION['vendor_email']);

if (empty($_SESSION['user_id']) && empty($_SESSION['admin_id'])) {
    session_destroy();
}

header("Location: login.php?logout=success");
exit();
