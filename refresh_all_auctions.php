<?php
// refresh_all_auctions.php
require_once "db_connect.php";
require_once "Auction_functions.php";

$sql = "SELECT auctionId FROM auctions";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        refreshAuctionStatus($row['auctionId']); 
    }
}

echo "All auctions refreshed at " . date("Y-m-d H:i:s") . "\n";
?>
