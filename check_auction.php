<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db_connect.php");
require_once("Auction_functions.php");

// this one is used to refresh and update every 5 mins -- based on lab lecture 7 
// Get all auctionIds
$sql = "SELECT auctionId FROM auctions";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $auctionId = $row['auctionId'];

    // Run your refresh function
    $isEnded = refreshAuctionStatus($auctionId);

    // If ended, do finalization (you will add endAuction later)
    if ($isEnded) {
        endAuction($auctionId);
    }
}

echo "Auction status refreshed at " . date("Y-m-d H:i:s");
