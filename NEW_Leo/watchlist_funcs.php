<?php
// ************************************************************************************
// watchlist_funcs.php
// ************************************************************************************
// Watchlist has 3 functions
// 1) add_to_watchlist —— 把某个auction加到关注列表里
// 2) view_watchlist —— 查看我当前关注了哪些auctions
// 3) remove_from_watchlist —— 从关注列表里删掉某个auction
//
// What's in the watchlist table:
// - watchId          INT(11) NOT NULL,
// - userId           INT(10) UNSIGNED NOT NULL,
// - auctionId        INT(11) UNSIGNED NOT NULL,
// - addedAt datetime NOT NULL DEFAULT current_timestamp()
// ************************************************************************************

require_once 'utilities.php';

// open the session first, otherwise can't get userId
// 记得先把 session 打开，不然拿不到 userId
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// 1. Add to watchlist
function addToWatchlist($userId, $auctionId) {

    $db = get_db_connection();

//“INSERT IGNORE” to avoid adding the same auction again
    $sql = "
        INSERT IGNORE INTO watchlist (userId, auctionId, addedAt)
        VALUES (?, ?, NOW())
    ";

    $stmt = $db->prepare($sql);

    $stmt->bind_param("ii", $userId, $auctionId);
    $stmt->execute();

    $stmt->close();
    return "success";
}




// 2. View watchlist —— 查看关注列表

function viewWatchlistByUser($userId)
{
    $db = get_db_connection();

//join auctions&items, to view auction info, and also item's info too.
    $sql = "
        SELECT
            w.watchId,
            w.auctionId,
            w.addedAt,

            a.itemId,
            a.auctionStartTime,
            a.auctionEndTime,
            a.auctionStatus,
            a.startPrice,

            i.itemName

        FROM watchlist AS w
        JOIN auctions AS a ON w.auctionId = a.auctionId
        JOIN items    AS i ON a.itemId    = i.itemId

        WHERE w.userId = ?

        ORDER BY w.addedAt DESC
    ";

    $stmt = $db->prepare($sql);

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $rows = [];
    while ($line = $result->fetch_assoc()) {
        $rows[] = $line;
    }
    $stmt->close();
    return $rows;
}




// 3.Remove from watchlist
function removeFromWatchlist($userId, $auctionId)
{

$db = get_db_connection();

$sql_check = "
SELECT watchId
FROM watchlist
WHERE userId = ? AND auctionId = ?
LIMIT 1
";
$stmt = $db->prepare($sql_check);
$stmt->bind_param("ii", $userId, $auctionId);
$stmt->execute();
$result = $stmt->get_result();
$exists = ($result->num_rows > 0);
$stmt->close();

// 2 situations:
// if not in watchlist, return a reminder to user
if (!$exists) {
return "You are not watching this auction, so it cannot be removed.";
}

// if yes, then is allowed to remove.
$sql_delete = "
DELETE FROM watchlist
WHERE userId = ? AND auctionId = ?
";
$stmt = $db->prepare($sql_delete);
$stmt->bind_param("ii", $userId, $auctionId);
$stmt->execute();
$stmt->close();

return "removed_success";
}


//Leo debug, add one more fucntion "isInWatchList()":

//4. Check if auction is in user's watchlist
function isInWatchlist($userId, $auctionId)
{
    $db = get_db_connection();
    $sql = "
        SELECT watchId
        FROM watchlist
        WHERE userId = ? AND auctionId = ?
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);

    $stmt->bind_param("ii", $userId, $auctionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = ($result && $result->num_rows > 0);
    $stmt->close();

    return $exists;
}
?>
