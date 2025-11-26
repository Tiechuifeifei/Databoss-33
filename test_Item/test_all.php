<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../db_connect.php");
require_once("../item_function.php");
require_once("../image_functions.php");
require_once("../auction_functions.php");

echo "<pre>";

$userId = 1; // 

echo "Using existing userId = $userId\n\n";


echo "=== Creating Item ===\n";

$itemId = createItem(
    "Test Item From User",
    "This is a test item created by an existing user.",
    $userId,    // sellerId
    2,          // categoryId
    "New" // itemCondition
);

echo "New itemId = $itemId\n";

echo "Item details:\n";
print_r(getItemById($itemId));
echo "\n\n";


echo "=== Uploading images ===\n";

uploadImage($itemId, "images/sample1.jpg", 1); // 主图
uploadImage($itemId, "images/sample2.jpg", 0);
uploadImage($itemId, "images/sample3.jpg", 0);

print_r(getImagesByItemId($itemId));
echo "\n\n";

echo "Primary image:\n";
print_r(getPrimaryImage($itemId));
echo "\n\n";


echo "=== Creating Auction ===\n";

$startTime = date("Y-m-d H:i:s", strtotime("-5 minutes")); 
$endTime   = date("Y-m-d H:i:s", strtotime("+1 hour"));

$auctionId = createAuction(
    $itemId,
    20.00,      // start price
    40.00,      // reserve price
    $startTime,
    $endTime
);

echo "New auctionId = $auctionId\n\n";


echo "=== Auction Details ===\n";
print_r(getAuctionById($auctionId));
echo "\n\n";

echo "=== Refreshing Auction Status ===\n";
refreshAuctionStatus($auctionId);
print_r(getAuctionById($auctionId));
echo "\n\n";


echo "=== Highest Price ===\n";
echo getCurrentHighestPrice($auctionId);
echo "\n\n";


echo "=== Auctions created by this user ===\n";
print_r(getAuctionsByUser($userId));
echo "\n\n";

echo "</pre>";
?>
