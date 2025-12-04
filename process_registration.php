<?php
require_once __DIR__ . '/utilities.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function back_with_msg(string $msg) {
    $_SESSION['error_msg'] = $msg;
    header("Location: register.php");
    exit;
}

// input form
$userName             = trim($_POST['userName'] ?? '');
$userEmail                = trim($_POST['userEmail'] ?? '');
$userPassword             = $_POST['userPassword'] ?? '';
$userPasswordConfirmation = $_POST['userPasswordConfirmation'] ?? '';
$userPhoneNumber          = trim($_POST['userPhoneNumber'] ?? '');
$userDob                  = $_POST['userDob'] ?? '';
$userHouseNo              = trim($_POST['userHouseNo'] ?? '');
$userStreet               = trim($_POST['userStreet'] ?? '');
$userCity                 = trim($_POST['userCity'] ?? '');
$userPostcode             = trim($_POST['userPostcode'] ?? '');

//input check
if ($userName === '') back_with_msg('Username is required.');
if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) back_with_msg('Invalid email format.');
if ($userPassword === '' || $userPasswordConfirmation === '') back_with_msg('Password is required.');
if ($userPassword !== $userPasswordConfirmation) back_with_msg('Passwords do not match.');
if (strlen($userPassword) < 6) back_with_msg('Password must be at least 6 characters.');

//age check
try {
    $birthDate = new DateTime($userDob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    if ($age < 18) back_with_msg('You must be at least 18 years old.');
} catch (Exception $e) {
    back_with_msg('Invalid date of birth.');
}

//database connection 
$db = get_db_connection();

// Email uniqueness
$stmt = $db->prepare("SELECT userId FROM users WHERE userEmail = ? LIMIT 1");
$stmt->bind_param('s', $userEmail);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $stmt->close();
    back_with_msg('This email is already registered.');
}
$stmt->close();

//Username uniqueness
$stmt = $db->prepare("SELECT userId FROM users WHERE userName = ? LIMIT 1");
$stmt->bind_param('s', $userName);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $stmt->close();
    back_with_msg('This username is already taken.');
}
$stmt->close();

//insert an user
$hash = password_hash($userPassword, PASSWORD_DEFAULT);

$stmt = $db->prepare("
    INSERT INTO users (
        userName, userEmail, userPassword,
        userPhoneNumber, userDob, userHouseNo, userStreet, userCity, userPostcode
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    'sssssssss',
    $userName,
    $userEmail,
    $hash,
    $userPhoneNumber,
    $userDob,
    $userHouseNo,
    $userStreet,
    $userCity,
    $userPostcode
);

if (!$stmt->execute()) {
    back_with_msg('Failed to register: ' . $db->error);
}

$userId = $stmt->insert_id;
$stmt->close();

//login 
$_SESSION['userId']       = $userId;
$_SESSION['userName'] = $userName;

header('Location: browse.php');
exit;

