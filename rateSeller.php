<?php
require_once 'utilities.php';
session_start();

//If not logged in, redirect to login
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['userId'];
$auctionId = isset($_POST['auctionId']) ? (int)$_POST['auctionId'] : 0;
$rating= isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment= isset($_POST['comment']) ? trim($_POST['comment']) : '';

//Basic validation
if ($auctionId <= 0 || $rating < 1 || $rating > 5) {
    header("Location: profile.php?rated_error=1");
    exit;
}

$db = get_db_connection();

//1.Get sellerId for this auction
$sqlSeller = "
    SELECT i.sellerId
    FROM auctions a
    JOIN items i ON a.itemId = i.itemId
    WHERE a.auctionId = ?
    LIMIT 1
";

$stmt=$db->prepare($sqlSeller);
if (!$stmt) {

    header("Location: profile.php?rated_error=2");
    exit;
}

$stmt->bind_param("i", $auctionId);
$stmt->execute();
$res=$stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    header("Location: profile.php?rated_error=3");
    exit;
}

$sellerId = (int)$res['sellerId'];

//2.Insert rating (with comment)
$sqlInsert = "
    INSERT INTO sellerRatings (sellerId, raterId, rating, auctionId, comment)
    VALUES (?, ?, ?, ?, ?)
";

$stmt = $db->prepare($sqlInsert);
if (!$stmt) {
    header("Location: profile.php?rated_error=4");
    exit;
}

$stmt->bind_param("iiiis", $sellerId, $userId, $rating, $auctionId, $comment);

if (!$stmt->execute()) {

    $stmt->close();
    header("Location: profile.php?rated_error=5");
    exit;
}

$stmt->close();

//Done, back to profile with success message
header("Location: profile.php?rated_success=1");
exit;
