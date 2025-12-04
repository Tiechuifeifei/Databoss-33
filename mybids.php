  <link rel="stylesheet" href="css/custom_2.css">

<?php
// mybids.php
// Buyer's view: buyer can view all his/her bids on different auctions.

require_once __DIR__.'/utilities.php';
require_once __DIR__.'/bid_functions.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$userId = $_SESSION['userId'] ?? null;
include __DIR__.'/header.php';

if (!$userId) {
  echo '<div class="container mt-4">
    <div class="alert alert-warning">Please log in to view your bids.</div>
  </div>';
  include __DIR__.'/footer.php';
  exit;
}

$rawBids = getBidsByUser($userId);

$grouped = [];
foreach ($rawBids as $row) {
  $aid = (int)$row['auctionId'];
  if (!isset($grouped[$aid])) {
    $grouped[$aid] = $row;
  } else {
    if ((float)$row['bidPrice'] > (float)$grouped[$aid]['bidPrice']) {
      $grouped[$aid] = $row;
    }
  }
}

$bids = array_values($grouped);
?>
<tbody class="container mt-4 my-bid-page">
<h2 class="mybids-title">My Bids</h2>

<?php if(empty($bids)): ?>
<p class="mybids-empty">You have not placed any bids yet.</p>
<?php else: ?>
<table class="table table-bordered table-striped mt-3 mybids-table">
<thead class="mybids-header">
<tr>
<th class="mybids-head-text">Item</th>
<th class="mybids-head-text">Auction ID</th>
<th class="mybids-head-text">Your bid</th>
<th class="mybids-head-text">Highest bidder?</th>
<th class="mybids-head-text">Current highest bid</th>
<th class="mybids-head-text">Auction status</th>
<th class="mybids-head-text">Bid time</th>
<th class="mybids-head-text">Auction end time</th>
</tr>
</thead>

<tbody class="mybids-body">
<?php foreach($bids as $b): ?>

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

// to show the buyers if they won. If not, just tell them they didn't, and what is the CHB.
$winnerText = $isHighest ? '<strong>Yes</strong>' : 'No';

//if auction ended, change it to result reminder.
if ($status === 'ended') {
  if ($highestRow) {
    if ($isHighest) {
// if the buyer won
$winnerText = '<span class="text-success"><strong>Congratulations! You won this auction.</strong></span>';
} else {
// if buyer didn't win
$winnerText = '<span class="text-muted">Unfortunately, another buyer won this auction.</span>';
}
} else {
// if no bids until the auction ended
$winnerText = '<span class="text-muted">Auction ended with no bids.</span>';
}
}
?>
<tr class="mybids-row">
<td class="mybids-cell">
<a class="mybids-itemlink" href="listing.php?itemId=<?=$itemId?>">
<?= h($itemName) ?>
</a>
</td>
<td class="mybids-cell-text"><?=$auctionId?></td>
<td class="mybids-cell-text">£<?=number_format($yourBid,2)?></td>
<td class="mybids-cell-text"><?php echo $winnerText; ?></td>
<td class="mybids-cell-text">£<?=number_format($currentHighest,2)?></td>
<td class="mybids-cell-text"><?=h($status)?></td>
<td class="mybids-cell-text"><?=h($bidTime)?></td>
<td class="mybids-cell-text"><?= $endTime ? h($endTime) : 'N/A' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>

<?php include __DIR__.'/footer.php'; ?>

