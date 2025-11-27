<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "utilities.php";
require_once "Auction_functions.php";
require_once "image_functions.php";
require_once "bid_functions.php";

session_start();

// Leo debug: 
// unified the names, try to use itemId first.
$itemId    = $_GET['itemId'] ?? null;
$auctionId = $_GET['auctionId'] ?? null;

//1. if has auctionId but no itemId, then check item based on auction
if ($auctionId && !$itemId) {
    $auction = getAuctionById($auctionId);
    if ($auction) {
        $itemId = $auction['itemId'];
    }
}

//2.if itemId yes, but no auctionId; then check auction based on item.
if ($itemId && !$auctionId) {
    $auction = getAuctionByItemId($itemId);
    if ($auction) {
        $auctionId = $auction['auctionId'];
    }
}

//3. if no both, then return to browse page 
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
$itemId = $auction['itemId'];
$item   = getItemById($itemId);
if (!$item) {
    header("Location: browse.php?error=" . urlencode("Item not found."));
    exit;
}

// YH: add the start_time for scheduled auction
$startPrice = (float)$auction['startPrice'];
$endTime    = new DateTime($auction['auctionEndTime']);
$now        = new DateTime();
$start_time = new DateTime($auction['auctionStartTime']);

// images
$primaryImage = getPrimaryImage($itemId);
$allImages    = getImagesByItemId($itemId);

// get bid info
$highestBid = getHighestBidForAuction($auctionId);
$bidHistory = getBidsByAuctionId($auctionId);

$currentPrice = $highestBid ? (float)$highestBid['bidPrice'] : $startPrice;

// header
include_once "header.php";

// time remaining
$timeRemaining = "";
if ($now < $endTime) {
    $interval      = $now->diff($endTime);
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

  <?php if (isset($_GET['success']) && $_GET['success'] === 'relisted'): ?>
      <div class="alert alert-success">
        Your Item has been successfully relisted!
      </div>
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
      
    <?php if ($item['itemStatus'] === 'inactive' && $_SESSION['userId'] == $item['sellerId']): ?>
        <a href="edit_item.php?itemId=<?= $item['itemId'] ?>" 
            class="btn btn-secondary mb-3">
            Edit Item
        </a>
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
<?php if ($status === 'ended'): ?>
    <!-- status：ended -->
    <div class="alert alert-secondary">
        <strong>This auction has ended.</strong>
    </div>
    <?php
        //读取 reservePrice（没有则视为 0 = 无保留价）
        $reservePrice = isset($auction['reservedPrice']) ? (float)$auction['reservedPrice'] : 0.0;



//是否有最高出价
$hasHighestBid = !empty($highestBid);
//直接在这里自己判断“不成功拍卖”，不要再依赖 isAuctionUnsuccessful()
$isUnsuccessful = false;
//情况1：完全没人出价，则不成功
if (!$hasHighestBid) {
    $isUnsuccessful = true;
}
//情况2：有人出价，但没达到保留价,则不成功
elseif ($reservePrice > 0 && (float)$highestBid['bidPrice'] < $reservePrice) {
    $isUnsuccessful = true;
}
//如果不是“不成功拍卖”，那就是有一个有效 winner
$hasValidWinner = !$isUnsuccessful;

?>

    <?php if ($hasValidWinner): ?>

        <!-- 情况 1：有有效的 Winner -->
        <?php
            $winnerId = $highestBid['buyerId'];
            $db = get_db_connection();
            $stmt = $db->prepare("SELECT userName FROM users WHERE userId = ?");
            $stmt->bind_param("i", $winnerId);
            $stmt->execute();
            $winnerRow = $stmt->get_result()->fetch_assoc();
            $winnerName = $winnerRow['userName'] ?? ('User ' . $winnerId);
        ?>
        <p><strong>Winner:</strong> <?= h($winnerName) ?></p>
        <p><strong>Final Price:</strong> £<?= number_format($highestBid['bidPrice'], 2) ?></p>

    <?php else: ?>

        <!-- 情况 2 & 3：拍卖不成功（无人出价 或 有出价但没到保留价） -->
        <?php if ($hasHighestBid && $reservePrice > 0 && (float)$highestBid['bidPrice'] < $reservePrice): ?>
            <p>
              Highest bid £<?= number_format($highestBid['bidPrice'], 2) ?> 
              did not meet the reserve price of 
              £<?= number_format($reservePrice, 2) ?>.  
              There is no winner for this auction.
            </p>
        <?php elseif ($hasHighestBid): ?>
            <!-- 理论上不该走到这里，但以防没有 reserve 逻辑时的兜底 -->
            <p>
              Highest bid: £<?= number_format($highestBid['bidPrice'], 2) ?>.  
              There is no winner for this auction.
            </p>
        <?php else: ?>
            <p>No bids were placed.</p>
        <?php endif; ?>

        <?php if (
            isset($_SESSION['userId']) &&
            $_SESSION['userId'] == $item['sellerId'] &&
            $isUnsuccessful
        ): ?>
            <a href="relist.php?auctionId=<?= $auctionId ?>" 
               class="btn btn-warning mt-3">
               Re-list this item
            </a>
        <?php endif; ?>

    <?php endif; ?>



<?php elseif ($status === 'scheduled'): ?>

    <!-- scheduled：scheduled -->
    <div class="alert alert-info">
        <strong>This auction has not started yet.</strong><br>
        Starts on: <?= $start_time->format('j M H:i') ?><br>
        (in <?= $now->diff($start_time)->format('%ad %hh %im') ?>)
    </div>

    <p class="lead">Starting Price: £<?= number_format($startPrice,2) ?></p>
    <p class="text-muted">Bidding will open once the auction starts.</p>


<?php elseif ($status === 'running'): ?>

    <!-- status：running -->
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
                <input type="number" name="bidPrice" class="form-control" step="0.01" required>
            </div>
            <button class="btn btn-primary mt-2">Place bid</button>
        </form>
    <?php endif; ?>


<?php endif; ?>

</div>
