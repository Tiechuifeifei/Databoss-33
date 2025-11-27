<?php
// login.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/utilities.php';

$email    = $_POST['userEmail']    ?? '';
$password = $_POST['userPassword'] ?? '';
$redirect = $_POST['redirect']     ?? 'browse.php';

//return to original page if error
function redirect_with_error(string $redirect, string $message): void {
    // 确保有 ? / & 正确拼接参数
    $sep = (strpos($redirect, '?') === false) ? '?' : '&';
    header("Location: {$redirect}{$sep}loginError=" . urlencode($message));
    exit();
}

//
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    redirect_with_error($redirect, 'Invalid login credentials.');
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
    redirect_with_error($redirect, 'Sorry, something went wrong. Please try again later.');
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    redirect_with_error($redirect, 'No user found with that email.');
}

$user = $result->fetch_assoc();
$stmt->close();

//check the password
if (!password_verify($password, $user['userPassword'])) {
    redirect_with_error($redirect, 'Wrong password.');
}

$_SESSION['userId']   = (int)$user['userId'];
$_SESSION['userName'] = $user['userName'];
$_SESSION['userRole'] = $user['userRole'];

//return to browse or listing page
header("Location: {$redirect}");
exit();
