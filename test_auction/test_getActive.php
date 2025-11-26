<?php
require_once("../db_connect.php");
require_once("../Auction_functions.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "<h2>Testing getActiveAuctions()</h2>";

$results = getActiveAuctions();

if (empty($results)) {
    echo "❗ No active auctions found OR query not working.\n";
} else {
    echo "✔ getActiveAuctions() returned " . count($results) . " rows.\n\n";
}

echo "<pre>";
print_r($results);
