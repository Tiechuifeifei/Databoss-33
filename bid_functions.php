<?php
require_once 'utilities.php';
// ******************************************************************************************************************************
// bids_functions.php
// ******************************************************************************************************************************
// Core Functions (5 total):
//
// 1. Get Highest Bid For Auction
//    Returns the highest bid row for a specific auction (or null if no bids).
//
// 2. Get Bids By Auction
//    "Auction's View": lists all bids for a given auction, including buyer's userName.
//
// 3. Get Bids By User
//    "Buyer's View": lists all bids placed by a given user, with related item & auction info.
//
// 4. View Bids On My Auctions
//    "Seller's View": lists all bids placed on the auctions created by the current seller.
// 
// 5. Place Bid
//    Allows a buyer to place a bid on an auction item, with multiple validation checks.
//    Returns a structured array: ["success" => true/false, "message" => "...", "bidId" => ...]
//
//
//
// Validation Rules Implemented:
//
// 1. Auction existence check:
//    Bidding is blocked if the auction does not exist.
//
// 2. Prevent self-bidding:
//    Sellers cannot place bids on their own auctions.
//
// 3. Auction status checks:
//    Rejects bids on cancelled, not-yet-started, or already-ended auctions.
//
// 4. Minimum bid requirement (no existing bids):
//    The first bid must be at least the start price set by the seller.
//
// 5. Minimum bid requirement (with existing bids):
//    Any new bid must be at least £5.00 higher than the current highest bid.
//
// 6. Basic input validation:
//    Rejects bid amounts less than or equal to £0.00 (non-numeric values are also rejected after type conversion).
//
// 7. Basic anti-spam rule:
//    The current highest bidder cannot place another bid on the same auction.
// ******************************************************************************************************************************
// what is in the bids table:
// - bidId      int
// - auctionId  int
// - buyerId    int
// - bidPrice   decimal(10,2)
// - bidTime    datetime
// ******************************************************************************************************************************





// ******************************************************************************************************************************
// 1. Get Highest Bid For Auction: 
//    To check the highest bid for one specific auction.
//
// How it works:
// - Input auctionId, output the highest bid row for that auctionId.
// - If there is no bid, then return null.
// ******************************************************************************************************************************

function getHighestBidForAuction($auctionId)
{
// 1) build the connection with database, $db是变量名，后面是函数。
    $db = get_db_connection();

// 2) write the SQL query
    $sql = "
        SELECT *
        FROM bids AS b
        WHERE b.auctionId = ?
        ORDER BY b.bidPrice DESC, b.bidTime ASC
        LIMIT 1
    ";

// 3) prepare SQL 预处理 SQL
    $stmt = $db -> prepare($sql);
    if (!$stmt) {
        return null;
    }

// 4) bind parameter 绑定参数
    $stmt -> bind_param("i", $auctionId);

// 5) execute query 执行查询
    $stmt -> execute();

// 6) get the result 得到结果
    $result = $stmt -> get_result();

// 7) fetch one row from the result 从结果集中抓取一行，这个抓的就是最高出价
    $row = $result -> fetch_assoc();

// 8) close the statement (not necessary, but better to have it)
    $stmt -> close();

// 9) if no bid, then return null. 没有出价的话，就返回 null 值。
    if (!$row) {
        return null;
    }

// 10) otherwise return the row
    return $row;
}




// ******************************************************************************************************************************
// 2. Get Bids By Auction: 
//    "Auction's View" - to check all bid/bids in an auction.
//
// How it works:
// - Input an auctionId, and then output all the bids belonging to this auctionId;
// - If there is no bid, then return an empty array.
// - Also can get the buyer's userName from users table, so can know who placed this bid
// 
// @param int $auctionId
// @return array
// ******************************************************************************************************************************

function getBidsByAuctionId($auctionId)
{
// 1) build the connection with database. 跟数据库建立连接
    $db = get_db_connection();

// 2) write SQL query 写 SQL
    $sql = "
        SELECT 
            b.*,
            u.userName AS buyerName
        FROM bids AS b
        JOIN users AS u ON b.buyerId = u.userId
        WHERE b.auctionId = ?
        ORDER BY b.bidPrice DESC, b.bidTime ASC
    ";// "ordered by time" must be written in SQL 数据库的排序规则必须在sql里写清楚，不能依赖前端。

// 3) prepare SQL 预处理 SQL
    $stmt = $db -> prepare($sql);
    if (!$stmt) {
        return [];
    }

// 4) bind parameter 绑定参数
    $stmt -> bind_param("i", $auctionId);

// 5 execute query 执行查询
    $stmt -> execute();

// 6) result 拿结果
    $result = $stmt -> get_result();

// 7) put all results in one array 把所有结果放进一个数组里
//    [] = array()
    $bids = [];
    while ($row = $result -> fetch_assoc()) {
        $bids[] = $row;
    }

// 8) close statement 关闭语句
    $stmt -> close();

// 9) return array 返回数组
    return $bids;
}




// ******************************************************************************************************************************
// 3. Get Bids By User: 
//    "Buyer's View" - basically "all my bids", a user(buyer) to check all his/her bid/bids.
//
// How it works:
// - Input userId, output all bids placed by this user/buyer for the item in the auction.
// - If the user has no bids, then return an empty array.
// - Also can get the itemName from items table, auction info from auctions table.
// ******************************************************************************************************************************

function getBidsByUser($userId)
{
// 1) connecte to db 连接数据库
    $db = get_db_connection();

// 2) 写sql
    $sql = "
        SELECT
            b.*,
            i.itemName,
            i.itemId,
            a.auctionId,
            a.auctionStartTime,
            a.auctionEndTime,
            a.auctionStatus,
            a.startPrice
        FROM bids AS b
        JOIN auctions AS a ON b.auctionId = a.auctionId
        JOIN items   AS i ON a.itemId     = i.itemId
        WHERE b.buyerId = ?
        ORDER BY FIELD(a.auctionStatus, 'running', 'scheduled', 'ended', 'cancelled'), b.bidTime DESC
    ";

// 3) prepare SQL 预处理 SQL
// if no value, then return empty array;
    $stmt = $db -> prepare($sql);
    if (!$stmt) {
        return [];
    }

// 4) bind parameter 绑定参数
    $stmt -> bind_param("i", $userId);

// 5) execute query 执行查询
    $stmt -> execute();

// 6) result 拿结果
    $result = $stmt -> get_result();

// 7) put all result in one array 把所有结果放进一个数组
    $bids = [];
    while ($row = $result -> fetch_assoc()) {
        $bids[] = $row; // ";" must be in the curly braces!
    }

// 8) close statement 关闭语句
    $stmt -> close();

// 9) return array 返回数组
    return $bids;
}




// ******************************************************************************************************************************
// 4. View Bids On My Auctions:
//    "Seller's View" - a user(seller) can view all bids that are placed on his/her auction/auctions.  
//
// How it works:
// - Input sellerId, output all bids were placed on this seller's auctions.
// - if there is no any bids, then return an empty array.
// - Also can get the buyer's userName from users table, so can know who placed this bid.
//
// @param INT $sellerId   ID of the seller (currently logged-in user as seller).
// @return ARRAY $bids.   Each line is a single bid info including the info from bids table, auctions table, items table, users table(buyer).
// ******************************************************************************************************************************

function viewBidsOnMyAuctions($sellerId)
{
// 1) connect to db 连接数据库
$db = get_db_connection();

// 2) 写sql
$sql = "
SELECT
a.auctionId,
a.itemId,
a.auctionStartTime,
a.auctionEndTime,
a.auctionStatus,
a.startPrice,

i.itemName,

b.bidId,
b.buyerId,
b.bidPrice,
b.bidTime,

u.userName

FROM auctions AS a 
JOIN items AS i ON a.itemId = i.itemId
LEFT JOIN bids AS b on a.auctionId = b.auctionId
LEFT JOIN users as u ON b.buyerId = u.userId

WHERE i.sellerId = ?
ORDER BY 
    FIELD(a.auctionStatus, 'running', 'scheduled', 'ended', 'cancelled'),
    a.auctionStartTime DESC,
    b.bidTime DESC
"; // ORDER BY 这样做可以有“状态优先级”，和Buyer's view一致。

// 3) Prepare sql 预处理sql
// if there is no value, then return an empty array for user, avoid show error.
$stmt = $db -> prepare($sql);
if ($stmt === false) {
    return array();
}

// 4) bind parameter 绑定参数
$stmt -> bind_param("i", $sellerId);

// 5) execute query 执行查询
$stmt -> execute();

// 6) result 拿到结果
$result = $stmt -> get_result();

// 7) put all results in one array 把所有结果都放进一个数组
$rows = [];
while ($line = $result -> fetch_assoc()) {
    $rows[] = $line;
}

// 8) close statement 关闭语句
// write close BEFORE return!
$stmt -> close();

// 9) return array 返回数组 (maybe empty)
return $rows;
}




// ******************************************************************************************************************************
// 5. Place Bid: 
//    A user(buyer) to place a bid on an item in an auction.
//
// How it works:
// 1) Validate bid price (> 0). (1)
// 2) Load auction info and check existence / seller / timing / status. (3.1, 3.3, 3.4, 3.5)
// 3) Apply bidding rules:
//    - First bid must be >= start price.(4.1)
//    - Current highest bidder cannot bid again. (4.2)
//    -New bid must be at least £5.00 higher than current highest bid. (4.3)
//    - Seller cannot bid on own auction. (3.2)
// 4) Insert the bid if all checks pass and return a result array.

//
// @param int $buyerId     ID of the user(buyer) who places the bid
// @param int $auctionId   ID of the auction that the bid is placed on
// @param float $bidPrice  the bid amount
//
// @return array:[
// "success" => true/false,
// "message" => "message to be shown for user"
// "bidId" => ID of the new bid, only exists when success = true]
// ******************************************************************************************************************************

function placeBid($buyerId, $auctionId, $bidPrice)
{
// 1) Validate bid price > 0 检查出价的价格大于0
    if ($bidPrice <= 0) { 
        return [
            "success" => false,
            "message" => "Your bid amount must be greater than £0.00."
        ];
    }

// 2) Build the connection with db 连接数据库
    $db = get_db_connection();

// 3) Check auction's info and status. 检查拍卖的信息、状态等
// YH DEBUG: sellerID is not in the auction table
$sqlAuction = "
    SELECT 
        i.sellerId,
        a.auctionStartTime,
        a.auctionEndTime,
        a.auctionStatus,
        a.startPrice
    FROM auctions a
    JOIN items i ON a.itemId = i.itemId
    WHERE a.auctionId = ?
    LIMIT 1
";

$stmtAuction = $db->prepare($sqlAuction);

        // Pre-check auction's existence.
    $stmtAuction = $db -> prepare($sqlAuction);
    if ($stmtAuction === false) {
        return [
            "success" => false,
            "message" => "Sorry, something went wrong. Please try again later."
        ];
    }
    $stmtAuction -> bind_param("i", $auctionId);
    $stmtAuction -> execute();

    $resultAuction = $stmtAuction -> get_result();
    $auctionRow    = $resultAuction -> fetch_assoc();
    $stmtAuction   -> close();

    // (3.1) Auction must exist, otherwise return reminder. 拍卖必须要存在，否则提示不存在然后返回。
    if (!$auctionRow) {
        return [
            "success" => false,
            "message" => "This auction does not exist."
        ];
    }
        // Check the if there is a startPrice, avoid error. 检查起拍价格是不是存在，避免报错。
    $startPrice = isset($auctionRow["startPrice"]) 
                  ? (float)$auctionRow["startPrice"] 
                  : 0.0;
        // SellerId for self-bidding validation. 拿到seller的ID，为后面身份检查做准备。
    $sellerId         = (int)$auctionRow["sellerId"];
        // For time-based auction check later 保留开始和结束时间，一会判断拍卖的“未开始”和“已结束”。
    $auctionStartTime = $auctionRow["auctionStartTime"];
    $auctionEndTime   = $auctionRow["auctionEndTime"];
        // Use null if auctionStatus is missing or null. 如果有auctionStatus，就用！如果没有status，那就用null!
        // "??"是null合并运算符号：if the value on left side not exist or is null, then use the value on right side.
        // 这样写 === if (isset($auctionRow["auctionStatus"])) {$auctionStatus = $auctionRow["auctionStatus"];} else {$auctionStatus = null;}
    $auctionStatus    = $auctionRow["auctionStatus"] ?? null; 
        // current timestamp, for time-validation later.
    $nowTimestamp     = time();

    // (3.2) Seller cannot bid on his/her own auction, return reminder. 禁止卖家给自己拍卖出价
    if ((int)$buyerId === $sellerId) {
        return [
            "success" => false,
            "message" => "Sorry, you cannot place a bid on your own auction."
        ];
    }

    // (3.3) Auction has been cancelled, return reminder. 如果拍卖已被手动取消，禁止出价
    if ($auctionStatus === 'cancelled') {
        return [
            "success" => false,
            "message" => "This auction is not available for bidding anymore."
        ];
    }

    // (3.4) Auction not started yet, return reminder. 拍卖未开始，不允许出价
    if (!empty($auctionStartTime)) {
        $startTimestamp = strtotime($auctionStartTime);
        if ($nowTimestamp < $startTimestamp) {
            return [
                "success" => false,
                "message" => "Sorry, this auction has not started yet, you cannot place a bid."
            ];
        }
    }

    // (3.5) Auction already ended, return reminder. 拍卖已结束，不允许再出价
    if (!empty($auctionEndTime)) {
        $endTimestamp = strtotime($auctionEndTime);
        if ($nowTimestamp >= $endTimestamp) {
            return [
                "success" => false,
                "message" => "Sorry, this auction has already ended, you cannot place a bid anymore."
            ];
        }
    }


// 4) Check the current highest bid price 检查当前最高出价
    $currentHighest = getHighestBidForAuction($auctionId);

    // (4.1) First time bid rule: must higher than the start price(if start price > 0), otherwise return reminder.首次出价规则：必须高于卖家设置的起拍价
if ($currentHighest === null) {
    if ($startPrice > 0 && $bidPrice < $startPrice) {
        return [
            "success" => false,
            "message" => "Your first bid must be at least the start price (£" . number_format($startPrice, 2) . ")."
        ];
    }
}

        // There is already a highest bid, compare new bid with current highest bid.
    if ($currentHighest !== null) {
    $currentHighestPrice = (float)$currentHighest["bidPrice"];
    $currentHighestBuyer = (int)$currentHighest["buyerId"];

    // (4.2) Current highest bidder cannot bid again, otherwise return reminder. 当前最高出价者不能再出价
    if ($currentHighestBuyer === (int)$buyerId) {
        return [
            "success" => false,
            "message" => "You are already the highest bidder for this auction."
        ];
    }

    // (4.3) Minimum increase for bid 最小的加价幅度
    $minIncrease = 5.00;  // can be a difference number
    if ($bidPrice < $currentHighestPrice + $minIncrease) {
        return [
            "success" => false,
            "message" => "Please increase your bid by at least £5.00."
        ];
    }
}
    
// 5) Insert the new bid into bids table 把新的出价插入导入bids表
    $sql = "
    INSERT INTO bids (auctionId, buyerId, bidPrice, bidTime)
    VALUES (?, ?, ?, NOW())";

// 6) prepare sql 预处理sql(报错）)
    $stmt = $db -> prepare($sql);
    if ($stmt === false) {
        return [
            "success" => false,
            "message" => "Sorry, something went wrong. Please try again later."
        ];
    }

// 7) bind parameters 绑定参数
// "iid" is an arguments list, representing VALUE(?, ?, ?)"
    $stmt -> bind_param("iid", $auctionId, $buyerId, $bidPrice);

// 8) execute quesry 执行查询（报错）
    $result = $stmt->execute();
    if ($result === false) {
        $stmt->close();
        return [
            "success" => false,
            "message" => "Sorry, something went wrong. Please try again later."
        ];
    }

// 9) if success, get the new bidId created by database(AUTO_INCREMENT)
// insert_id means get the lastest ID of the last insert operation, doesn't mean insert an ID etc.
    $newBidId = $db -> insert_id;
    
// 10) close statement 关闭语句
    $stmt -> close();

// 11) return the array 返回数组
    return array(
        "success" => true,
        "message" => "Congratulations! You placed the bid successfully.",
        "bidId"   => $newBidId
    );
}