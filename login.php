<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/utilities.php';

$email    = $_POST['userEmail']    ?? '';
$password = $_POST['userPassword'] ?? '';
$redirect = $_POST['redirect']     ?? 'browse.php';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    die('Invalid login credentials.');
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
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    die('Email not found.');
}

$user = $result->fetch_assoc();
$stmt->close();

if (!password_verify($password, $user['userPassword'])) {
    die('Incorrect password.');
}

$_SESSION['userId']        = $user['userId'];
$_SESSION['userName']  = $user['userName'];
$_SESSION['userRole']      = $user['userRole'];

header("Location: $redirect");
exit;
