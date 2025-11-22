<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/utilities.php';

function back($msg, $code = 302, $where = null) {
    if (headers_sent()) {
        echo '<div style="max-width:640px;margin:40px auto;font-family:system-ui,Arial">';
        echo '<div class="alert alert-danger" role="alert" style="border:1px solid #f5c2c7;padding:12px;background:#f8d7da;color:#842029;">'.h($msg).'</div>';
        $href = $where ?: ($_SERVER['HTTP_REFERER'] ?? 'browse.php');
        echo '<p><a href="'.h($href).'">Back</a></p></div>';
        exit;
    } else {
        $_SESSION['flash_error'] = $msg;
        header("Location: " . ($where ?: ($_SERVER['HTTP_REFERER'] ?? 'browse.php')), true, $code);
        exit;
    }
}

// NEW: read using correct POST names
$email = trim($_POST['userEmail'] ?? '');
$pass  = (string)($_POST['userPassword'] ?? '');
$redirect = $_POST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? 'browse.php');

if ($email === '' || $pass === '') {
    back('Email or password is empty.', 302, $redirect);
}

$db = get_db_connection();

// UPDATED query to match new user table schema
$stmt = $db->prepare("
    SELECT 
        userId,
        userName,
        userEmail,
        userPassword,
        userRole
    FROM users
    WHERE userEmail = ?
    LIMIT 1
");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    back('Account not found.', 302, $redirect);
}

// Verify password using the renamed 'userPassword'
if (!password_verify($pass, $user['userPassword'])) {
    back('Wrong password.', 302, $redirect);
}

// Login success: correct session keys
$_SESSION['userId']        = (int)$user['userId'];
$_SESSION['userName']  = $user['userName'] ?: 'User';
$_SESSION['userRole']      = $user['userRole'] ?: 'buyer';

// Redirect back
if (!headers_sent()) {
    header('Location: ' . $redirect);
}
exit;