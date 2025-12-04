<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/db_connect.php'; 


$email    = trim($_POST['userEmail']    ?? '');
$pass     = (string)($_POST['userPassword'] ?? '');
$redirect = $_POST['redirect'] ?? 'browse.php';


function login_fail(string $msg, string $redirect) {
    $_SESSION['flash_error'] = $msg;
    header("Location: $redirect");
    exit();
}


if ($email === '' || $pass === '') {
    login_fail('Email or password is empty.', $redirect);
}


$db = get_db_connection();

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
if (!$stmt) {
    login_fail('Sorry, something went wrong. Please try again later.', $redirect);
}

$stmt->bind_param('s', $email);
$stmt->execute();
$res  = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();


if (!$user) {
    login_fail('Account not found.', $redirect);
}


if (!password_verify($pass, $user['userPassword'])) {
    login_fail('Wrong password.', $redirect);
}


$_SESSION['userId']   = (int)$user['userId'];
$_SESSION['userName'] = $user['userName'] ?: 'User';
$_SESSION['userRole'] = $user['userRole'] ?: 'buyer';

unset($_SESSION['flash_error']);


header("Location: $redirect");

exit();
