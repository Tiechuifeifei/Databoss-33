<?php
require_once("db_connect.php");
require_once("Auction_functions.php");
require_once("Item_function.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$itemId = intval($_POST['itemId']);
$startPrice = floatval($_POST['startPrice']);
$reservedPrice = floatval($_POST['reservedPrice']);
$startTime = $_POST['startTime'];
$endTime = $_POST['endTime'];

updateItemStatus($itemId, 'inactive');

$newAuctionId = createAuction(
    $itemId,
    $startPrice,
    $reservedPrice,
    $startTime,
    $endTime
);

if (!$newAuctionId) {
    die("Failed to create new auction.");
}

header("Location: listing.php?auctionId=" . $newAuctionId);
exit;
