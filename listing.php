<?php
require_once("Auction_functions.php");
require_once("db_connect.php");
session_start();
?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("header.php");
require_once("utilities.php");

// YH DEBUG: to make it consistant, we should use auctionId instead of itemId
$auctionId = $_GET['auctionId'] ?? null;

if (!$auctionId) {
    echo "<p>Invalid auctionId.</p>";
    exit;
}
// refresh the auction status
refreshAuctionStatus($auctionId);

// get the details of the auction
$auction = getAuctionById($auctionId);

if (!$auction) {
    echo '<p class="text-danger">Auction not found.</p>';
    exit;
}

$itemId = $auction['itemId']; 

$db = get_db_connection();

// YH DEBUG: to make it consistant, we should use auctionId instead of itemId
// YH DEBUG: sellerId comes from item table not auction table
$sql = "
  SELECT 
    i.itemId,
    i.itemName,
    i.itemDescription,
    a.auctionId,
    a.startPrice,
    a.auctionStatus,
    a.auctionStartTime,
    a.auctionEndTime,
    i.sellerId,
    COALESCE((SELECT MAX(b.bidPrice) FROM bids b WHERE b.auctionId = a.auctionId), a.startPrice) AS currentPrice,
    (SELECT COUNT(*) FROM bids b2 WHERE b2.auctionId = a.auctionId) AS numBids
FROM items i
JOIN auctions a ON a.itemId = i.itemId
WHERE i.itemId = ?
LIMIT 1
";
$stmt = $db->prepare($sql);
if (!$stmt) {
  echo "<div class='container mt-4 text-danger'>Prepare failed: ".htmlspecialchars($db->error)."</div>";
  include_once("footer.php"); exit();
}
$stmt->bind_param("i", $itemId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
  echo "<div class='container mt-4 text-danger'>Auction not found for item_id=".htmlspecialchars($itemId)."</div>";
  include_once("footer.php"); exit();
}

$title         = $row['itemName'];
$description   = $row['itemDescription'];
$currentPrice  = (float)$row['currentPrice'];
$numBids       = (int)$row['numBids'];
$endTime       = new DateTime($row['auctionEndTime']);
$auctionId     = (int)$row['auctionId'];

$now = new DateTime();
$timeRemaining = '';
if ($now < $endTime) {
  $timeToEnd = date_diff($now, $endTime);
  $timeRemaining = ' (in ' . display_time_remaining($timeToEnd) . ')';
}

// watchlist 
$hasSession = true;
$watching   = false;
?>

<div class="container">

  <div class="row"><!-- Row #1 title + watch -->
    <div class="col-sm-8">
      <h2 class="my-3"><?php echo htmlspecialchars($title); ?></h2>
    </div>
    <div class="col-sm-4 align-self-center">
      <?php if ($now < $endTime): ?>
        <div id="watch_nowatch" <?php if ($hasSession && $watching) echo('style="display: none"');?> >
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
        </div>
        <div id="watch_watching" <?php if (!$hasSession || !$watching) echo('style="display: none"');?> >
          <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
          <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="row"><!-- Row #2 description + bidding -->
    <div class="col-sm-8">
      <div class="itemDescription">
        <?php echo nl2br(htmlspecialchars($description)); ?>
      </div>
    </div>

    <div class="col-sm-4">
      <p>
      <?php if ($now > $endTime): ?>
        This auction ended <?php echo(date_format($endTime, 'j M H:i')) ?>
      <?php else: ?>
        Auction ends <?php echo(date_format($endTime, 'j M H:i') . $timeRemaining) ?></p>
        <p class="lead">Current bid: £<?php echo number_format($currentPrice, 2) ?></p>

        <form method="POST" action="place_bid.php">
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">£</span>
            </div>
            <input type="number" class="form-control" id="bid" name="bid_amount" step="0.01" min="0" required>
            <input type="hidden" name="auction_id" value="<?php echo $auctionId; ?>">
          </div>
          <button type="submit" class="btn btn-primary form-control mt-2">Place bid</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include_once("footer.php"); ?>

<script>
// watchlist
function addToWatchlist() {
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo $itemId;?>]},
    success: function (obj) {
      var t = (obj || '').trim();
      if (t === 'success') { $("#watch_nowatch").hide(); $("#watch_watching").show(); }
    }
  });
}
function removeFromWatchlist() {
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo $itemId;?>]},
    success: function (obj) {
      var t = (obj || '').trim();
      if (t === 'success') { $("#watch_watching").hide(); $("#watch_nowatch").show(); }
    }
  });
}
</script>