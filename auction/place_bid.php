<?php
// place_bid.php  —  drop-in replacement (camelCase 版)
require_once __DIR__ . '/utilities.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function fail($msg, $code = 400) {
  http_response_code($code);
  exit($msg);
}

$db = get_db_connection();

// 1) 读取输入（兼容两种命名）
$auctionId  = isset($_POST['auctionId'])  ? (int)$_POST['auctionId']
            : (isset($_POST['auction_id']) ? (int)$_POST['auction_id'] : 0);

$bidAmount  = isset($_POST['bidAmount'])  ? (float)$_POST['bidAmount']
            : (isset($_POST['bid_amount']) ? (float)$_POST['bid_amount'] : 0.0);

if ($auctionId <= 0 || $bidAmount <= 0) {
  fail('Invalid input.');
}

// 2) 必须登录 & 取当前用户
if (empty($_SESSION['user_id'])) {
  fail('Please log in to place a bid.');
}
$buyerId = (int)$_SESSION['user_id'];

// 3) 读取拍卖（字段使用小驼峰命名）
$stmt = $db->prepare("
  SELECT 
    a.auctionId,
    a.itemId,
    a.auctionStatus,
    a.auctionEndTime,
    a.startPrice,
    a.sellerId,
    COALESCE(
      (SELECT MAX(b.bidPrice) FROM bids b WHERE b.auctionId = a.auctionId),
      a.startPrice
    ) AS currentPrice
  FROM auctions a
  WHERE a.auctionId = ?
  LIMIT 1
");
$stmt->bind_param("i", $auctionId);
$stmt->execute();
$auction = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$auction)                 fail('Auction not found.', 404);
if ($auction['auctionStatus'] !== 'running') fail('Auction not running.');
if (new DateTime() > new DateTime($auction['auctionEndTime'])) {
  fail('Auction already ended.');
}
if ((int)$buyerId === (int)$auction['sellerId']) {
  fail('Seller cannot bid on own auction.');
}

// 4) 出价校验
$current = (float)$auction['currentPrice'];
if ($bidAmount <= $current) {
  fail('Your bid must be greater than £' . number_format($current, 2));
}

// 5) 写入出价（列名小驼峰）
$stmt = $db->prepare("
  INSERT INTO bids (auctionId, buyerId, bidPrice, bidTime)
  VALUES (?, ?, ?, NOW())
");
$stmt->bind_param("iid", $auctionId, $buyerId, $bidAmount);
if (!$stmt->execute()) {
  $err = $db->error ?: 'Insert failed.';
  $stmt->close();
  fail($err);
}
$stmt->close();

// 6) 成功反馈并返回到 listing
$itemId = (int)$auction['itemId'];
echo "OK: your bid = £" . number_format($bidAmount, 2) . " for auction #{$auctionId}. ";
echo '<a href="listing.php?item_id=' . $itemId . '">Back to listing</a>';
