<?php
// Session Log Out Handler
require_once 'includes/auth.php';

// Unset all session variables
$_SESSION = [];

// Destroy session cookies if any
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start a fresh session to hold the flash message
session_start();
setFlashMessage('info', 'You have logged out successfully.');

// Redirect to login page
header("Location: login.php");
exit;
?>
