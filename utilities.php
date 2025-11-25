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

/** Render listing item */
function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time): void {

    if (!($end_time instanceof DateTime)) {
        $end_time = new DateTime($end_time);
    }

    $desc_short = strlen($desc) > 250 ? substr($desc, 0, 250) . '...' : $desc;
    $bid_label = ((int)$num_bids === 1) ? ' bid' : ' bids';

    $now = new DateTime();
    $time_remaining = ($now > $end_time)
        ? 'This auction has ended'
        : display_time_remaining(date_diff($now, $end_time)) . ' remaining';

    echo '
    <li class="list-group-item d-flex justify-content-between">
        <div class="p-2 mr-5">
            <h5>
                <a href="listing.php?item_id=' . (int)$item_id . '">' . h($title) . '</a>
            </h5>'
            . h($desc_short) .
        '</div>

        <div class="text-center text-nowrap">
            <span style="font-size: 1.5em">£' . number_format((float)$price, 2) . '</span><br/>'
            . (int)$num_bids . $bid_label . '<br/>' . h($time_remaining) .
        '</div>
    </li>';
}