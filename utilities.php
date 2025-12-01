<link rel="stylesheet" href="css/custom_2.css">

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');


// Get database connection //
function get_db_connection(): mysqli {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbName = 'auction_website'; // modular DB


    $db = new mysqli($host, $user, $pass, $dbName);
    if ($db->connect_errno) {
        die('DB connect error: ' . $db->connect_error);
    }

    $db->set_charset('utf8mb4');
    return $db;
}

// Escape HTML //
function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Time remaining helper */
function display_time_remaining(DateInterval $interval): string {
    if ($interval->days == 0 && $interval->h == 0) {
        return $interval->format('%im %Ss');
    } elseif ($interval->days == 0) {
        return $interval->format('%hh %im');
    } else {
        return $interval->format('%ad %hh');
    }
}

// Render listing item
// YH DEBUG: We should use auctionId instead of userID
// YH DEBUG: auctionId not auction_id
// YH DEBUG: seperate scheduled/ running and ended auctions
function print_listing_li($auctionId, $title, $desc, $price, $num_bids, $endTime, $startTime, $status, $winnerName)
{
    $now = new DateTime();

    
    if ($status === 'scheduled') {
        $interval  = $now->diff($startTime);
        $time_text = "Starts in " . display_time_remaining($interval);
        $badge     = "<span class='badge bg-info text-dark'>Not started</span>";
    } elseif ($status === 'running') {
        $interval  = $now->diff($endTime);
        $time_text = display_time_remaining($interval) . " remaining";
        $badge     = "<span class='badge bg-success'>Running</span>";
    } else { // ended
        $time_text = "Auction ended";
        $badge     = "<span class='badge bg-secondary'>Ended</span>";
    }

    $bid_word = ($num_bids == 1) ? "bid" : "bids";
    ?>

    <li class="list-group-item listing-item">
        <div class="row align-items-start">

            <!--LEFT-->
            <div class="col-9 listing-left">
                <a href="listing.php?auctionId=<?= $auctionId ?>" class="listing-title">
                    <?= htmlspecialchars($title) ?>
                </a>

                <div class="listing-desc text-muted">
                    <?= htmlspecialchars($desc) ?>
                </div>

                <div class="listing-badge">
                    <?= $badge ?>
                </div>
            </div>

            <!--RIGHT-->
            <div class="col-3 listing-right text-end">
                <div class="listing-price">
                    £<?= number_format($price, 2) ?>
                </div>
                <div class="listing-bids">
                    <?= $num_bids . ' ' . $bid_word ?>
                </div>
                <div class="listing-time">
                    <?= htmlspecialchars($time_text) ?>
                </div>

                <?php if ($status === 'ended'): ?>
                    <div class="listing-winner">
                        <?php if ($winnerName): ?>
                            Winner: <?= htmlspecialchars($winnerName) ?>
                        <?php else: ?>
                            No bids were placed
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </li>

    <?php
}