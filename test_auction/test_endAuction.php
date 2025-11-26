<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../Item_function.php");
require_once("../Auction_functions.php");
require_once("../db_connect.php");

// 1. 创建 item 
$itemId = createItem(
    "EndAuction Test Item",
    "Testing endAuction function",
    1,
    1,
    "new"
);

echo "<h3>Created itemId = $itemId</h3>";

// 2. 创建一个已经结束的 auction 
$startTime = date("Y-m-d H:i:s", time() - 20);
$endTime   = date("Y-m-d H:i:s", time() - 10);

$auctionId = createAuction(
    $itemId,
    10.0,   
    5.0,         
    $startTime,  
    $endTime     
);

echo "<h3>Created auctionId = $auctionId</h3>";

//  3. 插入一条 bid（最高价） 
$sql = "INSERT INTO bids (auctionId, buyerId, bidPrice, bidTime)
        VALUES (?, 2, ?, NOW())";

$stmt = $conn->prepare($sql);
$testBidPrice = 30.0;
$stmt->bind_param("id", $auctionId, $testBidPrice);
$stmt->execute();

echo "<p>Inserted bid with price = $testBidPrice</p>";

// 4. 调用 endAuction() 
endAuction($auctionId);

echo "<p><strong>endAuction() executed.</strong></p>";

// 5. 打印 auction 和 item 状态
$auction = getAuctionById($auctionId);
$item    = getItemById($itemId);

echo "<h4>Auction Status: " . $auction["auctionStatus"] . "</h4>";
echo "<h4>Winning Bid Id: " . $auction["winningBidId"] . "</h4>";
echo "<h4>Sold Price: " . $auction["soldPrice"] . "</h4>";
echo "<h4>Item Status: " . $item["itemStatus"] . "</h4>";
?>
