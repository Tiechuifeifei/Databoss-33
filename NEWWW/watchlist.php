<?php

// watchlist_funcs.php
// Watchlist has 3 functions
// 1.add_to_watchlist 
// 2.view_watchlist
// 3.remove_from_watchlist

require_once 'utilities.php';

//open the session first, otherwise can't get userId
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Add to watchlist
function addToWatchlist($userId, $auctionId) {
    $db = get_db_connection();
    // 使用 INSERT IGNORE 防止重复添加
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
// 连 auctions 和 items，让界面上不仅能看到 auctionId，还能看到 itemName / startPrice / status / startTime 等等。
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
function removeFromWatchlist($userId, $auctionId)
{
$db = get_db_connection();
$sql = "
        DELETE FROM watchlist
        WHERE userId = ? AND auctionId = ?
    ";
$stmt = $db->prepare($sql);
    if ($stmt === false) {
        return "fail";
    }
$stmt->bind_param("ii", $userId, $auctionId);
$stmt->execute();
$stmt->close();
return "success";
}
// YH: Check if auction is in user's watchlist then return null
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
    $exists = ($result->num_rows > 0);

    $stmt->close();
    return $exists;
}

?>

