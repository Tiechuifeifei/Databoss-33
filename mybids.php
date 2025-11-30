<?php
// mybids.php
// Buyer's view: buyer can view all his/her bids on different auctions.

require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/bid_functions.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$userId = $_SESSION['userId'] ?? null;
include __DIR__ . '/header.php';

// if not logged in, cannot check "my" bids
if (!$userId) {
  echo '<div class="container mt-4">
    <div class="alert alert-warning">Please log in to view your bids.</div>
  </div>';
  include __DIR__ . '/footer.php';
  exit;
}

// take all the bids records first.
$rawBids = getBidsByUser($userId);

// group by auctionId: for each auction, only keep YOUR highest bid
$grouped = []; // key: auctionId

foreach ($rawBids as $row) {
  $aid = (int)$row['auctionId'];

  // if this auction not seen before, save this bid
  if (!isset($grouped[$aid])) {
    $grouped[$aid] = $row;
  } else {
    // if this bidPrice is higher, replace the previous one
    if ((float)$row['bidPrice'] > (float)$grouped[$aid]['bidPrice']) {
      $grouped[$aid] = $row;
    }
  }
}

// now $bids = one row per auction
$bids = array_values($grouped);
?>

<div class="container mt-4">
  <h2>My Bids</h2>

  <?php if (empty($bids)): ?>
    <p>You have not placed any bids yet.</p>
  <?php else: ?>
    <?php foreach ($bids as $b): ?>

      <?php
      $auctionId = (int)$b['auctionId'];
      $itemId = (int)$b['itemId'];
      $itemName = $b['itemName'];
      $yourBid = (float)$b['bidPrice'];
      $bidTime = $b['bidTime'];
      $status = $b['auctionStatus'];
      $endTime = $b['auctionEndTime'];
      $startPrice = (float)$b['startPrice'];

      $highestRow = getHighestBidForAuction($auctionId);

      if ($highestRow) {
        $currentHighest = (float)$highestRow['bidPrice'];
        $isHighest = ((int)$highestRow['buyerId'] === (int)$userId);
      } else {
        $currentHighest = $startPrice;
        $isHighest = false;
      }

      $allBids = getBidsByAuctionId($auctionId);
      ?>

      <div class="card mb-4">
        <div class="card-header">
          <div><strong>#<?= $auctionId ?> — <?= h($itemName) ?></strong></div>

          <div>
            Status: <strong><?= h($status) ?></strong>
            <?php if ($endTime): ?>
              | Ends: <?= h($endTime) ?>
            <?php endif; ?>
          </div>

          <div>
            <?php
            if ($status === 'ended') {
              if ($isHighest) {
                echo '<span class="text-success"><strong>Congratulations! You won this auction.</strong></span>';
              } else {
                echo '<span class="text-muted"><strong>Unfortunately, another buyer won this auction.</strong></span>';
              }
            } else {
              if ($isHighest) {
                echo '<strong>You are currently the highest bidder.</strong>';
              } else {
                echo 'You are not the highest bidder yet.';
              }
            }
            ?>
          </div>
        </div>

        <div class="card-body">
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Buyer</th>
                <th>Buyer ID</th>
                <th>Bid Price</th>
                <th>Bid Time</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $index = 1;
              foreach ($allBids as $oneBid) {
                $rowClass = ((int)$oneBid['buyerId'] === (int)$userId) ? 'table-success' : '';
              ?>
                <tr class="<?= $rowClass ?>">
                  <td><?= $index++ ?></td>
                  <td><?= h($oneBid['buyerName']) ?></td>
                  <td><?= (int)$oneBid['buyerId'] ?></td>
                  <td>£<?= number_format($oneBid['bidPrice'], 2) ?></td>
                  <td><?= h($oneBid['bidTime']) ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>

          <a href="listing.php?itemId=<?= $itemId ?>" class="btn btn-outline-primary btn-sm">
            Open Auction
          </a>
        </div>
      </div>

    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
