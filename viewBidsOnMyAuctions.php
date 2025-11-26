<?php
// ======================================================================================
// viewBidsOnMyAuctions.php
// A standalone page for seller to view all bids on their auctions
// (This page belongs to the BIDS MODULE - Leo)
// ======================================================================================

require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/bids_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['userId'] ?? null;

// Header
include __DIR__ . '/header.php';

// Must be logged in
if (!$userId) {
    echo '<div class="container mt-4">
            <div class="alert alert-warning">
                Please log in to view bids on your auctions.
            </div>
          </div>';
    include __DIR__ . '/footer.php';
    exit;
}

// ---------------------------------------------------------------------------
// 1. Load ALL bids on ALL auctions created by THIS seller
// ---------------------------------------------------------------------------
$rows = viewBidsOnMyAuctions((int)$userId);

// ---------------------------------------------------------------------------
// 2. Group by auctionId
// ---------------------------------------------------------------------------
$auctions = [];

foreach ($rows as $line) {
    $aid = (int)$line['auctionId'];

    if (!isset($auctions[$aid])) {
        $auctions[$aid] = [
            'auctionId'        => $aid,
            'itemId'           => (int)$line['itemId'],
            'itemName'         => $line['itemName'],
            'auctionStatus'    => $line['auctionStatus'],
            'auctionStartTime' => $line['auctionStartTime'],
            'auctionEndTime'   => $line['auctionEndTime'],
            'startPrice'       => (float)$line['startPrice'],
            'bids'             => [],
        ];
    }

    // if no bids exist for this auction, bidId will be null → skip
    if (!empty($line['bidId'])) {
        $auctions[$aid]['bids'][] = [
            'bidId'     => (int)$line['bidId'],
            'buyerId'   => (int)$line['buyerId'],
            'buyerName' => $line['userName'],     // From users table
            'bidPrice'  => (float)$line['bidPrice'],
            'bidTime'   => $line['bidTime'],
        ];
    }
}

?>

<div class="container mt-4">
    <h3>Bids on My Auctions</h3>

    <?php if (empty($auctions)): ?>
        <div class="alert alert-info mt-3">
            You haven't created any auctions yet.
        </div>
    <?php else: ?>
        <ul class="list-group mt-3">

            <?php foreach ($auctions as $auction): ?>
                <?php
                    $auctionId  = $auction['auctionId'];        // 这是 auctionId，不是 itemId
                    $itemId     = $auction['itemId'];
                    $itemName   = h($auction['itemName']);
                    $status     = h($auction['auctionStatus']);
                    $startPrice = $auction['startPrice'];

                    $startTime  = $auction['auctionStartTime']
                                  ? new DateTime($auction['auctionStartTime'])
                                  : null;

                    $endTime    = $auction['auctionEndTime']
                                  ? new DateTime($auction['auctionEndTime'])
                                  : null;

                    // Calculate current price (highest bid or start price)
                    $currentPrice = $startPrice;
                    if (!empty($auction['bids'])) {
                        foreach ($auction['bids'] as $b) {
                            if ($b['bidPrice'] > $currentPrice) {
                                $currentPrice = $b['bidPrice'];
                            }
                        }
                    }

                    // Count how many bids in total for this auction
                    $bidCount = !empty($auction['bids']) ? count($auction['bids']) : 0;
                ?>

                <li class="list-group-item">

                    <!-- Auction Summary -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>#<?= $auctionId ?> — <?= $itemName ?></strong><br>

                            <!-- 第一行：价格 + 总出价数，中间用 | 和空格拉开距离 -->
                            <small class="text-muted">
                                <strong>Start price:</strong>
                                £<?= number_format($startPrice, 2) ?>
                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                <strong>Current highest bid:</strong>
                                £<?= number_format($currentPrice, 2) ?>
                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                <strong>Total bids:</strong>
                                <?= $bidCount ?>
                            </small>
                            <br>
                            <!-- 第二行：状态 + 结束时间 -->
                            <small class="text-muted">
                                <strong>Status:</strong>
                                <?= $status ?>
                                <?php if ($endTime): ?>
                                    &nbsp;&nbsp;|&nbsp;&nbsp;
                                    <strong>Ends:</strong>
                                    <?= $endTime->format('Y-m-d H:i') ?>
                                <?php endif; ?>
                            </small>
                        </div>

                        <div>
                            <!-- Link to open listing -->
                            <a class="btn btn-sm btn-outline-primary"
                               href="listing.php?auctionId=<?= $auctionId ?>">
                                Open Auction
                            </a>
                        </div>
                    </div>

                    <!-- All Bids for This Auction -->
                    <div class="mt-3">
                        <?php if (empty($auction['bids'])): ?>
                            <small class="text-muted">No bids yet for this auction.</small>
                        <?php else: ?>
                            <table class="table table-sm table-striped mt-2 mb-0">
                                <thead>
                                    <tr>
                                        <th>Buyer</th>
                                        <th>Bid Price</th>
                                        <th>Bid Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($auction['bids'] as $b): ?>
                                        <tr>
                                            <td><?= h($b['buyerName'] ?? ('User #' . $b['buyerId'])) ?></td>
                                            <td>£<?= number_format($b['bidPrice'], 2) ?></td>
                                            <td><?= h($b['bidTime']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                </li>

            <?php endforeach; ?>

        </ul>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
