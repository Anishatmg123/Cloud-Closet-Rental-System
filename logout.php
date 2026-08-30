<?php
/**
 * User Logout Module
 * Cloud Closet Rental System
 *
 * This file destroys the active session and logs the user out,
 * redirecting them back to the login page with a success message.
 */

// Start session
session_start();

// Unset all session variables
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
// Note: This completely destroys the session cookie in the user's browser.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start a fresh, clean session to pass a logout feedback message to login.php
session_start();
$_SESSION['registration_success'] = "You have been logged out successfully.";

// Redirect to login page
header("Location: login.php");
exit();
