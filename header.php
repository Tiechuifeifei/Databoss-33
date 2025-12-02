<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/utilities.php';

$currentUrl = $_SERVER['REQUEST_URI'] ?? 'browse.php';
?>
<?php

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/utilities.php';

$currentUrl = $_SERVER['REQUEST_URI'] ?? 'index.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/starRating_2.css">
  <link rel="stylesheet" href="css/custom_2.css">

  <title>[My Auction Site] <!--CHANGEME!--></title>
</head>

<body class="p-5">

<!--Logo in the middle-->
<div class="container-fluid bg-white pt-3 pb-2">
    <div class="row align-items-center">
        <div class="col-4"></div>
        <div class="col-4 text-center">
            <a href="index.php" class="header-logo">Auction</a>
        </div>
        
        <!--login and user infomation-->
        <div class="col-4 text-right d-flex justify-content-end align-items-center">
            <?php if (!empty($_SESSION['userId'])): ?>
                <span class="hi-user">Hi, <?= h($_SESSION['userName'] ?? 'User') ?></span>
                <a class="nav-link text-dark px-1" href="logout.php">Log out</a>
            <?php else: ?>
                <a type="button" class="nav-link text-dark px-2" data-toggle="modal" data-target="#loginModal">
                  Login</a>
                <span class="text-muted mx-1">|</span>
                <a class="nav-link text-dark px-2" href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!--Header buttons to different pages-->
<nav class="navbar navbar-expand-lg navbar-light bg-white pb-3">
  <button class="navbar-toggler mx-auto" type="button" data-toggle="collapse" data-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse justify-content-center" id="mainNav">
    <ul class="navbar-nav">
      <li class="nav-item mx-3"><a class="nav-link" href="index.php">Home</a></li>
      <li class="nav-item mx-3"><a class="nav-link" href="browse.php">Browse</a></li>
      
      <?php if (!empty($_SESSION['userId'])): ?>
        <li class="nav-item mx-3"><a class="nav-link" href="mybids.php">My Bids</a></li>

    <li class="nav-item mx-3"><a class="nav-link" href="recommendations.php">Recommendations</a></li>

    
        <li class="nav-item mx-3"><a class="nav-link" href="mylistings.php">My Listings</a></li>
        <li class="nav-item mx-3"><a class="nav-link" href="profile.php">My Profile</a></li>
      <?php endif; ?>
    </ul>
    
    <?php if (!empty($_SESSION['userId'])): ?>
      <a class="btn btn-outline-dark" style="padding:4px 12px;font-size:0.95rem;" 
        href="create_item.php">+ Create auction</a>

    <?php endif; ?>
  </div>
</nav>

<!--login modal code-->
<div class="modal fade mt-3 " id="loginModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="login-container">
        <h4 class="login-title">Login</h4>
      </div>
      <div class="modal-body">
        <?php 
        if (!empty($_SESSION['flash_error'])): ?>
          <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof $ !== 'undefined') {
                    $('#loginModal').modal('show');
                }
            });
          </script>
          <div class="alert alert-danger">
            <?= h($_SESSION['flash_error']); ?>
          </div>
          <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

          <!--Login html-->
        <form method="POST" action="login_result.php">
          <input type="hidden" name="redirect" value="<?= h($currentUrl) ?>">

          <div class="form-group mb-3">
            <label for="loginEmail" class="login-label">
              email</label>
            <input type="email" class="form-control login-input py-4" id="loginEmail" name="userEmail"
             placeholder="name@exmple.com" required>
          </div>

          <div class="form-group mb-4">
            <label for="loginPassword" class="login-label">
              Password</label>
            <input type="password" class="form-control login-input" id="loginPassword"
             name="userPassword" placeholder="Enter Your Password" required>
          </div>
          <button type="submit" class="btn-black btn-primary form-control">Sign in</button>
        </form>
        <div class="text-center mt-4 text-muted small">Don't have an account?
          <a href="register.php" class="text-dark font-weight-bold" style="text-decoration:underline">create now</a></div>
      </div>
    </div>
  </div>
</div>

