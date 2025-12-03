<?php
require_once 'utilities.php';
require_once 'auction_functions.php';
require_once 'bid_functions.php';
require_once 'watchlist_funcs.php';


$userId = $_SESSION['userId'] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}


$db = get_db_connection();


$sql = "SELECT * FROM users WHERE userId = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

/**
 * 1b. Fetch seller rating summary (if this user is a seller)
 */
$sql = "
    SELECT 
        AVG(rating) AS avgRating,
        COUNT(*) AS ratingCount
    FROM sellerRatings
    WHERE sellerId = ?
";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$sellerRating = $stmt->get_result()->fetch_assoc();
$stmt->close();

$avgRating   = $sellerRating['avgRating']   ?? null;
$ratingCount = $sellerRating['ratingCount'] ?? 0;


// 1b. Fetch seller rating (average + count)
$sqlRating = "
    SELECT 
        AVG(rating) AS avg_rating,
        COUNT(*)    AS rating_count
    FROM sellerRatings
    WHERE sellerId = ?
";
$stmt = $db->prepare($sqlRating);
$stmt->bind_param("i", $userId);
$stmt->execute();
$ratingRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

$avgRating   = $ratingRow['avg_rating'] ?? null;
$ratingCount = (int)($ratingRow['rating_count'] ?? 0);


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

        <p><strong>Seller Rating:</strong>
        <?php if ($ratingCount > 0): ?>
            <?= number_format($avgRating, 1) ?> / 5 
            (<?= $ratingCount ?> rating<?= $ratingCount > 1 ? 's' : '' ?>)
        <?php else: ?>
            No ratings yet.
        <?php endif; ?>
    </p>

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

        <?php
    
        $auctionId  = (int)$b['auctionId'];
        $itemId     = (int)$b['itemId'];
        $itemName   = $b['itemName'];
        $yourBid    = (float)$b['bidPrice'];
        $bidTime    = $b['bidTime'];
        $status     = $b['auctionStatus'];
        $endTime    = $b['auctionEndTime'];
        $startPrice = (float)$b['startPrice'];

        $highestRow = getHighestBidForAuction($auctionId);

        if ($highestRow) {
            $currentHighest = (float)$highestRow['bidPrice'];

            if (array_key_exists('buyerId', $highestRow)) {
                $isHighest = ((int)$highestRow['buyerId'] === (int)$userId);
            } else {
                // fallback: compare your bid vs current highest
                $isHighest = ($yourBid >= $currentHighest);
            }
        } else {
            // no bids -> highest is start price, and user is not highest
            $currentHighest = $startPrice;
            $isHighest = false;
        }

        // default winner text
        $winnerText = $isHighest ? '<strong>Yes</strong>' : 'No';

        // if auction ended, change to result reminder
        if ($status === 'ended') {
            if ($highestRow) {
                if ($isHighest) {
                    $winnerText = '<span class="text-success"><strong>Congratulations! You won this auction.</strong></span>';
                } else {
                    $winnerText = '<span class="text-muted">Unfortunately, another buyer won this auction.</span>';
                }
            } else {
                $winnerText = '<span class="text-muted">Auction ended with no bids.</span>';
            }
        }
        ?>

            <div class="card p-3 mb-2">
                <h5><?= h($itemName) ?></h5>
                <p>Seller: <a href="seller_profile.php?sellerId=<?= (int)$b['sellerId'] ?>"><?= h($b['sellerName']) ?></a></p>
                <p>Your Bid: £<?= h(number_format($yourBid, 2)) ?></p>
                <p>Status: <strong><?= h($status) ?></strong></p>


            
            <?php if ($status === 'ended'): ?>

    <?php
        // check if user already rated seller
        if ($isHighest) {
            $sellerId = (int)$b['sellerId'];

            $sqlCheck = "
                SELECT 1 
                FROM sellerRatings 
                WHERE auctionId = ? AND raterId = ?
                LIMIT 1
            ";
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->bind_param("ii", $auctionId, $userId);
            $stmtCheck->execute();
            $alreadyRated = ($stmtCheck->get_result()->num_rows > 0);
            $stmtCheck->close();
        }
    ?>

    <p>
        <?= $winnerText ?>

        <?php if ($isHighest): ?>
            <?php if (!$alreadyRated): ?>
                <button 
                    type="button"
                    class="btn btn-link p-0 align-baseline text-decoration-underline text-primary small ms-2"
                    data-toggle="modal"
                    data-target="#rateSellerModal"
                    data-auction-id="<?= $auctionId ?>"
                    data-seller-name="<?= h($b['sellerName']) ?>"
                    data-item-name="<?= h($itemName) ?>"
                >
                    Rate seller
                </button>

            <?php else: ?>
                <span class="text-muted small ms-2">(You rated this seller)</span>
            <?php endif; ?>
        <?php endif; ?>
    </p>

<?php else: ?>
    <p>Current highest bid: £<?= h(number_format($currentHighest, 2)) ?></p>
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

</div>

<!-- Rate Seller Modal -->
<div class="modal fade" id="rateSellerModal" tabindex="-1" role="dialog" aria-labelledby="rateSellerLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="rateSeller.php">
        <div class="modal-header">
          <h5 class="modal-title" id="rateSellerLabel">Rate seller</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span> <!-- For Bootstrap 4 -->
          </button>
        </div>
        <div class="modal-body">
          <p id="rateSellerItem" class="mb-1"></p>
          <p id="rateSellerSeller" class="mb-3 text-muted small"></p>

          <input type="hidden" name="auctionId" id="rateSellerAuctionId">

          <!-- Star Rating -->
          <div class="mb-3">
            <label class="form-label"><strong>Your Rating:</strong></label>

            <div id="starRating" class="star-rating">
              <span class="star" data-value="1">★</span>
              <span class="star" data-value="2">★</span>
              <span class="star" data-value="3">★</span>
              <span class="star" data-value="4">★</span>
              <span class="star" data-value="5">★</span>
            </div>

            <input type="hidden" name="rating" id="ratingValue" required>
          </div>

          <!-- Comment -->
          <div class="mb-3">
            <label for="rateSellerComment" class="form-label"><strong>Comment (optional):</strong></label>
            <textarea name="comment" id="rateSellerComment" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit Rating</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var rateModal = document.getElementById('rateSellerModal');
    if (!rateModal) return;

    //When the modal is about to be shown, fill in auction + seller info
    $('#rateSellerModal').on('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!button) return;

        var auctionId  = button.getAttribute('data-auction-id');
        var sellerName = button.getAttribute('data-seller-name');
        var itemName   = button.getAttribute('data-item-name');

        console.log('Opening rate modal for auctionId =', auctionId);

        document.getElementById('rateSellerAuctionId').value = auctionId;
        document.getElementById('rateSellerItem').textContent   = "Item: " + itemName;
        document.getElementById('rateSellerSeller').textContent = "Seller: " + sellerName;

        //Clear previous rating selection
        const stars = document.querySelectorAll("#starRating .star");
        const ratingValue = document.getElementById("ratingValue");
        stars.forEach(function (s) { s.classList.remove('selected'); });
        ratingValue.value = '';

        //Clear comment
        document.getElementById('rateSellerComment').value = "";
    });

    //Star rating logic
    const stars = document.querySelectorAll("#starRating .star");
    const ratingValue = document.getElementById("ratingValue");
    let selected = 0;

    function highlightStars(count) {
        stars.forEach((star, index) => {
            if (index < count) {
                star.classList.add("selected");
            } else {
                star.classList.remove("selected");
            }
        });
    }

    stars.forEach(star => {
        star.addEventListener("mouseover", function () {
            const value = parseInt(this.dataset.value);
            highlightStars(value);
        });

        star.addEventListener("mouseout", function () {
            highlightStars(selected);
        });

        star.addEventListener("click", function () {
            selected = parseInt(this.dataset.value);
            ratingValue.value = selected;  // store rating for form
            console.log("Selected rating =", selected);
            highlightStars(selected);
        });
    });

    //Prevent submitting without selecting a rating
    var form = document.querySelector('#rateSellerModal form');
    form.addEventListener('submit', function (e) {
        console.log("Submitting rating with:", {
            auctionId: document.getElementById('rateSellerAuctionId').value,
            rating: ratingValue.value
        });

        if (!ratingValue.value || ratingValue.value === "0") {
            e.preventDefault();
            alert("Please select a star rating before submitting.");
        }
    });
});
</script>

<?php include "footer.php"; ?>
