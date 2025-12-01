<?php
require_once("db_connect.php");
require_once("Auction_functions.php");
require_once("Item_function.php");


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auctionId = $_GET['auctionId'] ?? null;

if (!$auctionId) {
    die("Invalid auctionId");
}

$auction = getAuctionById($auctionId);
if (!$auction) {
    die("Auction not found");
}

// 权限验证
if (!isset($_SESSION['userId']) || $_SESSION['userId'] != $auction['sellerId']) {
    die("You are not allowed to re-list this item.");
}

$itemId = $auction['itemId'];

include('header.php')
?>



<div class="relist-container">

    <h2 class="relist-title">Re-list Item</h2>
    <p class="relist-subtitle">You can define new start time and end time for this auction.</p>

    <form method="POST" action="process_relist.php" class="relist-form">

        <input type="hidden" name="itemId" value="<?= $itemId ?>">
        <input type="hidden" name="oldAuctionId" value="<?= $auctionId ?>">

        <label class="relist-label">Starting Price</label>
        <input type="number" name="startPrice" class="relist-input" step="0.01" 
               value="<?= $auction['startPrice'] ?>" required>

        <label class="relist-label">Reserve Price</label>
        <input type="number" name="reservedPrice" class="relist-input" step="0.01" 
               value="<?= $auction['reservedPrice'] ?>" required>

        <label class="relist-label">New Start Time</label>
        <input type="datetime-local" name="startTime" class="relist-input" required>

        <label class="relist-label">New End Time</label>
        <input type="datetime-local" name="endTime" class="relist-input" required>

        <button type="submit" class="relist-btn">Create New Auction</button>
    </form>

</div>

<?php include("footer.php")?>
