<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("utilities.php");
require_once("Auction_functions.php");
require_once("image_functions.php");
require_once("bid_functions.php");
session_start();

$auctionId = $_GET['auctionId'] ?? null;
$itemId    = $_GET['item_id'] ?? null;

if (!$auctionId && $itemId) {
    $auction = getAuctionByItemId($itemId);
    if ($auction) {
        $auctionId = $auction['auctionId'];
    }
}

if (!$auctionId) {
    echo "<p>Invalid auctionId.</p>";
    exit;
}

// refresh auction status
refreshAuctionStatus($auctionId);

// get auction info
$auction = getAuctionById($auctionId);
if (!$auction) {
    echo "<p>Auction not found.</p>";
    exit;
}

// YH: add the start_time for scheduled auction
$itemId      = $auction['itemId'];
$startPrice  = (float)$auction['startPrice'];
$endTime     = new DateTime($auction['auctionEndTime']);
$now         = new DateTime();
$start_time = new DateTime($auction['auctionStartTime']);

// images
$primaryImage = getPrimaryImage($itemId);
$allImages    = getImagesByItemId($itemId);

// get bid info
$highestBid = getHighestBidForAuction($auctionId);
$bidHistory = getBidsByAuctionId($auctionId);

$currentPrice = $highestBid ? (float)$highestBid['bidPrice'] : $startPrice;

// header
include_once("header.php");

// time remaining
$timeRemaining = "";
if ($now < $endTime) {
    $interval = $now->diff($endTime);
    $timeRemaining = " (in " . display_time_remaining($interval) . ")";
}
?>

<div class="container mt-4">

<?php
require_once 'watchlist_funcs.php';

$userId = $_SESSION['userId'] ?? null;
$isWatching = $userId ? isInWatchlist($userId, $auction['auctionId']) : false;
?>

<div>
    <?php if (!$userId): ?>
        <a href="login.php" class="btn btn-outline-primary btn-sm">
            Login to watch
        </a>
    <?php elseif (!$isWatching): ?>
        <a href="watchlist_add.php?auctionId=<?= $auction['auctionId'] ?>" 
           class="btn btn-outline-success btn-sm">
            ♡ Add to Watchlist
        </a>
    <?php else: ?>
        <a href="watchlist_remove.php?auctionId=<?= $auction['auctionId'] ?>" 
           class="btn btn-danger btn-sm">
            ♥ Remove
        </a>
    <?php endif; ?>
</div>


  <!-- Success / Error message -->
  <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success"><?= h($_GET['success']) ?></div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger"><?= h($_GET['error']) ?></div>
  <?php endif; ?>

  <h3><?= h($auction['itemName']) ?></h3>

  <div class="row mt-3">

    <!-- LEFT SIDE -->
    <div class="col-md-8">

      <?php if (!empty($primaryImage)): ?>
          <img src="<?= h($primaryImage['imageUrl']) ?>" style="max-width: 300px; border-radius: 6px;">
      <?php endif; ?>

      <div class="d-flex gap-2 my-3">
        <?php foreach ($allImages as $img): ?>
            <img src="<?= h($img['imageUrl']) ?>" style="max-width: 120px; border-radius: 4px;">
        <?php endforeach; ?>
      </div>

      <h5>Description:</h5>
      <p><?= nl2br(h($auction['itemDescription'])) ?></p>

      <h5>Bid History:</h5>
      <?php if (empty($bidHistory)): ?>
          <p>No bids yet. Be the first!</p>
      <?php else: ?>
          <table class="table table-sm table-bordered">
            <tr><th>Bidder</th><th>Amount</th><th>Time</th></tr>
            <?php foreach ($bidHistory as $bid): ?>
                <tr>
                  <td><?= h($bid['buyerName']) ?></td>
                  <td>£<?= number_format($bid['bidPrice'], 2) ?></td>
                  <td><?= h($bid['bidTime']) ?></td>
                </tr>
            <?php endforeach; ?>
          </table>
      <?php endif; ?>

    </div>

<!-- RIGHT SIDE: BIDDING PANEL -->
<div class="col-md-4">

    <?php 
    $status = $auction['auctionStatus'];
    ?>

    <!--  Auction ended -->
    <?php if ($status === 'ended'): ?>

        <div class="alert alert-secondary">
            <strong>This auction has ended.</strong>
        </div>

        <?php if ($highestBid): ?>
            <p>Winner: User <?= h($highestBid['buyerId']) ?></p>
            <p>Final Price: £<?= number_format($highestBid['bidPrice'],2) ?></p>
        <?php else: ?>
            <p>No bids were placed.</p>
        <?php endif; ?>

    <!-- YH DEBUG: Auction NOT STARTED (scheduled) -->
    <?php elseif ($status === 'scheduled'): ?>

        <div class="alert alert-info">
            <strong>This auction has not started yet.</strong><br>
            Starts on: <?= $start_time->format('j M H:i') ?><br>
            (in <?= $now->diff($start_time)->format('%ad %hh %im') ?>)
        </div>

        <p class="lead">Starting Price: £<?= number_format($startPrice,2) ?></p>

        <!-- no bid form -->
        <p class="text-muted">Bidding will open once the auction starts.</p>

    <!-- Auction is running -->
    <?php elseif ($status === 'running'): ?>

        <p class="text-muted">
            Auction ends <?= $endTime->format('j M H:i') ?>
            (in <?= display_time_remaining($now->diff($endTime)) ?>)
        </p>

        <p class="lead">Current bid: £<?= number_format($currentPrice,2) ?></p>

        <?php if (isset($_SESSION['userId']) && $_SESSION['userId'] == $auction['sellerId']): ?>

            <p class="text-warning">You are the seller and cannot bid.</p>

        <?php else: ?>

            <form method="POST" action="place_bid.php">
                <input type="hidden" name="auctionId" value="<?= $auctionId ?>">
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="bidPrice" class="form-control" step="0.01" min="0" required>
                </div>
                <button class="btn btn-primary mt-2">Place bid</button>
            </form>

        <?php endif; ?>

    <?php endif; ?>

</div>


<?php include_once("footer.php"); ?>
