<?php include_once("header.php")?>
<link rel="stylesheet" href="css/custom_2.css">


<?php if (!empty($_SESSION['error_msg'])): ?>
  <div class="alert alert-danger my-3">
    <?= htmlspecialchars($_SESSION['error_msg']) ?>
  </div>
  <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<!-- YH: below codes added to show the error message  -->
<?php if (!empty($_SESSION['error_msg'])): ?>
  <div class="alert alert-danger my-3">
    <?= htmlspecialchars($_SESSION['error_msg']) ?>
  </div>
  <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<div class="register-container">
  <h1 class="register-title">Create Account</h1>

    <!-- Registration form -->
    <form method="POST" action="process_registration.php">

      <!-- Username -->
      <div class="form-group">
        <label for="username" class="register-label">Username</label>
          <input type="text" class="register-input" id="username" name="userName" placeholder="e.g. JohnDoe111" required>
          <small class="text-muted"><span class="text-danger">* Required.</span></small>
        </div>

      <!-- Email -->
      <div class="form-group">
        <label for="email" class="register-label">Email</label>
          <input type="email" class="register-input" id="email" name="userEmail" placeholder="e.g. name@email.com" required>
          <small class="text-muted"><span class="text-danger">* Required.</span></small>
      </div>

    <!-- Password -->
    <div class="form-group ">
      <label for="password" class="register-label">Password</label>
        <input type="password" class="register-input" id="password" name="userPassword" placeholder="Password" required>
        <small class="text-muted">
          <span class="text-danger">* Required.</span> Must be at least 6 characters.
        </small>
    </div>

    <div class="form-group">
      <label for="passwordConfirmation" class="register-label">Repeat password</label>
        <input type="password" class="register-input" id="passwordConfirmation" name="userPasswordConfirmation" placeholder="Enter password again" required>
        <small class="text-muted"><span class="text-danger">* Required.</span></small>
    </div>

      <!-- Phone Number -->
      <div class="form-group">
        <label for="phoneNumber" class="register-label">Phone Number</label>
          <input type="text" class="register-input" id="phoneNumber" name="userPhoneNumber" placeholder="Phone Number" required>
          <small class="text-muted"><span class="text-danger">* Required.</span></small>
      </div>

      <!-- Date of Birth -->
      <div class="form-group">
        <label for="dob" class="register-label">Date Of Birth</label>
          <input type="date" class="register-input" id="dob" name="userDob" placeholder="Select your date of birth" required>
          <small class="text-muted"><span class="text-danger">* Required.</span></small>
      </div>

      <!-- House Number -->
      <div class="form-group">
        <label for="houseNo" class="register-label">House Number</label>
          <input type="text" class="register-input" id="houseNo" name="userHouseNo" placeholder="House Number" required>
          <small class="text-muted"><span class="text-danger">* Required.</span></small>
      </div>

      <!-- Street -->
      <div class="form-group">
        <label for="street" class="register-label">Street</label>
          <input type="text" class="register-input" id="street" name="userStreet" placeholder="Street" required>
          <small class="text-muted"><span class="text-danger">* Required.</span></small>
      </div>

      <!-- City -->
      <div class="form-group">
        <label for="city" class="register-label">City</label>
          <input type="text" class="register-input" id="city" name="userCity" placeholder="City" required>
          <small class="text-muted"><span class="text-danger">* Required.</span></small>
      </div>

      <!-- Postcode -->
      <div class="form-group">
        <label for="postcode" class="register-label">Postcode</label>
          <input type="text" class="register-input" id="postcode" name="userPostcode" placeholder="Postcode" required>
          <small class="text-muted"><span class="text-danger">* Required.</span></small>
      </div>

      <!-- Submit -->
      <div class="form-group" style="display: flex; justify-content: center;">
        <button type="submit" class="btn-outline-box">Register</button>
      </div>

    </form>
</div>

  <div class="login-link">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a></div>

<!-- Client-side validation -->
<script>
// Password match
const password = document.getElementById('password');
const passwordConfirmation = document.getElementById('passwordConfirmation');
function checkPasswords() {
    if (password.value && passwordConfirmation.value) {
        passwordConfirmation.setCustomValidity(password.value !== passwordConfirmation.value ? "Passwords do not match" : "");
    }
}
password.addEventListener('input', checkPasswords);
passwordConfirmation.addEventListener('input', checkPasswords);

// Age check
const dob = document.getElementById('dob');
function checkAge() {
    if (!dob.value) return;
    const birthDate = new Date(dob.value);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
    dob.setCustomValidity(age < 18 ? "You must be at least 18 years old." : "");
}
dob.addEventListener('change', checkAge);
</script>

<?php include_once("footer.php")?>