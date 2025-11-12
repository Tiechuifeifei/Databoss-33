<?php
// login_as.php —— 本地调试用：直接“伪登录”为用户 #2（B）
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$_SESSION['userID']    = 2;
$_SESSION['userName']  = 'B';
$_SESSION['userEmail'] = 'b@email.com';
header('Location: mybids.php');
exit;
