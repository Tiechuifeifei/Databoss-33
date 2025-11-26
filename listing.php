<?php include_once("header.php")?>
<?php require("utilities.php")?>
<?php require_once("db_connect.php") ?>
<?php require("Auction_functions.php") ?>
<?php require("watchlist_funcs.php") ?>
<?php require("bids_functions.php") ?>

<?php
  // PROFESSOR'S COMMENTS: Get info from the URL:
  // check if item_id exist
  if (!isset($_GET['item_id']) || !ctype_digit($_GET['item_id'])) {
  die("Invalid item id.");
}

  $item_id = $_GET['item_id'];

  // Use item_id to make a query to the database.
  $sql = "SELECT
  i.itemName,
  i.itemDescription,
  a.auctionId,
  a.auctionEndTime,
  (SELECT COUNT(*) FROM bids b WHERE b.auctionId = a.auctionId) AS num_bids
  FROM items i
  JOIN auctions a ON i.itemId = a.itemId
  WHERE i.itemId=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $item_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();

  // Use the query above to fill the variables
  $title = $row['itemName'];
  $description = $row['itemDescription'];
  $auction_id = $row['auctionId'];
  $current_price = getCurrentHighestPrice($auction_id);
  $num_bids = $row['num_bids'];
  $end_time = new DateTime($row['auctionEndTime']);
  
  // PROFESSOR'S COMMENTS:Calculate time to auction end:

  // This is for identify if the auction is ended or not
  // If the auction ended, get auction status selling price and etc.
  $now = new DateTime();
  
  if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }
  else if ($now > $end_time) {
    endAuction($auction_id);
    $sql="SELECT
    a.auctionStatus,
    a.soldPrice,
    b.buyerId
    FROM auctions a
    JOIN bids b ON a.winningBidId = b.bidId
    WHERE a.auctionId=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $auction_status = $row['auctionStatus'];
    $sold_price = $row['soldPrice'];
    $buyer = $row['buyerId'];
  }
  
  // Check if the auction is added in watchlist
  $has_session = isset($_SESSION['userId']);
  $watching = false;

  if($has_session){
    $user_id = $_SESSION['userId'];
    $sql = "SELECT 1
    FROM watchlist
    WHERE userId=? AND auctionId=? ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $auction_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0){
      $watching = true;
  }
}
?>


<div class="container">

<div class="row"> <!-- PROFESSOR'S: Row #1 with auction title + watch button -->
  <div class="col-sm-8"> <!--PROFESSOR'S: Left col -->
    <h2 class="my-3"><?php echo($title); ?></h2>
  </div>
  <div class="col-sm-4 align-self-center"> <!--PROFESSORS: Right col -->
<?php
  /* PROFESSOR'S: The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */
  if ($now < $end_time):
?>
    <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
    </div>
    <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
    </div>
<?php endif /* PROFESSORS: Print nothing otherwise */ ?>
  </div>
</div>

<div class="row"> <!-- PROFESSOR'S: Row #2 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- PROFESSOR'S: Left col with item info -->

    <div class="itemDescription">
    <?php echo($description); ?>
    </div>

  </div>

  <div class="col-sm-4"> <!--PROFESSOR'S: Right col with bidding info -->

    <p>
<?php if ($now > $end_time): ?>
     This auction ended <?php echo(date_format($end_time, 'j M H:i')) ?>
<!--This part is to print out informations after the auction (sold price, auction status and etc-->
      <?php if (!empty($sold_price)): ?>
        Final price: £<?php echo number_format($sold_price,2); ?><br>
        status: <?php echo htmlspecialchars($auction_status); ?>
<?php else: ?>
  This is auction ended without a winning bid.
<?php endif; ?>
     Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></p>  
    <p class="lead">Current bid: £<?php echo(number_format($current_price, 2)) ?></p>

    <!--PROFESSOR'S: Bidding form -->
    <form method="POST" action="place_bid.php">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text">£</span>
        </div>
	    <input type="number" class="form-control" id="bid" name="bidAmount">
      </div>
<!-- This is for adding a auction_id for the bid-->
      <input type="hidden" name="auction_id" value="<?php echo $auction_id;?>">
      <button type="submit" class="btn btn-primary form-control">Place bid</button>
    </form>
<?php endif ?>

  
  </div> <!--PROFESSOR'S: End of right col with bidding info -->

</div> <!--PROFESSOR'S: End of row #2 -->



<?php include_once("footer.php")?>


<script> 
//PROFESSOR'S: JavaScript functions: addToWatchlist and removeFromWatchlist.

function addToWatchlist(button) {
  console.log("These print statements are helpful for debugging btw");

  //PROFESSOR'S: This performs an asynchronous call to a PHP function using POST method.
  //PROFESSOR'S: Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($auction_id);?>]},

    success: 
      function (obj, textstatus) {
        //PROFESSOR'S Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          var mydiv = document.getElementById("watch_nowatch");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); //PROFESSOR'S: End of AJAX call

} //PROFESSOR'S: End of addToWatchlist func

function removeFromWatchlist(button) {
  //PROFESSOR'S: This performs an asynchronous call to a PHP function using POST method.
  //PROFESSOR'S: Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        //PROFESSOR'S: Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          var mydiv = document.getElementById("watch_watching");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); //PROFESSOR'S: End of AJAX call

} //PROFESSOR'S: End of addToWatchlist func
</script>