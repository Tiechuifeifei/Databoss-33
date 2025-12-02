<?php
require_once 'utilities.php';
require_once 'auction_functions.php';
require_once 'bid_functions.php';
require_once 'watchlist_funcs.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// viewer (logged-in user) if any
$userId = $_SESSION['userId'] ?? null;

// seller to view: from GET ?sellerId=..., otherwise show current user if logged in
$sellerId = isset($_GET['sellerId']) ? (int)$_GET['sellerId'] : $userId;

if (!$sellerId) {
    header("Location: browse.php?error=" . urlencode("Seller not specified."));
    exit;
}

//Connect DB
$db = get_db_connection();

//Fetch Seller Info (use $sellerId)
$sql = "SELECT * FROM users WHERE userId = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: browse.php?error=" . urlencode("Seller not found."));
    exit;
}

/**
 * Seller rating summary (for this seller)
 */
$sql = "
    SELECT 
        AVG(rating) AS avgRating,
        COUNT(*) AS ratingCount
    FROM sellerRatings
    WHERE sellerId = ?
";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$sellerRating = $stmt->get_result()->fetch_assoc();
$stmt->close();

$avgRating   = $sellerRating['avgRating']   ?? null;
$ratingCount = $sellerRating['ratingCount'] ?? 0;

//Fetch Seller's Listings
$sqlListings = "
    SELECT 
        a.auctionId,
        a.auctionStatus,
        a.startPrice,
        a.auctionEndTime,
        i.itemName
    FROM auctions a
    JOIN items i ON a.itemId = i.itemId
    WHERE i.sellerId = ?
    ORDER BY a.auctionId DESC
";
$stmt = $db->prepare($sqlListings);
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch viewer's bids and watchlist as before (viewer may be null)
$bids = $userId ? getBidsByUser($userId) : [];
$watchlist = $userId ? viewWatchlistByUser($userId) : [];

?>

<?php include "header.php"; ?>

<div class="container mt-4 profile-page">

<h2 class="mb-4 profile-title"> Profile</h2>

<!--basic information-->
<div class="card mb-4 p-3 profile-card profile-section">
    <h4 class="profile-section-title"><?= h($user['userName']) ?></h4>
    <p class="porfile-info">Email: <?= h($user['userEmail']) ?></p>

    <p><strong>Seller Rating:</strong>
        <?php if ($ratingCount > 0): ?>
            <?= number_format($avgRating, 1) ?> / 5 
            (<?= $ratingCount ?> rating<?= $ratingCount > 1 ? 's' : '' ?>)
        <?php else: ?>
            No ratings yet.
        <?php endif; ?>
    </p>
</div>

<!--user listing-->
<h4 class="profile-section-title"> Listings</h4>

<?php if (empty($listings)): ?>
    <p class="profile-message">No listings yet.</p>
<?php else: ?>
    <?php foreach ($listings as $a): ?>
        <div class="card p-3 mb-2 profile-card profile-mini-card">
            <h5 class="profile-item-title"><?= h($a['itemName']) ?></h5>
            <p class="porfile-info">Status: <?= h($a['auctionStatus']) ?></p>
            <p class="porfile-info">Start Price: £<?= h($a['startPrice']) ?></p>
            <a href="listing.php?auctionId=<?= $a['auctionId'] ?>" class="profile-btn">View Auction</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Optionally show viewer's bids (if logged in) -->
<?php if ($userId): ?>
    <h4 class="mt-5 profile-section-title"> Viewer: Your Bids</h4>
    <?php if (empty($bids)): ?>
        <p class="profile-message">You haven't placed any bids.</p>
    <?php else: ?>
        <?php foreach ($bids as $b): ?>
            <div class="card p-3 mb-2">
                <h5><?= h($b['itemName']) ?></h5>
                <p>Seller: <?= h($b['sellerName']) ?></p>
                <p>Your Bid: £<?= h(number_format($b['bidPrice'], 2)) ?></p>
                <p>Status: <strong><?= h($b['auctionStatus']) ?></strong></p>
                <a class="profile-btn" href="listing.php?auctionId=<?= $b['auctionId'] ?>">View Auction</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

</div>

<?php include "footer.php"; ?>