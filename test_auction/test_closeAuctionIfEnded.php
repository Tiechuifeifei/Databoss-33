<?php
require_once("../Auction_functions.php");
require_once("../db_connect.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "<h2>Testing closeAuctionIfEnded()</h2>";

// 修改你要测试的 auctionId
$auctionId = 1001;

// Step 1: run the function
$result = closeAuctionIfEnded($auctionId);

echo "<p>Result: <strong>$result</strong></p>";

// Step 2: 查询数据库的结果，验证是否更新
$sql = "SELECT auctionId, auctionStatus, soldPrice, winningBidId 
        FROM auctions 
        WHERE auctionId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $auctionId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

echo "<h3>Auction DB After Running Function:</h3>";
echo "<pre>";
print_r($data);
echo "</pre>";

