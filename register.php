<?php include_once("header.php")?>

<?php if (!empty($_SESSION['error_msg'])): ?>
  <div class="alert alert-danger my-3">
    <?= htmlspecialchars($_SESSION['error_msg']) ?>
  </div>
  <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<!-- YH: below codes added to show the error message  >
<?php if (!empty($_SESSION['error_msg'])): ?>
  <div class="alert alert-danger my-3">
    <?= htmlspecialchars($_SESSION['error_msg']) ?>
  </div>
  <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>


<div class="container">
<h2 class="my-3">Register new account</h2>

<!-- Registration form -->
<form method="POST" action="process_registration.php">

  <!-- Username -->
  <div class="form-group row">
    <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="username" name="userName" placeholder="Choose a username" required>
      <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

  <!-- Email -->
  <div class="form-group row">
    <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
    <div class="col-sm-10">
      <input type="email" class="form-control" id="email" name="userEmail" placeholder="Email" required>
      <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

<!-- Password -->
<div class="form-group row">
  <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
  <div class="col-sm-10">
    <input type="password" class="form-control" id="password" name="userPassword" placeholder="Password" required>
    <small class="form-text text-muted">
      <span class="text-danger">* Required.</span> Must be at least 6 characters.
    </small>
  </div>
</div>

<div class="form-group row">
  <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
  <div class="col-sm-10">
    <input type="password" class="form-control" id="passwordConfirmation" name="userPasswordConfirmation" placeholder="Enter password again" required>
    <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
  </div>
</div>

  <!-- Phone Number -->
  <div class="form-group row">
    <label for="phoneNumber" class="col-sm-2 col-form-label text-right">Phone Number</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="phoneNumber" name="userPhoneNumber" placeholder="Phone Number" required>
      <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

  <!-- Date of Birth -->
  <div class="form-group row">
    <label for="dob" class="col-sm-2 col-form-label text-right">Date Of Birth</label>
    <div class="col-sm-10">
      <input type="date" class="form-control" id="dob" name="userDob" placeholder="Select your date of birth" required>
      <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

  <!-- House Number -->
  <div class="form-group row">
    <label for="houseNo" class="col-sm-2 col-form-label text-right">House Number</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="houseNo" name="userHouseNo" placeholder="House Number" required>
      <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

  <!-- Street -->
  <div class="form-group row">
    <label for="street" class="col-sm-2 col-form-label text-right">Street</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="street" name="userStreet" placeholder="Street" required>
      <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

  <!-- City -->
  <div class="form-group row">
    <label for="city" class="col-sm-2 col-form-label text-right">City</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="city" name="userCity" placeholder="City" required>
      <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

  <!-- Postcode -->
  <div class="form-group row">
    <label for="postcode" class="col-sm-2 col-form-label text-right">Postcode</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="postcode" name="userPostcode" placeholder="Postcode" required>
      <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

  <!-- Submit -->
  <div class="form-group row">
    <button type="submit" class="btn btn-primary form-control">Register</button>
  </div>

</form>

<div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a></div>

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