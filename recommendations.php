<?php include_once("header.php"); ?>
<?php require_once("utilities.php"); ?>

<div class="container">

<h2 class="my-3">Recommendations for you</h2>

<?php
if (session_status()===PHP_SESSION_NONE) {
  session_start();
}

$currentUserId = $_SESSION['userId'] ?? null;

if (!$currentUserId) {
echo '<div class="alert alert-warning mt-3">
Please log in to see your personalised recommendations.
</div>';
include_once("footer.php");
exit;
}

$db = get_db_connection();

$sql = "
SELECT 
a.auctionId,
a.itemId,
a.auctionStartTime,
a.auctionEndTime,
a.auctionStatus,
a.startPrice,
i.itemName,
COALESCE(MAX(b_all.bidPrice), a.startPrice) AS currentPrice,
COUNT(b_all.bidId) AS numBids,
COUNT(DISTINCT b_sim.buyerId) AS similar_user_count
FROM bids b_me
JOIN bids b_sim 
  ON b_me.auctionId = b_sim.auctionId
  AND b_sim.buyerId <> ?
JOIN bids b_target
  ON b_target.buyerId = b_sim.buyerId
JOIN auctions a
  ON a.auctionId = b_target.auctionId
JOIN items i
  ON a.itemId = i.itemId
LEFT JOIN bids b_all
  ON a.auctionId = b_all.auctionId
WHERE 
  b_me.buyerId = ?
  AND a.auctionStatus = 'running'
  AND a.auctionId NOT IN (
      SELECT DISTINCT auctionId
      FROM bids
      WHERE buyerId = ?
  )
GROUP BY 
  a.auctionId, a.itemId, a.auctionStartTime,
  a.auctionEndTime, a.auctionStatus, a.startPrice, i.itemName
ORDER BY 
  similar_user_count DESC,
  a.auctionEndTime ASC
LIMIT 20;
";

$stmt = $db->prepare($sql);
$stmt->bind_param("iii", $currentUserId, $currentUserId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo '<div class="alert alert-info mt-3">
  No recommendations available yet — place more bids to get personalised suggestions.
  </div>';
} else {
  echo '<ul class="list-group mb-5">';
  while ($row=$result->fetch_assoc()) {

  $auctionId=(int)$row['auctionId'];
  $title=$row['itemName'];
  $desc="";
  $price=(float)$row['currentPrice'];
  $num_bids=(int)$row['numBids'];
  $endTime=new DateTime($row['auctionEndTime']);
  $startTime=new DateTime($row['auctionStartTime']);
  $status= $row['auctionStatus'];
$winnerName=null;

print_listing_li(
$auctionId,
$title,
$desc,
$price,
$num_bids,
$endTime,
$startTime,
$status,
$winnerName
);
}
echo '</ul>';
}

$stmt->close();
$db->close();
?>

</div>

<?php include_once("footer.php"); ?>
