<?php
// 一定要在任何输出之前
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 我们需要 h() 这个转义函数；在 utilities.php 里已经实现了
// 仅引入函数，不会自动连库，安全
require_once __DIR__ . '/utilities.php';

// 当前页地址，用于登录成功后跳回
$currentUrl = $_SERVER['REQUEST_URI'] ?? 'browse.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- 样式保持你们原样 -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/custom.css">

  <title>[My Auction Site] <!--CHANGEME!--></title>
</head>

<body>

<!-- 顶部浅色导航：站点名 + 右侧登录/登出 -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
  <a class="navbar-brand" href="#">Site Name <!--CHANGEME!--></a>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item d-flex align-items-center">
<?php if (!empty($_SESSION['userId'])): ?>
      <span class="nav-link">Hi, <?= h($_SESSION['userName'] ?? 'User') ?></span>
      <a class="nav-link" href="logout.php">Logout</a>
<?php else: ?>
      <!-- 保持原来用 Modal 登录，同时补上 Register 链接 -->
      <button type="button" class="btn nav-link" data-toggle="modal" data-target="#loginModal">Login</button>
      <a class="nav-link" href="register.php">Register</a>
<?php endif; ?>
    </li>
  </ul>
</nav>

<!-- 第二条深色导航：功能菜单（外观不改） -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <ul class="navbar-nav align-middle">
    <li class="nav-item mx-1"><a class="nav-link" href="browse.php">Browse</a></li>

<?php if (!empty($_SESSION['userId'])): ?>
    <!-- 登录后先都显示（等你们在 login_result.php 正式写入 role 后再细分） -->
    <li class="nav-item mx-1"><a class="nav-link" href="mybids.php">My Bids</a></li>
    <li class="nav-item mx-1"><a class="nav-link" href="mylistings.php">My Listings</a></li>
    <li class="nav-item ml-3"><a class="nav-link btn border-light" href="create_auction.php">+ Create auction</a></li>
<?php endif; ?>
  </ul>
</nav>

<!-- Login 模态框（保留你们原样，只补“name”与隐藏重定向） -->
<div class="modal fade" id="loginModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Login</h4>
      </div>

      <div class="modal-body">
        <form method="POST" action="login_result.php">
          <input type="hidden" name="redirect" value="<?= h($currentUrl) ?>">
          <div class="form-group">
            <label for="loginEmail">Email</label>
            <!-- ★ 关键：补 name="email" -->
            <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Email" required>
          </div>
          <div class="form-group">
            <label for="loginPassword">Password</label>
            <!-- ★ 关键：补 name="password" -->
            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Password" required>
          </div>
          <button type="submit" class="btn btn-primary form-control">Sign in</button>
        </form>
        <div class="text-center">or <a href="register.php">create an account</a></div>
      </div>

    </div>
  </div>
</div>
