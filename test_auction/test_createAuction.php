<?php
require_once("../Auction_functions.php");   
require_once("../db_connect.php");

// -------- TEST createAuction() --------

$itemId = 2001; 
$startPrice = 10.00;
$reservePrice = 5.00;
$startTime = "2025-01-01 10:00:00";
$endTime   = "2025-01-01 12:00:00";

$newAuctionId = createAuction($itemId, $startPrice, $reservePrice, $startTime, $endTime);

echo "New Auction ID: " . $newAuctionId;
