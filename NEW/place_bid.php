<?php

// place_bid.php
// 只负责：接表单--调用placeBid--然后把placeBid里return的message原样显示给用户

require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/bids_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userId = $_SESSION['userId'] ?? null;

// 先输出header
include __DIR__ . '/header.php';

//let user must log in first
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

//必须是POST，并且带auctionId+bidPrice
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

$auctionId = (int)$_POST['auctionId'];
$bidPrice  = (float)$_POST['bidPrice'];  //float is enough for using
// now the login user is buyerId
$buyerId = (int)$userId;

//call the core fucntion "placeBid" in bids_function.php, all the reminder/message/validatons and rules are according to the core function.
$result = placeBid($buyerId, $auctionId, $bidPrice);

//show result, only use $result's message
?>
<div class="container mt-4">
<?php if (!empty($result['success'])): ?>
    <div class="alert alert-success">
        <?= h($result['message'] ?? 'Bid placed successfully.') ?>
    </div>
    <p>
        Your bid: £<?= number_format($bidPrice, 2) ?><br>
        Auction ID: <?= (int)$auctionId ?>
    </p>
<?php else: ?>
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

