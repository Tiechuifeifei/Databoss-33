<?php
// viewBidsOnMyAuctions.php
// seller"s view: seller can check all the bids on his/her auction/auctions.

require_once __DIR__.'/utilities.php';
require_once __DIR__.'/bid_functions.php';

if (session_status() === PHP_SESSION_NONE){
session_start();
}

$userId = $_SESSION['userId'] ?? null;

include __DIR__.'/header.php';

if (!$userId){
echo '<div class="container mt-4">
<div class="alert alert-warning">
Please log in to view bids on your auctions.
</div>
</div>';
include __DIR__.'/footer.php';
exit;
}

//get all seller's information
$rows = viewBidsOnMyAuctions((int)$userId);

//group by auctionId
$auctions = [];

//simply group the bids
foreach ($rows as $line){

$aid = (int)$line['auctionId'];

if (!isset($auctions[$aid])){
$auctions[$aid] = [
'auctionId'=> $aid,
'itemId'=> (int)$line['itemId'],
'itemName'=> $line['itemName'],
'auctionStatus'=> $line['auctionStatus'],
'auctionStartTime'=> $line['auctionStartTime'],
'auctionEndTime'=> $line['auctionEndTime'],
'startPrice'=> (float)$line['startPrice'],
'winningBidId'=> isset($line['winningBidId']) ? (int)$line['winningBidId'] : null,
'bids'=> [], //这一场拍卖下面所有的出价
];
}


//bidId will be null if there is no bid at all,
if (!empty($line['bidId'])){
$auctions[$aid]['bids'][] = [
'bidId' => (int)$line['bidId'],
'buyerId' => (int)$line['buyerId'],
'buyerName' => $line['userName'],   //来自users表
'bidPrice' => (float)$line['bidPrice'],
'bidTime' => $line['bidTime'],
];
}

}
?>

<div class="container mt-4">
<h3>Bids on My Auctions</h3>

<?php if (empty($auctions)): ?>

<div class="alert alert-info mt-3">
You haven't created any auctions yet.
</div>

<?php else: ?>

<ul class="list-group mt-3">

<?php foreach ($auctions as $auction): ?>

<?php
//infos of auctions 
$auctionId = $auction['auctionId'];
$itemId = $auction['itemId'];
$itemName = h($auction['itemName']);

$statusRaw = $auction['auctionStatus'];
$status = h($statusRaw);
$winningBidId = $auction['winningBidId'] ?? null;


$startPrice = $auction['startPrice'];

$startTime = $auction['auctionStartTime'] 
? new DateTime($auction['auctionStartTime']) 
: null;

$endTime = $auction['auctionEndTime']
? new DateTime($auction['auctionEndTime'])
: null;

//set current price as start price, then can find the highest later in the loop.
$currentPrice = $startPrice;

$winnerBuyerId = null;
$winnerName = null;

//finding the highest bid
if (!empty($auction['bids'])){
foreach ($auction['bids'] as $b){
if ($b['bidPrice'] > $currentPrice){
$currentPrice = $b['bidPrice'];
$winnerBuyerId = $b['buyerId'];
$winnerName = $b['buyerName'] ?? ('User #'.$b['buyerId']);
}
}
}

//count the bids
$bidCount = !empty($auction['bids']) ? count($auction['bids']) : 0;
?>

<li class="list-group-item">

<div class="d-flex justify-content-between align-items-center">
<div>
<strong>#<?= $auctionId ?> — <?= $itemName ?></strong><br>

<small class="text-muted">
<strong>Start price:</strong>
£<?= number_format($startPrice, 2) ?>
&nbsp;&nbsp;|&nbsp;&nbsp;
<strong>Current highest bid:</strong>
£<?= number_format($currentPrice, 2) ?>
&nbsp;&nbsp;|&nbsp;&nbsp;
<strong>Total bids:</strong>
<?= $bidCount ?>
</small>

<br>

<small class="text-muted">
<strong>Status:</strong> <?= $status ?>
<?php if ($endTime): ?>
&nbsp;&nbsp;|&nbsp;&nbsp;
<strong>Ends:</strong>
<?= $endTime->format('Y-m-d H:i') ?>
<?php endif; ?>
</small>

<?php
// show the winner and winning bid if auction finished
if ($statusRaw === 'ended'){
if ($bidCount > 0){
echo "<br><small class='text-success'><strong>Winner:</strong> ".h($winnerName)." &nbsp;|&nbsp; <strong>Final price:</strong> £".number_format($currentPrice,2)."</small>";
}else{
echo "<br><small class='text-muted'><strong>Auction ended with no bids.</strong></small>";
}
}
?>

</div>

<div>
<a class="btn btn-sm btn-outline-primary"
href="listing.php?auctionId=<?= $auctionId ?>">
Open Auction
</a>
</div>
</div>

<div class="mt-3">
<?php if (empty($auction['bids'])): ?>

<small class="text-muted">No bids yet for this auction.</small>

<?php else: ?>

<table class="table table-sm table-striped mt-2 mb-0">

<thead>
<tr>
<th>#</th>          <!--排名-->
<th>Buyer</th>
<th>Buyer ID</th>
<th>Bid Price</th>
<th>Bid Time</th>
</tr>
</thead>



<tbody>
<?php
$rank = 1;
foreach ($auction['bids'] as $b) {
$isWinningBid = (
    $statusRaw === 'ended'
    && $winningBidId !== null
    && (int)$b['bidId'] === (int)$winningBidId
);

$rowClass = $isWinningBid ? "table-success" : "";

echo "<tr class='{$rowClass}'>";
echo "<td>{$rank}</td>";
echo "<td>" . h($b['buyerName'] ?? ('User #'.$b['buyerId'])) . "</td>";
echo "<td>" . (int)$b['buyerId'] . "</td>";
echo "<td>£" . number_format($b['bidPrice'], 2) . "</td>";
echo "<td>" . h($b['bidTime']) . "</td>";
echo "</tr>";
    $rank++;
}
?>
</tbody>



</table>

<?php endif; ?>
</div>

</li>

<?php endforeach; ?>

</ul>

<?php endif; ?>
</div>

<?php include __DIR__.'/footer.php'; ?>
