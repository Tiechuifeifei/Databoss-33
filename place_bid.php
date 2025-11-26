<?php
// place_bid.php — updated for modular DB & camelCase
require_once __DIR__ . '/utilities.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

/**
 * Fail helper: sends HTTP response code and exits with message
 */
function fail(string $msg, int $code = 400) {
    http_response_code($code);
    exit($msg);
}

// 1) Read input (support both camelCase and snake_case)
$auctionId = isset($_POST['auctionId']) ? (int)$_POST['auctionId'] :
             (isset($_POST['auction_id']) ? (int)$_POST['auction_id'] : 0);

$bidAmount = isset($_POST['bidAmount']) ? (float)$_POST['bidAmount'] :
             (isset($_POST['bid_amount']) ? (float)$_POST['bid_amount'] : 0.0);

if ($auctionId <= 0 || $bidAmount <= 0) {
    fail('Invalid input.');
}

// 2) Must be logged in & get current user
if (empty($_SESSION['userId'])) {
    fail('Please log in to place a bid.');
}
$buyerId = (int)$_SESSION['userId'];

// 3) Get auction info
$db = get_db_connection();

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

if (!$auction) fail('Auction not found.', 404);
if ($auction['auctionStatus'] !== 'running') fail('Auction not running.');
if (new DateTime() > new DateTime($auction['auctionEndTime'])) fail('Auction already ended.');
if ((int)$buyerId === (int)$auction['sellerId']) fail('Seller cannot bid on own auction.');

// 4) Validate bid amount
$current = (float)$auction['currentPrice'];
if ($bidAmount <= $current) {
    fail('Your bid must be greater than £' . number_format($current, 2));
}

// 5) Insert bid
$stmt = $db->prepare("
    INSERT INTO bids (auctionId, buyerId, bidPrice, bidTime)
    VALUES (?, ?, ?, NOW())
");
$stmt->bind_param("iid", $auctionId, $buyerId, $bidAmount);
if (!$stmt->execute()) {
    $err = $db->error ?: 'Failed to insert bid.';
    $stmt->close();
    fail($err);
}
$stmt->close();

// Optional: update auctions table with latest winning bid
$stmt = $db->prepare("
    UPDATE auctions
    SET soldPrice = ?, winningBidId = LAST_INSERT_ID()
    WHERE auctionId = ?
");
$stmt->bind_param("di", $bidAmount, $auctionId);
$stmt->execute();
$stmt->close();

// 6) Success message
$itemId = (int)$auction['itemId'];
echo "OK: your bid = £" . number_format($bidAmount, 2) . " for auction #{$auctionId}. ";
echo '<a href="listing.php?item_id=' . $itemId . '">Back to listing</a>';