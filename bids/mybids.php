<?php
// mybids.php — Buyer's view of all bids (最终版：调整列顺序 + 显示结束时间)

require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/bids_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['userId'] ?? null;

include __DIR__ . '/header.php';

// 1) 用户必须先登录
if (!$userId) {
    echo '<div class="container mt-4">
            <div class="alert alert-warning">Please log in to view your bids.</div>
          </div>';
    include __DIR__ . '/footer.php';
    exit;
}

// 2) 加载当前 buyer 的所有出价记录
$bids = getBidsByUser($userId);
?>

<div class="container mt-4">
    <h2>My Bids</h2>

    <?php if (empty($bids)): ?>
        <p>You have not placed any bids yet.</p>
    <?php else: ?>

        <table class="table table-bordered table-striped mt-3">
            <thead>
            <tr>
                <th>Item</th>
                <th>Auction ID</th>
                <th>Your bid</th>
                <th>Highest bidder?</th>
                <th>Current highest bid</th>
                <th>Auction status</th>
                <th>Bid time</th>
                <th>Auction end time</th>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($bids as $b): ?>

                <?php
                $auctionId   = (int)$b['auctionId'];
                $yourBid     = (float)$b['bidPrice'];
                $bidTime     = $b['bidTime'];
                $status      = $b['auctionStatus'] ?? '';
                $endTime     = $b['auctionEndTime'] ?? '';
                $startPrice  = isset($b['startPrice']) ? (float)$b['startPrice'] : 0.0;

                // ---------- 当前最高出价 ----------
                $highestRow = getHighestBidForAuction($auctionId);
                if ($highestRow) {
                    $currentHighest = (float)$highestRow['bidPrice'];
                    $isHighest      = ((int)$highestRow['buyerId'] === (int)$userId);
                } else {
                    // 没有任何出价时，用 startPrice 作为当前价格
                    $currentHighest = $startPrice;
                    $isHighest      = false;
                }
                ?>

                <tr>
                    <td>
                        <a href="listing.php?auctionId=<?= $auctionId ?>">
                            <?= h($b['itemName']) ?>
                        </a>
                    </td>

                    <td><?= $auctionId ?></td>

                    <td>£<?= number_format($yourBid, 2) ?></td>

                    <td><strong><?= $isHighest ? 'Yes' : 'No' ?></strong></td>

                    <td>£<?= number_format($currentHighest, 2) ?></td>

                    <td><?= h($status) ?></td>

                    <td><?= h($bidTime) ?></td>

                    <td><?= $endTime ? h($endTime) : 'N/A' ?></td>
                </tr>

            <?php endforeach; ?>

            </tbody>
        </table>

    <?php endif; ?>

</div>

<?php include __DIR__ . '/footer.php'; ?>
