<?php
require_once 'utilities.php';
require_once 'auction_functions.php';
require_once 'bid_functions.php';
require_once 'watchlist_funcs.php';

// Check login
$userId = $_SESSION['userId'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}

// Connect DB
$db = get_db_connection();

// 1. Fetch User Info
$sql = "SELECT * FROM users WHERE userId = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Fetch User's Listings
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
$stmt->bind_param("i", $userId);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 3. Fetch User's Bids
$bids = getBidsByUser($userId);

// 4. Fetch Watchlist
$watchlist = viewWatchlistByUser($userId);

?>

<?php include "header.php"; ?>

<div class="container mt-4">

<h2 class="mb-4">👤 My Profile</h2>

<!-- basic information -->
<div class="card mb-4 p-3">
    <h4>Basic Information</h4>
    <p><strong>Name:</strong> <?= h($user['userName']) ?></p>
    <p><strong>Email:</strong> <?= h($user['userEmail']) ?></p>
    <p><strong>Phone:</strong> <?= h($user['userPhoneNumber'] ?? '—') ?></p>
    <p><strong>Address:</strong>
        <?= h($user['userHouseNo']) . ", " . h($user['userStreet']) . ", " . h($user['userCity']) ?>
    </p>
    <p><strong>Postcode:</strong> <?= h($user['userPostcode']) ?></p>
    <p><strong>Date of Birth:</strong> <?= h($user['userDob']) ?></p>
    <p><strong>Joined:</strong> <?= h($user['createdAt']) ?></p>
</div>


<!-- user listing -->
<h4 class="mt-5"> My Listings</h4>

<?php if (empty($listings)): ?>
    <p>No listings yet.</p>
<?php else: ?>
    <?php foreach ($listings as $a): ?>
        <div class="card p-3 mb-2">
            <h5><?= h($a['itemName']) ?></h5>
            <p>Status: <strong><?= h($a['auctionStatus']) ?></strong></p>
            <p>Start Price: £<?= h($a['startPrice']) ?></p>
            <a href="listing.php?auctionId=<?= $a['auctionId'] ?>" 
               class="btn btn-primary btn-sm">View Auction</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- user bids -->
<h4 class="mt-5"> My Bids</h4>

<?php if (empty($bids)): ?>
    <p>You haven’t placed any bids.</p>
<?php else: ?>
    <?php foreach ($bids as $b): ?>
        <div class="card p-3 mb-2">
            <h5><?= h($b['itemName']) ?></h5>
            <p>Your Bid: £<?= h($b['bidPrice']) ?></p>
            <p>Status: <strong><?= h($b['auctionStatus']) ?></strong></p>

            <?php if ($b['auctionStatus'] === 'ended'): ?>
                <?php if ($b['bidId'] == $b['winningBidId']): ?>
                    <p class="text-success"> You won this auction!</p>
                <?php else: ?>
                    <p class="text-danger"> You did not win.</p>
                <?php endif; ?>
            <?php endif; ?>

            <a class="btn btn-primary btn-sm" href="listing.php?auctionId=<?= $b['auctionId'] ?>">View Auction</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- watchlist -->
<h4 class="mt-5"> My Watchlist</h4>

<?php if (empty($watchlist)): ?>
    <p>No items in your watchlist.</p>
<?php else: ?>
    <?php foreach ($watchlist as $w): ?>
        <div class="card p-3 mb-2">
            <h5><?= h($w['itemName']) ?></h5>
            <p>Status: <?= h($w['auctionStatus']) ?></p>

            <a class="btn btn-primary btn-sm" 
               href="listing.php?auctionId=<?= $w['auctionId'] ?>">View</a>

            <a class="btn btn-danger btn-sm" 
               href="watchlist_remove.php?auctionId=<?= $w['auctionId'] ?>">Remove</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>


</div>

<?php include "footer.php"; ?>
