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


if (!isset($_SESSION['userId']) || $_SESSION['userId'] != $auction['sellerId']) {
    die("You are not allowed to re-list this item.");
}

$itemId = $auction['itemId'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Re-list Item</title>
</head>
<body>

<h2>Re-list Item</h2>

<p>You can define new start time and end time for this auction.</p>

<form method="POST" action="process_relist.php">

    <input type="hidden" name="itemId" value="<?= $itemId ?>">
    <input type="hidden" name="oldAuctionId" value="<?= $auctionId ?>">

    <label>Starting Price:</label><br>
    <input type="number" name="startPrice" step="0.01" 
        value="<?= $auction['startPrice'] ?>" required><br><br>

    <label>Reserve Price:</label><br>
    <input type="number" name="reservedPrice" step="0.01" 
        value="<?= $auction['reservedPrice'] ?>" required><br><br>

    <label>New Start Time:</label><br>
    <input type="datetime-local" name="startTime" required><br><br>

    <label>New End Time:</label><br>
    <input type="datetime-local" name="endTime" required><br><br>

    <button type="submit">Create New Auction</button>
</form>

</body>
</html>
