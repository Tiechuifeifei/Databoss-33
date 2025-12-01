<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/utilities.php';

// For login redirect back
$currentUrl = $_SERVER['REQUEST_URI'] ?? 'browse.php';
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/starRating.css">
  <title>Auction</title>
</head>

<body>

<!-- bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
  <a class="navbar-brand" href="browse.php">Auction</a>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item d-flex align-items-center">
      <?php if (!empty($_SESSION['userId'])): ?>
        <span class="nav-link">Hi, <?= h($_SESSION['userName'] ?? 'User') ?></span>
        <a class="nav-link" href="logout.php">Logout</a>
      <?php else: ?>
        <button type="button" class="btn nav-link" data-toggle="modal" data-target="#loginModal">
          Login
        </button>
        <a class="nav-link" href="register.php">Register</a>
      <?php endif; ?>
    </li>
  </ul>
</nav>

<!-- function manu -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <ul class="navbar-nav">

    <li class="nav-item mx-1">
      <a class="nav-link" href="browse.php">Browse</a>
    </li>

    <?php if (!empty($_SESSION['userId'])): ?>

      <li class="nav-item mx-1">
        <a class="nav-link" href="mybids.php">My Bids</a>
      </li>

      <li class="nav-item mx-1">
        <a class="nav-link" href="profile.php">My Profile</a>
      </li>

      <li class="nav-item mx-1">
        <a class="nav-link" href="mylistings.php">My Listings</a>
      </li>

      <li class="nav-item mx-1">
        <a class="nav-link" href="viewBidsOnMyAuctions.php">Bids on My Auctions</a>
      </li>

      <li class="nav-item ml-3">
        <a class="nav-link btn border-light" href="create_item.php">+ Create auction</a>
      </li>

    <?php endif; ?>

  </ul>
</nav>


<div class="modal fade" id="loginModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Login</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <div id="loginErrorBox" class="alert alert-danger d-none"></div>

        <form id="loginForm" method="POST" action="login_result.php">
          <input type="hidden" name="redirect" value="<?= h($currentUrl) ?>">

          <div class="form-group">
            <label for="loginEmail">Email</label>
            <input type="email" class="form-control" id="loginEmail" name="userEmail"
                   placeholder="Email" required>
          </div>

          <div class="form-group">
            <label for="loginPassword">Password</label>
            <input type="password" class="form-control" id="loginPassword" name="userPassword"
                   placeholder="Password" required>
          </div>

          <button type="submit" class="btn btn-primary form-control">Sign in</button>
        </form>

        <div class="text-center mt-2">
          or <a href="register.php">create an account</a>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var form    = document.getElementById('loginForm');
  var errorBox = document.getElementById('loginErrorBox');

  if (!form || !errorBox) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault(); 

    errorBox.textContent = '';
    errorBox.classList.add('d-none');

    var formData = new FormData(form);
    var action   = form.getAttribute('action') || 'login_result.php';

    fetch(action, {
      method: 'POST',
      body: formData
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.success) {
          window.location.href = data.redirect || 'browse.php';
        } else {
          errorBox.textContent = data.message || 'Login failed.';
          errorBox.classList.remove('d-none');
        }
      })
      .catch(function () {
        errorBox.textContent = 'Sorry, something went wrong. Please try again later.';
        errorBox.classList.remove('d-none');
      });
  });


  if (window.jQuery) {
    $('#loginModal').on('hidden.bs.modal show.bs.modal', function () {
      errorBox.textContent = '';
      errorBox.classList.add('d-none');
      form.reset();
    });
  }
});
</script>
