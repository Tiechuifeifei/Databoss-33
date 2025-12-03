<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'process_registration.php';


<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/utilities.php';

$db = get_db_connection();

echo "<h2>Database Connection OK</h2>";


$userName = "TestUser_" . rand(1000, 9999);
$userEmail = "test" . rand(1000, 9999) . "@example.com";
$userPassword = "123456"; 
$hash = password_hash($userPassword, PASSWORD_DEFAULT);

$userRole = "buyer";

$userPhoneNumber = "1234567890";
$userDob         = "2000-01-01";
$userHouseNo     = "1";
$userStreet      = "Test Street";
$userCity        = "London";
$userPostcode    = "E1 1AA";


$sql = "
    INSERT INTO users (
        userName, userEmail, userPassword, userRole,
        userPhoneNumber, userDob, userHouseNo, userStreet, userCity, userPostcode
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $db->prepare($sql);

if (!$stmt) {
    echo "<strong>Prepare failed:</strong> " . $db->error;
    exit;
}

$stmt->bind_param(
    "ssssssssss",
    $userName,
    $userEmail,
    $hash,
    $userRole,
    $userPhoneNumber,
    $userDob,
    $userHouseNo,
    $userStreet,
