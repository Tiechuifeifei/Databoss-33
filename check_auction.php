<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db_connect.php");
require_once("Auction_functions.php");

// Refresh auctions every 5 minutes
$sql = "SELECT auctionId FROM auctions";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $auctionId = (int)$row['auctionId'];

    // This will refresh status and end auction + send emails if needed
    $message = closeAuctionIfEnded($auctionId);

    echo "Auction {$auctionId}: {$message}\n";
}

echo "Auction status check completed at " . date("Y-m-d H:i:s");
