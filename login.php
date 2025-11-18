<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/utilities.php';

// Collect POST data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$redirect = $_POST['redirect'] ?? 'browse.php';

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    die('Invalid login credentials.');
}

// Connect to DB
$db = get_db_connection();

// Look up user by email
$stmt = $db->prepare("SELECT id, username, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    die('Email not found.');
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    die('Incorrect password.');
}

// Login success — set session
$_SESSION['userId'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

// Redirect
header("Location: $redirect");
exit;