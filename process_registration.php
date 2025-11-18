<?php
require_once __DIR__ . '/utilities.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/**
 * Helper to display an error and exit
 */
function back_with_msg(string $msg) {
    include_once __DIR__ . '/header.php';
    echo '<div class="container my-4" style="max-width:860px;">';
    echo '<div class="alert alert-danger">' . htmlspecialchars($msg) . '</div>';
    echo '<p><a class="btn btn-secondary" href="register.php">Back</a></p>';
    echo '</div>';
    include_once __DIR__ . '/footer.php';
    exit;
}

// ---- Collect POST data ----
$username = trim($_POST['username'] ?? '');
$role = trim($_POST['accountType'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirmation = $_POST['passwordConfirmation'] ?? '';
$phone = trim($_POST['phoneNumber'] ?? '');
$dob = $_POST['dob'] ?? '';
$house = trim($_POST['houseNo'] ?? '');
$street = trim($_POST['street'] ?? '');
$city = trim($_POST['city'] ?? '');
$postcode = trim($_POST['postcode'] ?? '');

// ---- Validation ----
if ($username === '') back_with_msg('Username is required.');
if (!in_array($role, ['buyer', 'seller'], true)) back_with_msg('Please select a role.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) back_with_msg('Invalid email format.');
if ($password === '' || $passwordConfirmation === '') back_with_msg('Password is required.');
if ($password !== $passwordConfirmation) back_with_msg('Passwords do not match.');
if (strlen($password) < 6) back_with_msg('Password must be at least 6 characters.');

// Age check
try {
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    if ($age < 18) back_with_msg('You must be at least 18 years old.');
} catch (Exception $e) {
    back_with_msg('Invalid date of birth.');
}

// ---- DB connection ----
$db = get_db_connection();

// Check email uniqueness
$stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $stmt->close();
    back_with_msg('This email is already registered.');
}
$stmt->close();

// Check username uniqueness
$stmt = $db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $stmt->close();
    back_with_msg('This username is already taken.');
}
$stmt->close();

// ---- Insert user ----
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("
    INSERT INTO users (username, email, password_hash, role, phone_number, dob, house_no, street, city, postcode)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param('ssssssssss', $username, $email, $hash, $role, $phone, $dob, $house, $street, $city, $postcode);

if (!$stmt->execute()) {
    back_with_msg('Failed to register: ' . $db->error);
}

$userId = $stmt->insert_id;
$stmt->close();

// ---- Auto login ----
$_SESSION['userId'] = $userId;
$_SESSION['username'] = $username;
$_SESSION['role'] = $role;

// Redirect to browse page
header('Location: browse.php');
exit;
