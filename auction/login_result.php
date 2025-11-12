<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/utilities.php';

function back($msg, $code = 302, $where = null) {
  // 简单返回：输出消息并给一条返回链接（避免 header 已输出导致跳转失败）
  if (headers_sent()) {
    echo '<div style="max-width:640px;margin:40px auto;font-family:system-ui,Arial">';
    echo '<div class="alert alert-danger" role="alert" style="border:1px solid #f5c2c7;padding:12px;background:#f8d7da;color:#842029;">'.h($msg).'</div>';
    $href = $where ?: ($_SERVER['HTTP_REFERER'] ?? 'browse.php');
    echo '<p><a href="'.h($href).'">Back</a></p></div>';
    exit;
  } else {
    $_SESSION['flash_error'] = $msg;
    header("Location: " . ($where ?: ($_SERVER['HTTP_REFERER'] ?? 'browse.php')), true, $code);
    exit;
  }
}

$email = trim($_POST['email'] ?? '');
$pass  = (string)($_POST['password'] ?? '');
$redirect = $_POST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? 'browse.php');

if ($email === '' || $pass === '') {
  back('Email or password is empty.', 302, $redirect);
}

$db = get_db_connection();

// 查 users 表（字段已小驼峰）
$stmt = $db->prepare("
  SELECT userId, userName, userEmail, userPassword, role
  FROM users
  WHERE userEmail = ?
  LIMIT 1
");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
  back('Account not found.', 302, $redirect);
}

// 支持两种密码存储：hash 或明文
$ok = false;
$stored = (string)$user['userPassword'];
if (strlen($stored) >= 20 && ($stored[0] === '$')) {
  // 看起来像 password_hash
  $ok = password_verify($pass, $stored);
} else {
  // 明文
  $ok = hash_equals($stored, $pass);
}

if (!$ok) {
  back('Wrong password.', 302, $redirect);
}

// 登录成功：统一写入 session 键名（全站都用这三个）
$_SESSION['userId']   = (int)$user['userId'];
$_SESSION['userName'] = $user['userName'] ?: 'User';
$_SESSION['role']     = $user['role'] ?: 'buyer'; // 你们可以在注册时写 buyer/seller

// 跳回来源（或 fallback 到 browse.php）
if (!headers_sent()) {
  header('Location: ' . $redirect);
}
exit;
