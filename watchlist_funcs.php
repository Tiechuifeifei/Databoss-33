<?php
// ************************************************************************************
// watchlist_funcs.php
// ************************************************************************************
// Watchlist has 3 functions
// 1) add_to_watchlist       —— 把某个 auction 加到关注列表里
// 2) view_watchlist         —— 查看我当前关注了哪些 auctions
// 3) remove_from_watchlist  —— 从关注列表里删掉某个 auction
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

// ************************************************************************************
// 1. Add to watchlist —— 加关注
// ************************************************************************************
function addToWatchlist($userId, $auctionId) {

    // 1. connect db 连接数据库
    $db = get_db_connection();

    // 2. write SQL 写SQL
    // 使用 INSERT IGNORE 防止重复添加
    $sql = "
        INSERT IGNORE INTO watchlist (userId, auctionId, addedAt)
        VALUES (?, ?, NOW())
    ";

// 3. prepare statement 预处理语句
// 预处理失败就返回 fail
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        return "fail";
    }

// 4. bind parameter 绑参数
    $stmt->bind_param("ii", $userId, $auctionId);

// 5. execute query 执行查询
    $stmt->execute();

// 6. close statement 关闭语句
    $stmt->close();

// 7. return success 返回成功
    return "success";
}



// ************************************************************************************
// 2. View watchlist —— 查看关注列表
// ************************************************************************************
function viewWatchlistByUser($userId)
{
    // 1. connect the db 连接数据库
    $db = get_db_connection();

// 2. write SQL 写SQL
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

// 3. prepare statement 预处理语句
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        // 失败就简单返回空列表
        return [];
    }

// 4. bind parameter 绑参数
    $stmt->bind_param("i", $userId);

// 5. execute query 执行查询
    $stmt->execute();

// 6. get result 拿结果
    $result = $stmt->get_result();

// 7. fetch all rows 去拿所有行
    $rows = [];
    while ($line = $result->fetch_assoc()) {
        $rows[] = $line;
    }

// 8. close statement 关闭语句
    $stmt->close();

// 9. return array 返回数组（给 PHP 直接用）
    return $rows;
}



// ************************************************************************************
// 3. Remove from watchlist —— 取消关注
// ************************************************************************************
function removeFromWatchlist($userId, $auctionId)
{

// 1. connect the db 连接数据库
    $db = get_db_connection();

// 2. prepare SQL 写SQL
    $sql = "
        DELETE FROM watchlist
        WHERE userId = ? AND auctionId = ?
    ";

// 3. prepare statement 预处理语句
    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        return "fail";
    }

// 4. bind parameter 绑参数
    $stmt->bind_param("ii", $userId, $auctionId);

// 5. execute query 执行查询
    $stmt->execute();

// 6. close statement 关闭语句
    $stmt->close();

// 7. return success 返回成功
    return "success";
}

// ************************************************************************************
// 如果 functionname 根本匹配不了就返回 fail
// ************************************************************************************
?>

