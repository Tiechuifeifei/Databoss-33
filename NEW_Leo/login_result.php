<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once __DIR__ . '/utilities.php';

// 告诉浏览器：这是 JSON 响应
header('Content-Type: application/json; charset=utf-8');

// 简单的 JSON 输出工具函数
function json_response(bool $success, string $message = '', string $redirect = 'browse.php') {
    echo json_encode([
        'success'  => $success,
        'message'  => $message,
        'redirect' => $redirect,
    ]);
    exit;
}

// 读取表单字段（名字保持不变）
$email    = trim($_POST['userEmail']    ?? '');
$pass     = (string)($_POST['userPassword'] ?? '');
$redirect = $_POST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? 'browse.php');

// 1) 基础校验
if ($email === '' || $pass === '') {
    json_response(false, 'Email or password is empty.');
}

// 2) 查数据库（沿用你原来的逻辑）
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
    json_response(false, 'Sorry, something went wrong. Please try again later.');
}

$stmt->bind_param('s', $email);
$stmt->execute();
$res  = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

// 3) 账号是否存在
if (!$user) {
    json_response(false, 'Account not found.');
}

// 4) 密码是否正确
if (!password_verify($pass, $user['userPassword'])) {
    json_response(false, 'Wrong password.');
}

// 5) 登录成功：写 session（保留你原来的 key）
$_SESSION['userId']   = (int)$user['userId'];
$_SESSION['userName'] = $user['userName'] ?: 'User';
$_SESSION['userRole'] = $user['userRole'] ?: 'buyer';

// 6) 返回成功 + 前端要跳转到哪
json_response(true, '', $redirect);
