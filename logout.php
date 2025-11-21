<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Clear all user-related session keys
unset($_SESSION['userId']);
unset($_SESSION['userUsername']);
unset($_SESSION['userRole']);

// Optional: clear all session data
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to homepage (browse)
header("Location: browse.php");
exit;
