<?php
// refresh_all_auctions.php
// Run this regularly (cron / Task Scheduler) to refresh auctions and trigger end processing/emails.

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/London');

require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/Auction_functions.php';

$debugFile = __DIR__ . '/auction_email_debug.txt';
file_put_contents($debugFile, "==== refresh_all_auctions started at " . date("Y-m-d H:i:s") . " ====\n", FILE_APPEND);

try {
    $sql = "SELECT auctionId FROM auctions";
    $result = $conn->query($sql);

    if (! $result) {
        $err = "DB error when selecting auctions: " . ($conn->error ?? 'unknown') . "\n";
        file_put_contents($debugFile, $err, FILE_APPEND);
        echo $err;
        exit(1);
    }

    $processed = 0;
    $endedProcessed = 0;

    while ($row = $result->fetch_assoc()) {
        $auctionId = (int)$row['auctionId'];

        // Log that we're checking this auction
        file_put_contents($debugFile, "Checking auction {$auctionId} at " . date("Y-m-d H:i:s") . "\n", FILE_APPEND);

        // 1) Refresh status (updates auctionStatus in DB if needed)
        $refreshResult = refreshAuctionStatus($auctionId);
        file_put_contents($debugFile, "  refreshAuctionStatus returned: " . var_export($refreshResult, true) . "\n", FILE_APPEND);

        // 2) Close/process the auction if refresh says it's ended (this calls endAuction())
        $closeMessage = closeAuctionIfEnded($auctionId);
        file_put_contents($debugFile, "  closeAuctionIfEnded: " . $closeMessage . "\n", FILE_APPEND);

        // Count if it was processed as ended
        if (stripos($closeMessage, 'ended') !== false) {
            $endedProcessed++;
        }

        $processed++;
    }

    $summary = "All auctions checked: {$processed}. Auctions processed as ended in this run: {$endedProcessed}.\n";
    file_put_contents($debugFile, $summary, FILE_APPEND);
    echo $summary;
    file_put_contents($debugFile, "==== refresh_all_auctions finished at " . date("Y-m-d H:i:s") . " ====\n\n", FILE_APPEND);

} catch (Throwable $e) {
    $err = "Exception: " . $e->getMessage() . "\n";
    file_put_contents($debugFile, $err, FILE_APPEND);
    echo $err;
    exit(1);
}
