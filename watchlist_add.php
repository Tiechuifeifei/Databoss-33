<?php
require_once 'watchlist_funcs.php';
session_start();

$userId = $_SESSION['userId'] ?? null;
$auctionId = $_GET['auctionId'] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit;
}

addToWatchlist($userId, $auctionId);

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
