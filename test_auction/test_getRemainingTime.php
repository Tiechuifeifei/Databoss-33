<?php
require_once("../Auction_functions.php");
require_once("../db_connect.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "<h2>Testing getRemainingTime()</h2>";

$auctionId = 1001;

$result = getRemainingTime($auctionId);

echo "<p>Remaining time for auction $auctionId: <strong>$result</strong></p>";
