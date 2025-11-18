<?php
// login_as.php — local debugging: "fake login" as user ID 2
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Set session keys according to new schema
$_SESSION['userId']   = 2;
$_SESSION['username'] = 'B';
$_SESSION['role']     = 'buyer'; // or 'seller' if this user is a seller

header('Location: mybids.php');
exit;
