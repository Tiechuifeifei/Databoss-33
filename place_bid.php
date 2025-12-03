<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'utilities.php';
require_once 'bid_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$buyerId = $_SESSION['userId'] ?? null;
if (!$buyerId) {
    header("Location: login.php?loginError=" . urlencode("Please log in to place a bid."));
    exit;
}


$auctionId = isset($_POST['auctionId']) ? (int)$_POST['auctionId'] : 0;
$itemId    = isset($_POST['itemId'])    ? (int)$_POST['itemId']    : 0;
$bidPrice  = $_POST['bidPrice'] ?? null;

$redirectItemId = $itemId ?: $auctionId;


if ($auctionId <= 0 || $bidPrice === null || $bidPrice === '' || !is_numeric($bidPrice)) {
    $msg = "Invalid bid input.";
    header("Location: listing.php?itemId={$redirectItemId}&auctionId={$auctionId}&error=" . urlencode($msg));
    exit;
}

$bidPrice = (float)$bidPrice;


$result = placeBid($buyerId, $auctionId, $bidPrice);

if ($result['success']) {
    header("Location: listing.php?itemId={$redirectItemId}&auctionId={$auctionId}&success=bid");
} else {
    header("Location: listing.php?itemId={$redirectItemId}&auctionId={$auctionId}&error=" . urlencode($result['message']));
}

exit;
