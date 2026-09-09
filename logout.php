<?php
/**
 * Customer Logout
 */

session_start();

unset($_SESSION['user_id']);
unset($_SESSION['full_name']);
unset($_SESSION['email']);

// If no other sessions exist, destroy session
if (empty($_SESSION['vendor_id'])) {
    session_destroy();
}

header("Location: login.php?logout=success");
exit();
