<?php
// *****************************************************************************************
// place_bid.php
// 只负责：接表单 → 调用 placeBid() → 把 placeBid() 里返回的 message 原样显示给用户
// *****************************************************************************************

require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/bids_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['userId'] ?? null;

// 先输出 header
include __DIR__ . '/header.php';

// ---------------------------
// 1) 必须先登录
// ---------------------------
if (!$userId) {
    ?>
    <div class="container mt-4">
        <div class="alert alert-warning">
            Please log in before placing a bid.（请先登录再出价）
        </div>
        <a href="browse.php" class="btn btn-secondary mt-3">Back to browse</a>
    </div>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

// ---------------------------
// 2) 必须是 POST，并且带 auctionId + bidPrice
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_POST['auctionId'])
    || !isset($_POST['bidPrice'])
) {
    ?>
    <div class="container mt-4">
        <div class="alert alert-danger">
            Invalid bid request.（非法的出价请求）
        </div>
        <a href="browse.php" class="btn btn-secondary mt-3">Back to browse</a>
    </div>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

// ---------------------------
// 3) 拿表单里的数据
// ---------------------------
$auctionId = (int)$_POST['auctionId'];
$rawBid    = trim((string)$_POST['bidPrice']);
$bidPrice  = (float)$rawBid;

// 当前登录用户就是 buyerId
$buyerId = (int)$userId;

// ---------------------------
// 4) 调用核心函数 placeBid()
//    所有业务规则、提示文案全部按照 bids_functions.php 里写的来
// ---------------------------
$result = placeBid($buyerId, $auctionId, $bidPrice);

// ---------------------------
// 5) 展示结果（只用 $result['message']）
// ---------------------------
?>
<div class="container mt-4">
    <?php
    // 理论上 placeBid() 一定会返回带 success 的数组，这里只是兜底
    if (!is_array($result) || !array_key_exists('success', $result)) : ?>
        <div class="alert alert-danger">
            Sorry, something went wrong. Please try again later.
        </div>
        <p>
            Attempted bid: £<?= number_format($bidPrice, 2) ?><br>
            Auction ID: <?= (int)$auctionId ?>
        </p>
    <?php elseif ($result['success']) : ?>
        <div class="alert alert-success">
            <?= h($result['message'] ?? 'Bid placed successfully.') ?>
        </div>
        <p>
            Your bid: £<?= number_format($bidPrice, 2) ?><br>
            Auction ID: <?= (int)$auctionId ?>
        </p>
    <?php else : ?>
        <div class="alert alert-warning">
            <?= h($result['message'] ?? 'Your bid was not accepted.') ?>
        </div>
        <p>
            Attempted bid: £<?= number_format($bidPrice, 2) ?><br>
            Auction ID: <?= (int)$auctionId ?>
        </p>
    <?php endif; ?>

    <a href="browse.php" class="btn btn-secondary mt-3">Back to browse</a>
</div>

<?php
include __DIR__ . '/footer.php';
