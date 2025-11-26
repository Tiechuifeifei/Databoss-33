<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'utilities.php';
require_once 'bid_functions.php';
session_start();

// YH comment: I changed a lot of below codes. Since we have the function file, we can remove some old version codes.
// user check 
$buyerId = $_SESSION['userId'] ?? null;
if (!$buyerId) {
    header("Location: login.php?loginError=" . urlencode("Please log in to place a bid."));
    exit;
}

// auctionId and bidPrice
$auctionId = $_POST['auctionId'] ?? null;
$bidPrice  = $_POST['bidPrice'] ?? null;

// check the existance of user and auction
if (!$auctionId || !$bidPrice) {
    header("Location: listing.php?auctionId=$auctionId&error=" . urlencode("Invalid bid input."));
    exit;
}

// call placeBid() 
// YH DEBUG: $result
$result = placeBid($buyerId, $auctionId, $bidPrice);

// success / error redirect to listing.php
if ($result['success']) {
    header("Location: listing.php?auctionId=$auctionId&success=" . urlencode($result['message']));
    exit;
} else {
    header("Location: listing.php?auctionId=$auctionId&error=" . urlencode($result['message']));
    exit;
}
?>
