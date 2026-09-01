<?php
/**
 * Administrator Logout
 */

session_start();

unset($_SESSION['admin_id']);
unset($_SESSION['full_name']);
unset($_SESSION['email']);
unset($_SESSION['role']);

if (empty($_SESSION['user_id']) && empty($_SESSION['vendor_id'])) {
    session_destroy();
}

header("Location: login.php?logout=success");
exit();
