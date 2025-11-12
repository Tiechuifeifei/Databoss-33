<?php
require_once __DIR__ . '/utilities.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$userId = $_SESSION['userId'] ?? null;

include __DIR__ . '/header.php';

if (!$userId) {
  echo '<div class="container mt-4"><div class="alert alert-warning">Please log in to view your bids.</div></div>';
  include __DIR__ . '/footer.php';
  exit;
}

$db = get_db_connection();

/*
  取出当前登录用户参与过的所有拍卖，并计算：
  - 你自己的最高出价 yourTopBid
  - 该拍卖的当前最高价 topPrice（无人出价则为 startPrice）
  - 基本信息（auctionId, itemId, auctionStatus, auctionEndTime）
*/
$sql = "
  SELECT
    a.auctionId,
    a.itemId,
    a.auctionStatus,
    a.auctionEndTime,
    COALESCE((
      SELECT MAX(b2.bidPrice) FROM bids b2 WHERE b2.auctionId = a.auctionId
    ), a.startPrice) AS topPrice,
    MAX(b1.bidPrice) AS yourTopBid
  FROM bids b1
  JOIN auctions a ON a.auctionId = b1.auctionId
  WHERE b1.buyerId = ?
  GROUP BY a.auctionId, a.itemId, a.auctionStatus, a.auctionEndTime, a.startPrice
  ORDER BY a.auctionId DESC
";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$rs = $stmt->get_result();
?>

<div class="container mt-4">
  <h3>My bids</h3>
  <ul class="list-group mt-3">
    <?php while ($row = $rs->fetch_assoc()): ?>
      <?php
        $auctionId = (int)$row['auctionId'];
        $itemId    = (int)$row['itemId'];
        $status    = (string)$row['auctionStatus'];
        $endTime   = new DateTime($row['auctionEndTime']);
        $topPrice  = (float)$row['topPrice'];
        $yourTop   = (float)$row['yourTopBid'];
        $now       = new DateTime();
        $badge     = '';

        if ($now > $endTime) {
          $badge = '<span class="badge badge-secondary">Ended</span>';
        } else {
          if ($yourTop >= $topPrice) {
            $badge = '<span class="badge badge-success">Leading</span>';
          } else {
            $badge = '<span class="badge badge-danger">Outbid</span>';
          }
        }
      ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <a href="listing.php?item_id=<?=$itemId?>">#<?=$auctionId?> — Item <?=$itemId?></a><br>
          <small>
            Your bid £<?=number_format($yourTop, 2)?> ·
            Top £<?=number_format($topPrice, 2)?> ·
            Status: <?=h($status)?> ·
            Ends: <?=$endTime->format('Y-m-d H:i')?>
          </small>
        </div>
        <div><?=$badge?></div>
      </li>
    <?php endwhile; ?>
  </ul>
</div>

<?php
$stmt->close();
include __DIR__ . '/footer.php';
