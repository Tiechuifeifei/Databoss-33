<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../Item_function.php");
require_once("../Auction_functions.php");
require_once("../db_connect.php");

// ===== 1. 创建一个测试 item =====
$itemId = createItem(
    "Test Item for refresh",
    "Testing status transitions",
    1,      // sellerId
    1,      // categoryId
    "new"   // item condition
);

echo "<h3>Created itemId = $itemId</h3>";

// ===== 2. 创建一个测试 auction =====
// 把开始时间设为当前时间 - 5 秒（让它直接进入 running）
$startTime = date("Y-m-d H:i:s", time() - 5);
$endTime   = date("Y-m-d H:i:s", time() + 10);

$auctionId = createAuction(
    $itemId,
    10.0,     // start price
    5.0,      // reserve price
    $startTime,
    $endTime
);

echo "<h3>Created auctionId = $auctionId</h3>";

// ===== 3. 调用 refreshAuctionStatus =====
$ended = refreshAuctionStatus($auctionId);

echo "<p><strong>refreshAuctionStatus result = </strong>";
echo $ended ? "ENDED or CANCELLED" : "NOT ENDED";
echo "</p>";

// ===== 4. 打印当前 auction & item 状态 =====
$auction = getAuctionById($auctionId);
$item = getItemById($itemId);

echo "<h4>Auction Status: " . $auction["auctionStatus"] . "</h4>";
echo "<h4>Item Status: " . $item["itemStatus"] . "</h4>";
?>
