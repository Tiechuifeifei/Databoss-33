<?php
require_once("../Auction_functions.php");
require_once("../db_connect.php");

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// -------- TEST getActiveAuctions() --------

echo "<h2>Testing getActiveAuctions()</h2>";

$activeAuctions = getActiveAuctions();

echo "<pre>";
var_dump($activeAuctions);
echo "</pre>";

?>
