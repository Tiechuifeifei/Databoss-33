<?php
// register.php
include_once __DIR__ . '/header.php';
?>

<div class="container my-5" style="max-width: 860px;">
  <h2 class="mb-4">Register new account</h2>

  <form method="POST" action="process_registration.php" novalidate>
    <div class="form-group mb-3">
      <label class="d-block mb-2">Registering as a:</label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="role" id="roleBuyer" value="buyer" checked>
        <label class="form-check-label" for="roleBuyer">Buyer</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="role" id="roleSeller" value="seller">
        <label class="form-check-label" for="roleSeller">Seller</label>
      </div>
    </div>

    <div class="form-group mb-3">
      <label for="email">Email</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
      <small class="form-text text-muted">Required.</small>
    </div>

    <div class="form-group mb-3">
      <label for="password">Password</label>
      <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
      <small class="form-text text-muted">Required.</small>
    </div>

    <div class="form-group mb-4">
      <label for="password2">Repeat password</label>
      <input type="password" class="form-control" id="password2" name="password2" placeholder="Enter password again" required>
      <small class="form-text text-muted">Required.</small>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Register</button>

    <p class="mt-3">
      Already have an account?
      <a href="login.php">Login</a>
    </p>
  </form>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>
