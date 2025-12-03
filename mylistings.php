  <link rel="stylesheet" href="css/custom_2.css">

<?php
require_once __DIR__ . '/utilities.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$userId = $_SESSION['userId'] ?? null;
include __DIR__ . '/header.php';

if (!$userId) {
    echo '<div class="container mt-4"><div class="alert alert-warning">Please log in to view your listings.</div></div>';
    include __DIR__ . '/footer.php';
    exit;
}

$db = get_db_connection();

// Join auctions with items to get item names
$sql = "
    SELECT 
        a.auctionId,
        a.itemId,
        i.itemName,
        a.auctionStatus,
        a.auctionEndTime,
        a.startPrice,
        COALESCE(
            (SELECT MAX(b.bidPrice) FROM bids b WHERE b.auctionId = a.auctionId),
            a.startPrice
        ) AS topPrice
    FROM auctions a
    JOIN items i ON i.itemId = a.itemId
    WHERE i.sellerId = ?
      AND a.auctionStatus <> 'relisted'
    ORDER BY a.auctionId DESC
";

$stmt = $db->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$rs = $stmt->get_result();
?>


<div class="container mt-4 mylistings-page">
    <h3 class="mylistings-title">My listings</h3>

    <?php if ($rs->num_rows === 0): ?>
        <div class="alert alert-info mt-3 mylistings-empty">You haven't created any auctions yet.</div>
    <?php else: ?>
        </div>

        <ul class="list-group mt-3 mylistings-list">
            <?php while ($row = $rs->fetch_assoc()): ?>
                <?php
                    $auctionId = (int)$row['auctionId'];
                    $itemId= (int)$row['itemId'];
                    $itemName= h($row['itemName']);
                    $status= (string)$row['auctionStatus'];
                    $endTime= new DateTime($row['auctionEndTime']);
                    $start = (float)$row['startPrice'];
                    $topPrice = (float)$row['topPrice'];
                ?>

                <li class="list-group-item d-flex justify-content-between align-items-center mylistings-item">
                    <div class="mylistings-item-main">
                        <a href="listing.php?auctionId=<?=$auctionId?>"class="mylistings-item-link">
                            <?=$itemName?></a><br>
                        <small class="mylistings-item-meta">
                            Start £<?=number_format($start, 2)?> ·
                            Current £<?=number_format($topPrice, 2)?> ·
                            <span class="mylistings-status mylistings-status-<?=h($status)?>">
                                Status: <?=h($status)?>
                            </span>
                            Ends: <?=$endTime->format('Y-m-d H:i')?>
                        </small>
                    </div>

                    <div class="mylistings-item-actions">
                        <a class="btn btn-sm btn-outline-secondary mylistings-open-btn" 
                        href="listing.php?auctionId=<?=$auctionId?>">Open</a>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
</div>

<?php
$stmt->close();

include __DIR__ . '/footer.php';
