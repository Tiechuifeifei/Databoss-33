<?php
require_once("db_connect.php");   
require_once("Item_function.php");   
require_once("bid_functions.php");
require_once(__DIR__ . '/utilities.php');

/*
|--------------------------------------------------------------------------
| Auction FUNCTIONS
|--------------------------------------------------------------------------
| This file contains all auction-related backend logic:
| - 1. create a new auction 
| - 2. get the auction details
| - 3. search for the active auction -for browse ！！！
| - 4. get the current highest price - for bid!!!
| - 5. update auctionstatus automatically 
| - 6. get the remaining time - call utilities.php 
| - 7. all listing - for item
| - 8. endAuctions - update the auction when it ends
| - 9. Close auction only if ended
| - 10. cancel auction 
| - 11. update status ???? could be deleted?
| - 12. get acution by itemid
| - 13. refresh all auctions - call 5
| - 14. is auction successful?
|---------------------------------------------------------------------------
*/

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
        $endTime
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
            a.reservedPrice,
            a.winningBidId,
            i.itemName,
            i.itemDescription,
            i.categoryId,
            i.sellerId,
            COALESCE(MAX(b.bidPrice), a.startPrice) AS currentPrice,
            COUNT(b.bidId) AS numBids

        FROM auctions a
        JOIN items i ON a.itemId = i.itemId
        LEFT JOIN bids b ON a.auctionId = b.auctionId

        WHERE a.auctionStatus IN ('scheduled', 'running')

        GROUP BY a.auctionId
        ORDER BY a.auctionStartTime ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}


// 4. 更新auction当前价格（被bid模块调用）
// call from bid module
function getCurrentHighestPrice($auctionId) {
    global $conn;

    $sql = "SELECT MAX(bidPrice) AS highestPrice
            FROM bids
            WHERE auctionId = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    // 如果没人出价，返回起拍价 if there is no bid, then go back to the start price 
    if ($result['highestPrice'] === null) {

        // 查起拍价serch for the startprice
        $sql2 = "SELECT startPrice FROM auctions WHERE auctionId = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $auctionId);
        $stmt2->execute();
        $starting = $stmt2->get_result()->fetch_assoc();

        return $starting['startPrice'];
    }

    return $result['highestPrice'];
}

// update auctionstatus automatically 
// 5. 自动根据时间刷新auction状态，并返回是否已经结束
function refreshAuctionStatus($auctionId) {
    global $conn;

    // 1. Get timeline + current status + itemId
    $sql = "
        SELECT auctionStartTime, auctionEndTime, auctionStatus, itemId
        FROM auctions
        WHERE auctionId = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return false;
    }

    $row = $result->fetch_assoc();
    $itemId = $row['itemId'];

    $now   = new DateTime();
    $start = new DateTime($row['auctionStartTime']);
    $end   = new DateTime($row['auctionEndTime']);

    $status = $row['auctionStatus'];
    if ($now < $start && $status !== 'scheduled') {

        $newStatus = 'scheduled';
        $sql2 = "UPDATE auctions SET auctionStatus = ? WHERE auctionId = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("si", $newStatus, $auctionId);
        $stmt2->execute();

        updateItemStatus($itemId, 'inactive');

        return 'scheduled';
    }

    if ($now >= $start && $now < $end && $status !== 'running') {

        $newStatus = 'running';
        $sql2 = "UPDATE auctions SET auctionStatus = ? WHERE auctionId = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("si", $newStatus, $auctionId);
        $stmt2->execute();

        updateItemStatus($itemId, 'active');

        return 'running';
    }

    if ($now >= $end && $status !== 'ended') {

        $newStatus = 'ended';
        $sql2 = "UPDATE auctions SET auctionStatus = ? WHERE auctionId = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("si", $newStatus, $auctionId);
        $stmt2->execute();

        $highestBid = getHighestBidForAuction($auctionId);

        if ($highestBid) {
            updateItemStatus($itemId, 'sold');
        } else {
            updateItemStatus($itemId, 'inactive');
        }

        return 'ended';
    }

    if ($status === 'cancelled') {
        updateItemStatus($itemId, 'withdrawn');
        return 'cancelled';
    }

    return $status; 
}



// 6. get the remaining time: call utilities.php
// 获取某个auction的剩余时间
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

    // if ended 
    if ($now >= $end_time) {
        return "Auction ended";
    }

    // count the remaining time
    $interval = $now->diff($end_time);
    return display_time_remaining($interval);  // from utilities.php
}



// 7. 获取某个用户创建的所有拍卖（用于“我的拍卖”）
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

//8. endAuctions -- update the auction when it ends
//8. endAuctions -- update the auction when it ends
function endAuction($auctionId) {
    file_put_contents(__DIR__ . '/auction_email_debug.txt', "endAuction called for auction {$auctionId}\n", FILE_APPEND);

    global $conn;

    $auctionId = (int)$auctionId;

    // 1. Get highest bid (id + price)
    $sql = "SELECT bidId, bidPrice
            FROM bids
            WHERE auctionId = ?
            ORDER BY bidPrice DESC
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $highestBid = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 2. Get itemId for status update
    $sql_item = "SELECT itemId FROM auctions WHERE auctionId = ?";
    $stmt_item = $conn->prepare($sql_item);
    $stmt_item->bind_param("i", $auctionId);
    $stmt_item->execute();
    $rowItem = $stmt_item->get_result()->fetch_assoc();
    $stmt_item->close();

    if (!$rowItem) {
        return; // auction not found
    }
    $itemId = $rowItem['itemId'];

    if ($highestBid) {
        // 3. Update sold price + winning bid
        $sql2 = "UPDATE auctions
                 SET soldPrice = ?, winningBidId = ?
                 WHERE auctionId = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param(
            "dii",
            $highestBid['bidPrice'],
            $highestBid['bidId'],
            $auctionId
        );
        $stmt2->execute();
        $stmt2->close();

        // 4. Update item to sold
        updateItemStatus($itemId, "sold");

    } else {
        // no bids
        $sql3 = "UPDATE auctions
                 SET soldPrice = NULL, winningBidId = NULL
                 WHERE auctionId = ?";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("i", $auctionId);
        $stmt3->execute();
        $stmt3->close();

        // No winner → item inactive
        updateItemStatus($itemId, "inactive");
    }

    // 5. NOW send emails (after DB is correct)
    notifyAuctionEnded($auctionId);
}


// 9. Close auction only if ended
function closeAuctionIfEnded($auctionId) {
    // refresh auction status (updates DB if needed)
    $ended = refreshAuctionStatus($auctionId);

    // If refresh says ended (string 'ended' or truthy), run endAuction()
    if ($ended) {
        file_put_contents(__DIR__ . '/auction_email_debug.txt', "closeAuctionIfEnded: endAuction called for auction {$auctionId}\n", FILE_APPEND);
        endAuction($auctionId);
        return "Auction closed (ended).";
    }

    return "Auction still active.";
}


// 10. cancel auction 
function cancelAuction($auctionId, $itemId) {
    global $conn;

    // 1. cancel auction
    $sql1 = "
        UPDATE auctions
        SET auctionStatus = 'cancelled'
        WHERE auctionId = ?
    ";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $auctionId);
    $stmt1->execute();

    // 2. reset item to inactive
    $sql2 = "
        UPDATE items
        SET itemStatus = 'inactive'
        WHERE itemId = ?
    ";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $itemId);

    return $stmt2->execute();
}

//12. YH: get auction by itemId and we can get auctionId by link to itemid, I wrote in one file but I too tired to find it. I am pretty sure I wrote it. 
function getAuctionByItemId($itemId) {
    global $conn;
    $sql = "SELECT * FROM auctions WHERE itemId = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

//13. refresh all auctions
function refreshAllAuctions() {
    global $conn;

    $sql = "SELECT auctionId FROM auctions";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        refreshAuctionStatus($row['auctionId']);
    }
}

// is the auction successful?
function isAuctionUnsuccessful($auctionId) {
    $auction = getAuctionById($auctionId);
    if (!$auction) return false;

    if ($auction['auctionStatus'] !== 'ended') {
        return false;
    }

    $highestBid = getHighestBidForAuction($auctionId);

    if (!$highestBid) {
        return true;
    }

    if ($highestBid['bidPrice'] < $auction['reservedPrice']) {
        return true;
    }

    return false;
}

/**
 * Notify all bidders when an auction has ended.
 * - Winner gets a "you won" email
 * - Other bidders get a "you did not win" email
 * - Seller gets a summary email
 */
function notifyAuctionEnded($auctionId)
{
    file_put_contents(__DIR__ . '/auction_email_debug.txt', "notifyAuctionEnded called for auction {$auctionId}\n", FILE_APPEND);

    $db = get_db_connection();
    $auctionId = (int)$auctionId;

    // 1. Get auction + item + seller info + winner info (if any)
    $sqlAuction = "
        SELECT 
            a.auctionId,
            a.auctionEndTime,
            a.soldPrice,
            a.winningBidId,
            i.itemName,
            i.sellerId,
            s.userName  AS sellerName,
            s.userEmail AS sellerEmail
        FROM auctions a
        JOIN items  i ON a.itemId   = i.itemId
        JOIN users  s ON i.sellerId = s.userId
        WHERE a.auctionId = ?
        LIMIT 1
    ";

    $stmt = $db->prepare($sqlAuction);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $auctionRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$auctionRow) {
        return; // auction not found, nothing to notify
    }

    $itemName     = $auctionRow['itemName'];
    $sellerId     = (int)$auctionRow['sellerId'];
    $sellerName   = $auctionRow['sellerName'];
    $sellerEmail  = $auctionRow['sellerEmail'];
    $soldPrice    = $auctionRow['soldPrice'];
    $winningBidId = $auctionRow['winningBidId'];

    // 2. Get winner info, if there is a winner
    $winnerId   = null;
    $winnerName = null;
    $winnerEmail= null;

    if (!is_null($winningBidId)) {
        $sqlWinner = "
            SELECT 
                b.bidId,
                b.bidPrice,
                u.userId,
                u.userName,
                u.userEmail
            FROM bids b
            JOIN users u ON b.buyerId = u.userId
            WHERE b.bidId = ?
            LIMIT 1
        ";

        $stmt = $db->prepare($sqlWinner);
        $stmt->bind_param("i", $winningBidId);
        $stmt->execute();
        $winRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($winRow) {
            $winnerId    = (int)$winRow['userId'];
            $winnerName  = $winRow['userName'];
            $winnerEmail = $winRow['userEmail'];
        }
    }

    // 3. Get all distinct bidders for this auction
    $sqlBidders = "
        SELECT DISTINCT 
            u.userId,
            u.userName,
            u.userEmail
        FROM bids b
        JOIN users u ON b.buyerId = u.userId
        WHERE b.auctionId = ?
    ";

    $stmt = $db->prepare($sqlBidders);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $biddersRes = $stmt->get_result();
    $stmt->close();

    // 4. Notify each bidder if they won or not
    while ($row = $biddersRes->fetch_assoc()) {
        $bidderId    = (int)$row['userId'];
        $bidderName  = $row['userName'];
        $bidderEmail = $row['userEmail'];

        if (!filter_var($bidderEmail, FILTER_VALIDATE_EMAIL)) {
            continue; // skip weird emails
        }

        if (!is_null($winnerId) && $bidderId === $winnerId) {
            // Winner email
            $subject = "You won the auction: {$itemName}";
            $body = "Hi {$bidderName},\n\n"
                  . "Congratulations! You have won the auction for '{$itemName}'.\n";

            if (!is_null($soldPrice)) {
                $body .= "Final price: £" . number_format((float)$soldPrice, 2) . "\n";
            }

            $body .= "\nPlease log in to your account to view the details.\n\n"
                   . "Regards,\nAuction Website";

        } else {
            // Non-winner email
            $subject = "Auction ended: {$itemName}";
            $body = "Hi {$bidderName},\n\n"
                  . "The auction for '{$itemName}' has now ended.\n";

            if (!is_null($winnerName)) {
                $body .= "Unfortunately, you did not win this time.\n";
            } else {
                $body .= "The auction ended with no winning bid.\n";
            }

            $body .= "\nYou can log in to view other items and bid again.\n\n"
                   . "Regards,\nAuction Website";
        }

        sendEmail($bidderEmail, $subject, $body);
    }

    // 5. Email seller a summary
    if (filter_var($sellerEmail, FILTER_VALIDATE_EMAIL)) {
        $subject = "Your auction has ended: {$itemName}";

        if (!is_null($winnerId)) {
            $body = "Hi {$sellerName},\n\n"
                  . "Your auction for '{$itemName}' has ended and a buyer has won the item.\n";

            if (!is_null($soldPrice)) {
                $body .= "Final price: £" . number_format((float)$soldPrice, 2) . "\n";
            }

            $body .= "\nPlease log in to your account to see the winner's details.\n\n"
                   . "Regards,\nAuction Website";
        } else {
            $body = "Hi {$sellerName},\n\n"
                  . "Your auction for '{$itemName}' has ended, but there was no winning bid.\n"
                  . "You may wish to relist the item.\n\n"
                  . "Regards,\nAuction Website";
        }

        sendEmail($sellerEmail, $subject, $body);
    }
}

?>

