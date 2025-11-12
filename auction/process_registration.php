<?php
// process_registration.php
require_once __DIR__ . '/utilities.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

function back_with_msg($msg, $is_ok = false) {
  // 统一的提示页（遵循你们的 header/footer 框架）
  include_once __DIR__ . '/header.php';
  echo '<div class="container my-4" style="max-width:860px;">';
  echo $is_ok
    ? '<div class="alert alert-success">'.htmlspecialchars($msg).'</div>'
    : '<div class="alert alert-danger">'.htmlspecialchars($msg).'</div>';
  echo '<p><a class="btn btn-secondary" href="register.php">Back</a></p>';
  echo '</div>';
  include_once __DIR__ . '/footer.php';
  exit;
}

// ---- 获取与校验表单 ----
$role      = isset($_POST['role']) ? trim($_POST['role']) : '';
$email     = isset($_POST['email']) ? trim($_POST['email']) : '';
$pass      = isset($_POST['password']) ? (string)$_POST['password'] : '';
$pass2     = isset($_POST['password2']) ? (string)$_POST['password2'] : '';

if (!in_array($role, ['buyer','seller'], true)) {
  back_with_msg('Please choose a role (buyer / seller).');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  back_with_msg('Invalid email format.');
}
if ($pass === '' || $pass2 === '') {
  back_with_msg('Password is required.');
}
if ($pass !== $pass2) {
  back_with_msg('Passwords do not match.');
}
if (strlen($pass) < 6) {
  back_with_msg('Password must be at least 6 characters.');
}

// ---- DB 连接 ----
$db = get_db_connection();

// ---- 检查邮箱是否已存在 ----
$check = $db->prepare("SELECT userId FROM users WHERE userEmail = ? LIMIT 1");
$check->bind_param('s', $email);
$check->execute();
$check_res = $check->get_result();
if ($check_res && $check_res->num_rows > 0) {
  $check->close();
  back_with_msg('This email is already registered. Try logging in.');
}
$check->close();

// ---- 生成 userName（先用邮箱@前缀，不重复即可；若已存在则附加数字）----
$userName = strstr($email, '@', true);
if ($userName === false || $userName === '') {
  $userName = 'User';
}
// 简单避免重名（不是强约束，仅做体验优化）
$suffix = 0;
while (true) {
  $try = $suffix === 0 ? $userName : $userName.$suffix;
  $q = $db->prepare("SELECT userId FROM users WHERE userName = ? LIMIT 1");
  $q->bind_param('s', $try);
  $q->execute();
  $r = $q->get_result();
  $dup = ($r && $r->num_rows > 0);
  $q->close();
  if (!$dup) { $userName = $try; break; }
  $suffix++;
  if ($suffix > 1000) { break; } // 极端保护
}

// ---- 插入用户 ----
$hash = password_hash($pass, PASSWORD_DEFAULT);
$createdAt = date('Y-m-d H:i:s');

$ins = $db->prepare("
  INSERT INTO users (userName, userEmail, userPassword, createdAt, role)
  VALUES (?, ?, ?, ?, ?)
");
$ins->bind_param('sssss', $userName, $email, $hash, $createdAt, $role);

if (!$ins->execute()) {
  $err = 'Failed to register: ' . $db->error;
  $ins->close();
  back_with_msg($err);
}

$userId = $ins->insert_id;
$ins->close();

// ---- 自动登录（与 header / 其它页面统一会话字段）----
$_SESSION['userId']   = (int)$userId;
$_SESSION['userName'] = $userName;
$_SESSION['role']     = $role;

// ---- 成功后跳转（保留老师框架，不直接裸 echo）----
header('Location: browse.php');
exit;
