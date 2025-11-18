<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/utilities.php';

$currentUrl = $_SERVER['REQUEST_URI'] ?? 'browse.php';
include_once __DIR__ . '/header.php';
?>

<div class="container my-5" style="max-width:560px;">
  <h3 class="mb-4">Login</h3>
  <form method="POST" action="login_result.php">
    <input type="hidden" name="redirect" value="<?= h($currentUrl) ?>">

    <div class="form-group">
      <label for="pageLoginEmail">Email</label>
      <input type="email" class="form-control" id="pageLoginEmail" name="email" placeholder="Email" required>
    </div>

    <div class="form-group">
      <label for="pageLoginPassword">Password</label>
      <input type="password" class="form-control" id="pageLoginPassword" name="password" placeholder="Password" required>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Sign in</button>
  </form>

  <div class="text-center mt-3">
    or <a href="register.php">create an account</a>
  </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>
