<link rel="stylesheet" href="css/custom_2.css">

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

//Connect DB
$db = get_db_connection();

//Fetch User Info
$sql = "SELECT * FROM users WHERE userId = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

//Fetch User's Listings
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

//Fetch User's Bids
$bids = getBidsByUser($userId);

//Fetch Watchlist
$watchlist = viewWatchlistByUser($userId);

?>

<?php include "header.php"; ?>

<div class="container mt-4 profile-page">

<h2 class="mb-4 profile-title">My Profile</h2>

<!--basic information-->
<div class="card mb-4 p-3 profile-card profile-section">
    <h4 class="profile-section-title">Basic Information</h4>
    <p class="porfile-info">Name: <?= h($user['userName']) ?></p>
    <p class="porfile-info">Email: <?= h($user['userEmail']) ?></p>
    <p class="porfile-info">Phone: <?= h($user['userPhoneNumber'] ?? '—') ?></p>
    <p class="porfile-info">Address: <?= h($user['userHouseNo']) . ", " . h($user['userStreet']) . ", " . h($user['userCity']) ?>
    </p>
    <p class="porfile-info">Postcode: <?= h($user['userPostcode']) ?></p>
    <p class="porfile-info">Date of Birth: <?= h($user['userDob']) ?></p>
    <p class="porfile-info">Joined: <?= h($user['createdAt']) ?></p>
</div>


<!--user listing-->
<h4 class="profile-section-title"> My Listings</h4>

<?php if (empty($listings)): ?>
    <p class="profile-message">No listings yet.</p>
<?php else: ?>
    <?php foreach ($listings as $a): ?>
        <div class="card p-3 mb-2 profile-card profile-mini-card">
            <h5 class="profile-item-title"><?= h($a['itemName']) ?></h5>
            <p class="porfile-info">Status: <?= h($a['auctionStatus']) ?></strong></p>
            <p class="porfile-info">Start Price: £<?= h($a['startPrice']) ?></p>
            <a href="listing.php?auctionId=<?= $a['auctionId'] ?>" 
               class="profile-btn">View Auction</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!--user bids-->
<h4 class="mt-5 profile-section-title"> My Bids</h4>

<?php if (empty($bids)): ?>
    <p class="profile-message">You haven't placed any bids.</p>
<?php else: ?>
    <?php foreach ($bids as $b): ?>
        <div class="profile-card p-3 mb-2">
            <h5 class="profile-item-title"><?= h($b['itemName']) ?></h5>
            <p class="porfile-info">Your Bid: £<?= h($b['bidPrice']) ?></p>
            <p class="porfile-info">Status: <?= h($b['auctionStatus']) ?></p>

            <?php if ($b['auctionStatus'] === 'ended'): ?>
                <?php if ($b['bidId'] == $b['winningBidId']): ?>
                    <p class="text-success profile-result"> You won this auction!</p>
                <?php else: ?>
                    <p class="text-danger profile-result"> You did not win.</p>
                <?php endif; ?>
            <?php endif; ?>

            <a class="profile-btn" href="listing.php?auctionId=<?= $b['auctionId'] ?>"
            style="margin= auto;">View Auction</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!--watchlist-->
<h4 class="mt-5 profile-section-title"> My Watchlist</h4>

<?php if (empty($watchlist)): ?>
    <p class="profile-message">No items in your watchlist.</p>
<?php else: ?>
    <?php foreach ($watchlist as $w): ?>
        <div class="card p-3 mb-2">
            <h5><?= h($w['itemName']) ?></h5>
            <p class="porfile-info">Status: <?= h($w['auctionStatus']) ?></p>

            <a class="profile-btn" 
               href="listing.php?auctionId=<?= $w['auctionId'] ?>">View</a>

            <a class="profile-btn-remove"
               href="watchlist_remove.php?auctionId=<?= $w['auctionId'] ?>">Remove</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>


</div>

<?php include "footer.php"; ?>
