<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/db_connect.php';   // 这里是你定义 get_db_connection() 的文件

// 读取表单字段（名字保持不变）
$email    = trim($_POST['userEmail']    ?? '');
$pass     = (string)($_POST['userPassword'] ?? '');
$redirect = $_POST['redirect'] ?? 'browse.php';

// 小工具：失败时写入 flash_error 然后跳回去
function login_fail(string $msg, string $redirect) {
    $_SESSION['flash_error'] = $msg;
    header("Location: $redirect");
    exit();
}

// 1) 基础校验
if ($email === '' || $pass === '') {
    login_fail('Email or password is empty.', $redirect);
}

// 2) 查数据库
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

// 3) 账号是否存在
if (!$user) {
    login_fail('Account not found.', $redirect);
}

// 4) 密码是否正确
if (!password_verify($pass, $user['userPassword'])) {
    login_fail('Wrong password.', $redirect);
}

// 5) 登录成功：写 session（保留你原来的 key）
$_SESSION['userId']   = (int)$user['userId'];
$_SESSION['userName'] = $user['userName'] ?: 'User';
$_SESSION['userRole'] = $user['userRole'] ?: 'buyer';

// 万一之前有错误信息，清掉
unset($_SESSION['flash_error']);

// 6) 成功后直接跳转
header("Location: $redirect");
exit();