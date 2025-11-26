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
function print_listing_li($auctionId, $title, $desc, $price, $num_bids, $endTime, $startTime, $status)
{
    $now = new DateTime();

    // Compute status text
    if ($status === 'scheduled') {

        $interval = $now->diff($startTime);
        $time_text = "Starts in " . display_time_remaining($interval);

        $badge = "<span class='badge bg-info text-dark'>Not started</span>";

    } elseif ($status === 'running') {

        $interval = $now->diff($endTime);
        $time_text = display_time_remaining($interval) . " remaining";

        $badge = "<span class='badge bg-success'>Running</span>";

    } else { // ended
        $time_text = "Auction ended";
        $badge = "<span class='badge bg-secondary'>Ended</span>";
    }

    echo "
    <li class='list-group-item'>
      <div class='d-flex justify-content-between'>

        <div>
          <a href='listing.php?auctionId=$auctionId' class='fw-bold'>$title</a><br>
          <small class='text-muted'>$desc</small><br>
          $badge
        </div>

        <div class='text-end'>
          <strong>£" . number_format($price, 2) . "</strong><br>
          <small>$num_bids " . ($num_bids == 1 ? "bid" : "bids") . "</small><br>
          <small class='text-muted'>$time_text</small>
        </div>

      </div>
    </li>
    ";
}
