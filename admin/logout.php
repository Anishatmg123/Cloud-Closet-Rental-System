<?php
/**
 * Admin Logout Module
 * Cloud Closet Rental System
 *
 * This file handles admin logout by:
 * 1. Starting/resuming the active PHP session.
 * 2. Unsetting specific admin session variables (admin_id, full_name, email, role).
 * 3. Destroying all session data using session_destroy().
 * 4. Redirecting the administrator back to the login page (login.php) with a logout success message.
 *
 * This is beginner-friendly and perfect for BCA viva explanation of sessions!
 */

// 1. Initialize or resume the existing session
session_start();

// 2. Unset admin-specific session variables to clean up session memory
unset($_SESSION['admin_id']);
unset($_SESSION['full_name']);
unset($_SESSION['email']);
unset($_SESSION['role']);

// 3. Clear all session data completely from the server
$_SESSION = array();

// 4. Destroy the session cookie on the client browser if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 5. Finally, destroy the session on the server side
session_destroy();

// 6. Redirect the admin to the login page with a success parameter
header("Location: login.php?logout=success");
exit();
?>
