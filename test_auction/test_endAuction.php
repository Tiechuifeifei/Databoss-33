<?php
require_once("../Auction_functions.php");
require_once("../db_connect.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "<h2>Testing endAuction()</h2>";

$auctionId = 1001;

endAuction($auctionId);

// 显示更新后的结果
$sql = "SELECT auctionId, soldPrice, winningBidId FROM auctions WHERE auctionId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $auctionId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo "<pre>";
print_r($result);
echo "</pre>";
