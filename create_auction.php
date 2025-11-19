<?php include_once("header.php")?>

<?php
/* (Uncomment this block to redirect people without selling privileges away from this page)
  // If user is not logged in or not a seller, they should not be able to
  // use this page.
  if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'seller') {
    header('Location: browse.php');
  }
*/
?>

<div class="container">

<!-- Create auction form -->
<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Create new auction</h2>
  <div class="card">
    <div class="card-body">
      <!-- Note: This form does not do any dynamic / client-side / 
      JavaScript-based validation of data. It only performs checking after 
      the form has been submitted, and only allows users to try once. You 
      can make this fancier using JavaScript to alert users of invalid data
      before they try to send it, but that kind of functionality should be
      extremely low-priority / only done after all database functions are
      complete. -->
      <form method="post" action="create_auction_result.php">

<div class="form-group row">
  <label class="col-sm-2 col-form-label text-right">Title of auction</label>
  <div class="col-sm-10">
    <input type="text" class="form-control" 
           id="auctionTitle" 
           name="auctionTitle" 
           placeholder="e.g. Black mountain bike">
  </div>
</div>

<div class="form-group row">
  <label class="col-sm-2 col-form-label text-right">Details</label>
  <div class="col-sm-10">
    <textarea class="form-control" 
              id="auctionDetails" 
              name="auctionDetails" 
              rows="4"></textarea>
  </div>
</div>

<div class="form-group row">
  <label class="col-sm-2 col-form-label text-right">Category</label>
  <div class="col-sm-10">
    <select class="form-control" 
            id="auctionCategory" 
            name="auctionCategory">
      <option value="">Choose...</option>
      <option value="101">Vintage Jewellery & Watches</option>
      <option value="102">Furniture</option>
      <option value="103">Classic Car & Automobilia</option>
      <option value="104">Vintage Fashion</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-sm-2 col-form-label text-right">Starting price</label>
  <div class="col-sm-10">
    <input type="number" class="form-control" 
           id="auctionStartPrice" 
           name="startPrice">
  </div>
</div>

<div class="form-group row">
  <label class="col-sm-2 col-form-label text-right">Reserve price</label>
  <div class="col-sm-10">
    <input type="number" class="form-control" 
           id="auctionReservePrice" 
           name="reservePrice">
  </div>
</div>

<div class="form-group row">
  <label for="auctionStartDate" class="col-sm-2 col-form-label text-right">Start date</label>
  <div class="col-sm-10">
    <input type="datetime-local"
           class="form-control"
           id="auctionStartDate"
           name="startTime">
    <small class="form-text text-muted">
      <span class="text-danger">* Required.</span> Day for the auction to start.
    </small>
  </div>
</div>

<div class="form-group row">
  <label class="col-sm-2 col-form-label text-right">End date</label>
  <div class="col-sm-10">
    <input type="datetime-local" class="form-control" 
           id="auctionEndDate" 
           name="endTime">
  </div>
</div>

<button type="submit" class="btn btn-primary form-control">Create Auction</button>

</form>



<?php include_once("footer.php")?>