<?php include_once("includes/header.php")?>

<div class="container">
<h2 class="my-3">Register new account</h2>

<!-- Create auction form -->
<form method="POST" action="process_registration.php">
  <div class="form-group row">
    <label for="accountType" class="col-sm-2 col-form-label text-right">Registering as a:</label>
	<div class="col-sm-10">
	  <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountBuyer" value="buyer" checked>
        <label class="form-check-label" for="accountBuyer">Buyer</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountSeller" value="seller">
        <label class="form-check-label" for="accountSeller">Seller</label>
      </div>
      <small id="accountTypeHelp" class="form-text-inline text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>

  <!-- email -->
  <div class="form-group row">
    <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
	<div class="col-sm-10">
      <input type="text" class="form-control" id="email" placeholder="Email">
      <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>

    <!-- password -->
  <div class="form-group row">
    <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="password" placeholder="Password">
      <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="passwordConfirmation" placeholder="Enter password again">
      <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

      <!-- phone Number -->
  <div class="form-group row">
    <label for="phoneNumber" class="col-sm-2 col-form-label text-right">Phone Number</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="phoneNumber" placeholder="Phone Number">
      <small id="phoneHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

        <!-- DOB -->
  <div class="form-group row">
    <label for="dob" class="col-sm-2 col-form-label text-right">Date Of Birth</label>
    <div class="col-sm-10">
      <input type="date" class="form-control" id="dob" placeholder="Select your date of birth">
      <small id="dobHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

        <!-- House Number -->
  <div class="form-group row">
    <label for="houseNo" class="col-sm-2 col-form-label text-right">House Number</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="houseNo" placeholder="House Number">
      <small id="houseNumberHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

<!-- 🛣 Street -->
  <div class="form-group row">
    <label for="street" class="col-sm-2 col-form-label text-right">Street</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="street" placeholder="Street">
      <small id="streeHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

<!-- 🏙 City -->
  <div class="form-group row">
    <label for="city" class="col-sm-2 col-form-label text-right">City</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="city" placeholder="City">
      <small id="cityHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

<!-- 📮 Postcode -->
  <div class="form-group row">
    <label for="postcode" class="col-sm-2 col-form-label text-right">Postcode</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="city" placeholder="Postcode">
      <small id="postcodeHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>

  <!-- 📮 Submit -->
  <div class="form-group row">
    <button type="submit" class="btn btn-primary form-control">Register</button>
  </div>
</form>

<div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>

</div>

<?php include_once("includes/footer.php")?>