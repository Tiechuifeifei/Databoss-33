<?php
require_once("../Auction_functions.php");
require_once("../db_connect.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "<h2>Testing getCurrentHighestPrice()</h2>";

$auctionId = 1001;   
$price = getCurrentHighestPrice($auctionId);

echo "<p>Highest price for auction $auctionId = $price</p>";
