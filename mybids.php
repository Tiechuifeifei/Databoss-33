<?php
//mybids.php
// Buyer's view: buyer can view all his/her bids on different auctions.

require_once __DIR__.'/utilities.php';
require_once __DIR__.'/bid_functions.php';

if (session_status()===PHP_SESSION_NONE){  session_start(); }
$userId = $_SESSION['userId'] ?? null;
include __DIR__.'/header.php';

// if not logedin, cannot check "my" bids
if(!$userId){
echo '<div class="container mt-4">
    <div class="alert alert-warning">Please log in to view your bids.</div>
</div>';
include __DIR__.'/footer.php';
exit;
}
//take all the bids records first.
$bids = getBidsByUser($userId);

?>
<div class="container mt-4">
<h2>My Bids</h2>

<?php if(empty($bids)): ?>
<p>You have not placed any bids yet.</p>
<?php else: ?>
<table class="table table-bordered table-striped mt-3">
<thead>
<tr>
<th>Item</th><th>Auction ID</th><th>Your bid</th>
<th>Highest bidder?</th>
<th>Current highest bid</th>
<th>Auction status</th>
<th>Bid time</th>
<th>Auction end time</th>
</tr>
</thead>
<tbody>
<?php foreach($bids as $b): ?>

<?php

$auctionId  = (int)$b['auctionId'];
$itemId     = (int)$b['itemId'];
$itemName   = $b['itemName'];
$yourBid    = (float)$b['bidPrice'];
$bidTime    = $b['bidTime'];
$status     = $b['auctionStatus'];
$endTime    = $b['auctionEndTime'];
$startPrice = (float)$b['startPrice'];

//find the CHB
$highestRow = getHighestBidForAuction($auctionId);

if($highestRow){
    $currentHighest = (float)$highestRow['bidPrice'];

    //main debug, find the buyerId, to avoid Undefined array key
    if (array_key_exists('buyerId', $highestRow)) {
        $isHighest = ((int)$highestRow['buyerId'] === (int)$userId);
    } else {
        //if no buyerId，check with comparing yourbid and CHB
        $isHighest = ($yourBid >= $currentHighest);
    }

} else {
    //if no any bids, the CHB=starting price, means user is not the HighestB
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
<tr>
<td>
<a href="listing.php?itemId=<?=$itemId?>">
<?= h($itemName) ?>
</a>
</td>
<td><?=$auctionId?></td>
<td>£<?=number_format($yourBid,2)?></td>
<td><?php echo $winnerText; ?></td>
<td>£<?=number_format($currentHighest,2)?></td>
<td><?=h($status)?></td>
<td><?=h($bidTime)?></td>
<td><?= $endTime ? h($endTime) : 'N/A' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
<?php include __DIR__.'/footer.php'; ?>
