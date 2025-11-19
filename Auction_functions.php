<?php
require_once("../db_connect.php");   
require_once(__DIR__ . '/../utilities.php');

//  AUCTION FUNCTIONS

// 1. 创建新的 auction（item 上传后）/create auction after the item is uploaded 
function createAuction($itemId, $startPrice, $reservePrice, $startTime, $endTime) {
    global $conn;  

    $sql = "INSERT INTO Auctions 
            (itemId, startPrice, reservedPrice, auctionStartTime, auctionEndTime, auctionStatus)
            VALUES (?, ?, ?, ?, ?, 'scheduled')";
// NOTE: This itemId is temporary for testing.
// After the Item module is implemented, itemId will be obtained from createItem(). 对接itemid 从createitem中
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iddss", 
        $itemId, 
        $startPrice, 
        $reservePrice, 
        $startTime, 
        $endTime,
    );

    $stmt->execute();
    return $stmt->insert_id;   // mysqli 的 lastInsertId
}

// 2. 获取某个 auction 的详情（browse.php / listing.php 用）/////
// Get auction detail including joined item info.
//TODO: After item team completes createItem(), integrate dynamic itemId.// join item的表格，调用itemid
function getAuctionById($auctionId) {
    global $conn; 
        $sql = "
            SELECT 
                a.auctionId,
                a.itemId,
                a.auctionStartTime,
                a.auctionEndTime,
                a.auctionStatus,
                a.startPrice,
                a.soldPrice,
                a.winningBidId,
                a.reservedPrice,
                i.itemName,
                i.itemDescription,
                i.categoryId,
                i.sellerId
            FROM auctions a
            JOIN items i ON a.itemId = i.itemId
            WHERE a.auctionId = ?
            LIMIT 1
        ";
        // 1. Prepare
        $stmt = $conn->prepare($sql);
        // 2. Bind parameter
        $stmt->bind_param("i", $auctionId);
        // 3. Execute
        $stmt->execute();
        // 4. Get result
        $result = $stmt->get_result();
        // 5. Return associative array or null
        return $result->fetch_assoc();
    }
    

// 3. 查询所有 active auction（browse 用）
function getActiveAuctions() {
    global $conn;

    $sql = "
        SELECT 
            a.auctionId,
            a.itemId,
            a.auctionStartTime,
            a.auctionEndTime,
            a.auctionStatus,
            a.startPrice,
            a.soldPrice,
            a.winningBidId,
            a.reservedPrice,
            i.itemName,
            i.itemDescription,
            i.categoryId,
            i.sellerId
        FROM auctions a
        JOIN items i ON a.itemId = i.itemId
        WHERE a.auctionStatus IN ('scheduled', 'running')
        ORDER BY a.auctionStartTime ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    // 返回 array of associative arrays
    return $result->fetch_all(MYSQLI_ASSOC);
}

// 4. 更新 auction 当前价格（被 bid 模块调用）
// call from bid module
// bid team should notice this one is hard, you need to find out the highest bidprice and give the id to auction. 
function updateAuctionWinningBid($auctionId, $bidId) {
    global $conn;

    $sql = "
        UPDATE auctions
        SET winningBidId = ?
        WHERE auctionId = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $bidId, $auctionId);

    return $stmt->execute();  // true 或 false
}

// update auctionstatus automatically 
// 5. 自动根据时间刷新 auction 状态，并返回是否已经结束
function refreshAuctionStatus($auctionId) {
    global $conn;

    //Query times + current status
    $sql = "
        SELECT auctionStartTime, auctionEndTime, auctionStatus
        FROM auctions
        WHERE auctionId = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return false; // Auction not found
    }

    $row = $result->fetch_assoc();

    // cancelled auctions are treated as ended
    if ($row['auctionStatus'] === 'cancelled') {
        return true;
    }

    // Compare with system time
    $now   = new DateTime();
    $start = new DateTime($row['auctionStartTime']);
    $end   = new DateTime($row['auctionEndTime']);

    if ($now < $start) {
        $newStatus = "scheduled";
    } 
    else if ($now >= $start && $now < $end) {
        $newStatus = "running";
    } 
    else {
        $newStatus = "ended";
    }

    // Update DB if status changed (BUT DON'T RETURN HERE!)
    if ($newStatus !== $row['auctionStatus']) {
        $updateSql = "
            UPDATE auctions
            SET auctionStatus = ?
            WHERE auctionId = ?
        ";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $newStatus, $auctionId);
        $updateStmt->execute();
    }

    // Always return correct "is ended?" boolean
    return ($newStatus === "ended" || $newStatus === "cancelled");
}


//6. get the remaining time: call utilities.php
// 6. 获取某个 auction 的剩余时间（返回格式化后的字符串）
// interact with utilities.php
function getRemainingTime($auctionId) {
    global $conn;

    $sql = "SELECT auctionEndTime FROM auctions WHERE auctionId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return "Auction not found";
    }

    $row = $result->fetch_assoc();
    $end_time = new DateTime($row['auctionEndTime']);
    $now = new DateTime();

    // 如果已经结束
    if ($now >= $end_time) {
        return "Auction ended";
    }

    // 否则计算剩余时间
    $interval = $now->diff($end_time);
    return display_time_remaining($interval);  // 来自 utilities.php 的格式化函数
}



// 7 获取某个用户创建的所有拍卖（用于“我的拍卖”）
// call from item

function getAuctionsByUser($userId) {
    global $conn;

    $sql = "
        SELECT 
            a.auctionId,
            a.itemId,
            a.auctionStartTime,
            a.auctionEndTime,
            a.auctionStatus,
            a.startPrice,
            a.soldPrice,
            a.winningBidId,
            a.reservedPrice,
            
            i.itemName,
            i.itemDescription,
            i.categoryId,
            i.sellerId
        FROM auctions a
        JOIN items i ON a.itemId = i.itemId
        WHERE i.sellerId = ?
        ORDER BY a.auctionStartTime DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

?>