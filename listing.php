<link rel="stylesheet" href="css/custom_2.css">

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "utilities.php";
require_once "Auction_functions.php";
require_once "image_functions.php";
require_once "bid_functions.php";

session_start();

$itemId    = $_GET['itemId'] ?? ($_GET['item_id'] ?? null);
$auctionId = $_GET['auctionId'] ?? null;

if ($auctionId && !$itemId) {
    $auction = getAuctionById($auctionId);
    if ($auction) {
        $itemId = $auction['itemId'];
    }
}

if ($itemId && !$auctionId) {
    $auction = getAuctionByItemId($itemId);
    if ($auction) {
        $auctionId = $auction['auctionId'];
    }
}


if (!$itemId || !$auctionId) {
    header("Location: browse.php?error=" . urlencode("Invalid item."));
    exit;
}


refreshAuctionStatus($auctionId);

$auction = getAuctionById($auctionId);
if (!$auction) {
    header("Location: browse.php?error=" . urlencode("Auction not found."));
    exit;
}

$itemId = $auction["itemId"];
$item = getItemById($itemId);
if (!$item) {
    header("Location: browse.php?error=" . urlencode("Item not found."));
    exit;
}

$startPrice = (float)$auction['startPrice'];
$endTime    = new DateTime($auction['auctionEndTime']);
$startTime  = new DateTime($auction['auctionStartTime']);
$now        = new DateTime();

$primaryImage = getPrimaryImage($itemId);
$allImages    = getImagesByItemId($itemId);

$highestBid = getHighestBidForAuction($auctionId);
$bidHistory = getBidsByAuctionId($auctionId);

$currentPrice = $highestBid ? (float)$highestBid['bidPrice'] : $startPrice;

// fetch seller name for the listing link
$db = get_db_connection();
$sellerId = $item['sellerId'] ?? $auction['sellerId'] ?? null;
$sellerName = 'Seller';
if ($sellerId) {
    $stmt = $db->prepare("SELECT userName FROM users WHERE userId = ?");
    if ($stmt) {
        $stmt->bind_param("i", $sellerId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row && !empty($row['userName'])) {
            $sellerName = $row['userName'];
        }
        $stmt->close();
    }
}

include_once "header.php";

$reservePrice = isset($auction['reservedPrice']) ? (float)$auction['reservedPrice'] : 0.0;

$hasHighestBid   = !empty($highestBid);
$isUnsuccessful  = false;

//No bids
if (!$hasHighestBid) {
    $isUnsuccessful = true;
}
//have bids but not achieve reserve price
elseif ($reservePrice > 0 && (float)$highestBid['bidPrice'] < $reservePrice) {
    $isUnsuccessful = true;
}

$hasValidWinner = !$isUnsuccessful;

?>
<div class="container mt-4">

<?php
require_once "watchlist_funcs.php";
$userId = $_SESSION['userId'] ?? null;
$isWatching = $userId ? isInWatchlist($userId, $auctionId) : false;
?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'relisted'): ?>
    <div class="alert alert-success">Your Item has been successfully relisted!</div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'bid'): ?>
    <div class="alert alert-success">
        Your bid has been placed successfully!
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= h($_GET['error']) ?></div>
<?php endif; ?>


<!--html-->
<div class="row mt-3nb product-page">

<!--left-->
<div class="col-md-8">

  <?php if ($primaryImage): ?>
      <img src="<?= h($primaryImage['imageUrl']) ?>" style="max-width: 300px; border-radius: 6px;">
  <?php endif; ?>

  <div class="d-flex gap-2 my-3">
      <?php foreach ($allImages as $img): ?>
          <img src="<?= h($img['imageUrl']) ?>" style="max-width: 120px; border-radius: 4px;">
      <?php endforeach; ?>
  </div>
</div>


<!--right-->
<div class="col-md-4">

<h3 class="item-name"><?= h($auction['itemName']) ?></h3>
<a class="nav-link" href="seller_profile.php?sellerId=<?= (int)$sellerId ?>"><?= h($sellerName) ?>'s Profile</a>

<?php $status = $auction['auctionStatus']; ?>

<div class="mb-3">
    <?php if (!$userId): ?>
        <a href="login.php" class="watch-btn watch-btn-outline">Login to watch</a>
    <?php elseif (!$isWatching): ?>
        <a href="watchlist_add.php?auctionId=<?= $auctionId ?>" 
           class="bid-btn">♡ Add to Watchlist</a>
    <?php else: ?>
        <a href="watchlist_remove.php?auctionId=<?= $auctionId ?>" 
           class="bid-btn">♥ Remove</a>
    <?php endif; ?>
</div>


  <h5>Description:</h5>
  <p><?= nl2br(h($auction['itemDescription'])) ?></p>

  <?php if ($item['itemStatus'] === 'inactive' && $userId == $item['sellerId']): ?>
      <a href="edit_item.php?itemId=<?= $item['itemId'] ?>" class="edit-btn edit-btn-small mb-3">Edit Item</a>
  <?php endif; ?>

  <h5>Bid History:</h5>
  <?php if (empty($bidHistory)): ?>
      <p>No bids yet. Be the first!</p>
  <?php else: ?>
      <table class="table table-sm table-bordered">
          <tr><th>Bidder</th><th>Amount</th><th>Time</th></tr>
          <?php foreach ($bidHistory as $bid): ?>
              <tr>
                  <td><?= h($bid['buyerName']) ?></td>
                  <td>£<?= number_format($bid['bidPrice'],2) ?></td>
                  <td><?= h($bid['bidTime']) ?></td>
              </tr>
          <?php endforeach; ?>
      </table>
  <?php endif; ?>

<!--relist-->
<?php if ($status === 'relisted'): ?>
    <div class="auction-ended-box"><strong>This auction has been re-listed.</strong></div>

    <?php if ($userId == $item['sellerId']): ?>
        <button class="auction-info-line" disabled>Already re-listed</button>
    <?php endif; ?>

 
<?php if ($item['itemStatus'] === 'inactive' && $userId == $item['sellerId']): ?>
      <a href="edit_item.php?itemId=<?= $item['itemId'] ?>" class="bid-btn">Edit Item</a>
  <?php endif; ?>

<!--ended-->
<?php elseif ($status === 'ended'): ?>

    <div class="auction-ended-box"><strong>This auction has ended.</strong></div>

    <?php if ($hasValidWinner): ?>

        <?php
        $winnerId = $highestBid['buyerId'];
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT userName FROM users WHERE userId = ?");
        $stmt->bind_param("i", $winnerId);
        $stmt->execute();
        $winnerRow = $stmt->get_result()->fetch_assoc();
        $winnerName = $winnerRow['userName'] ?? ('User '.$winnerId);
        ?>
        <p><strong>Winner:</strong> <?= h($winnerName) ?></p>
        <p><strong>Final Price:</strong> £<?= number_format($highestBid['bidPrice'],2) ?></p>

    <?php else: ?>

        <!--unsuccessful auction-->
        <?php if ($hasHighestBid && $reservePrice > 0 && $highestBid['bidPrice'] < $reservePrice): ?>
            <p>
            Highest bid £<?= number_format($highestBid['bidPrice'],2) ?> 
            did not meet the reserve price £<?= number_format($reservePrice,2) ?>.  
            No winner.
            </p>

        <?php elseif ($hasHighestBid): ?>
            <p class="auction-info-line">Highest bid £<?= number_format($highestBid['bidPrice'],2) ?>. No winner.</p>
        <?php else: ?>
            <p lass="auction-info-line">No bids were placed.</p>
        <?php endif; ?>

        <?php if ($userId == $item['sellerId'] && $isUnsuccessful): ?>
            <a href="relist.php?auctionId=<?= $auctionId ?>" class="relist-btn">Re-list this item</a>
        <?php endif; ?>

    <?php endif; ?>


<!--scheduled-->
<?php elseif ($status === 'scheduled'): ?>

    <div class="alert auction-alert-info"><strong>This auction has not started yet.</strong></div>
    <p>Starts: <?= $startTime->format('j M H:i') ?> (in <?= $now->diff($startTime)->format('%ad %hh %im') ?>)</p>
    <p class="lead">Starting Price: £<?= number_format($startPrice,2) ?></p>


<!--running-->
<?php elseif ($status === 'running'): ?>

    <p class="auction-info-line">
        Ends <?= $endTime->format('j M H:i') ?>  
        (in <?= display_time_remaining($now->diff($endTime)) ?>)
    </p>

    <p class="auction-info-line">Current bid: £<?= number_format($currentPrice,2) ?></p>

    <?php if ($userId == $auction['sellerId']): ?>
        <p class="auction-info-line text-warning">You are the seller and cannot bid.</p>
    <?php else: ?>

        <p class="bid-note">• Please double-check that your bid amount is correct before submitting.</p>
        <p class="bid-note">• Please confirm your decision carefully, as bids cannot be cancelled or withdrawn.</p>

        <form method="POST" action="place_bid.php" class="mt-2">
            <input type="hidden" name="auctionId" value="<?= $auctionId ?>">
            <div class="bid-input-wrap">
            <span class="input-group-text">£</span>
            <input type="number" name="bidPrice" class="form-control" step="0.01" required>
            </div>
            <button class="btn bid-btn mt-2">Place bid</button>
        </form>
    <?php endif; ?>

<?php endif; ?>



</div>

</div>
</div>

