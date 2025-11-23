<?php
require_once 'utilities.php';
// ******************************************************************************************************************************
// bids_functions.php
// ******************************************************************************************************************************
// This part has 5 major functions, names are as follow:
// 1. Get Highest Bid For Auction: 
//    To check the highest bid for one specific auction.
// 2. Get Bids By Auction: 
//    “Auction's View" - to check all bid/bids in an auction.
// 3. Get Bids By User: 
//    "Buyer's View" - basically "all my bids", a user(buyer) to check all his/her bid/bids.
// 4. Place Bid: 
//    A user(buyer) to place a bid on an item in an auction.
// 5. View Bids On My Auctions:
//    "Seller's View" - a user(seller) can view all bids that are placed on his/her auction/auctions.  
//
//
// what’s in the bids table:
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
// 1. build the connection with database, $db是变量名，后面是函数。
    $db = get_db_connection();

// 2. write the SQL query
    $sql = "
        SELECT *
        FROM bids AS b
        WHERE b.auctionId = ?
        ORDER BY b.bidPrice DESC, b.bidTime ASC
        LIMIT 1
    ";

// 3. prepare SQL 预处理 SQL
    $stmt = $db -> prepare($sql);
    if (!$stmt) {
        return null;
    }

// 4. bind parameter 绑定参数
    $stmt -> bind_param("i", $auctionId);

// 5. execute query 执行查询
    $stmt -> execute();

// 6. get the result 得到结果
    $result = $stmt -> get_result();

// 7. fetch one row from the result 从结果集中抓取一行，这个抓的就是最高出价
    $row = $result -> fetch_assoc();

// 8. close the statement (not necessary, but better to have it)
    $stmt -> close();

// 9. if there is no bid, then return null. 没有出价的话，就返回 null 值。
// "!" means "not"
    if (!$row) {
        return null;
    }

// otherwise return the row
    return $row;
}




// ******************************************************************************************************************************
// 2. Get Bids By Auction: 
//    “Auction's View" - to check all bid/bids in an auction.
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
// 1. build the connection with database, first step for every function.
// 跟数据库建立连接
    $db = get_db_connection();

// 2. write SQL query 写 SQL
    $sql = "
        SELECT 
            b.*,
            u.userName AS buyerName
        FROM bids AS b
        JOIN users AS u ON b.buyerId = u.userId
        WHERE b.auctionId = ?
        ORDER BY b.bidPrice DESC, b.bidTime ASC
    ";// “ordered by time” must be written in SQL 数据库的排序规则必须在sql里写清楚，不能依赖前端。

// 3. prepare SQL 预处理 SQL
    $stmt = $db -> prepare($sql);
    if (!$stmt) {
        return [];
    }

// 4. bind parameter 绑定参数
    $stmt -> bind_param("i", $auctionId);

// 5. execute query 执行查询
    $stmt -> execute();

// 6. result 拿结果
    $result = $stmt -> get_result();

// 7. put all results in one array 把所有结果放进一个数组里
// [] = array(), the same!!
    $bids = [];
    while ($row = $result -> fetch_assoc()) {
        $bids[] = $row;
    }

// 8. close statement 关闭语句
    $stmt -> close();

// 9. return array 返回数组
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
// 1. connecting to db 连接数据库
    $db = get_db_connection();

// 2. sql
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

// 3. prepare SQL 预处理 SQL
// if there is no value, then return empty array;
// []=array();
// "!" means not.
    $stmt = $db -> prepare($sql);
    if (!$stmt) {
        return [];
    }

// 4. bind parameter 绑定参数
    $stmt -> bind_param("i", $userId);

// 5. execute query 执行查询
    $stmt -> execute();

// 6. result 拿结果
    $result = $stmt -> get_result();

// 7. put all result in one array 把所有结果放进一个数组
    $bids = [];
    while ($row = $result -> fetch_assoc()) {
        $bids[] = $row; // ";" must be in the curly braces!
    }

// 8. close statement 关闭语句
    $stmt -> close();

// 9. return array 返回数组
    return $bids;
}




// ******************************************************************************************************************************
// 4. Place Bid: 
//    A user(buyer) to place a bid on an item in an auction.
//
// How it works:
// - Validate the bid price (> 0).
// - Make sure it's higher than current highest bid if there is any bid.
// - insert the bid into bids table if passed all the checks above.
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
// 1. Check the validation of the bid price 检查出价的价格
    if ($bidPrice <= 0) { 
        return [
            "success" => false,
            "message" => "Your bid amount must be greater than £0.00."
        ];
    }

// 2. Build the connection with db 连接数据库
    $db = get_db_connection();

// 3. Check auction info(existence) (sellerId & endTime & startPrice) 先去检查这场拍卖的基本信息：卖家是谁、是否已经结束、（无人出价的话）卖家所设置的起始价。
$sqlAuction = "
    SELECT sellerId, auctionStartTime, auctionEndTime, auctionStatus, startPrice
    FROM auctions
    WHERE auctionId = ?
    LIMIT 1
";
    // Order: prepare()生语句 → bind_param()绑参数 → execute()执行语句
    // Pre-check auction's existence, return message if not.提前检查下这个auction存在不，不存在就return提示。
    $stmtAuction = $db -> prepare($sqlAuction);
    if ($stmtAuction === false) {
        return [
            "success" => false,
            "message" => "Sorry, something went wrong. Please try again later."
        ];
    }

    // bind auctionId 绑定一下auctionId
    $stmtAuction -> bind_param("i", $auctionId);
    $stmtAuction -> execute();

    $resultAuction = $stmtAuction -> get_result();
    $auctionRow    = $resultAuction -> fetch_assoc();
    $stmtAuction   -> close();

    // Return a message to user if the auction does not exist at all 如果拍卖都不存在，显示错误然后返回。
    if (!$auctionRow) {
        return [
            "success" => false,
            "message" => "This auction does not exist."
        ];
    }

    // checking the auction's existence first before checking the start bid price.先检查拍卖是不是存在，再去拿起拍价
    $startPrice = isset($auctionRow["startPrice"]) 
                  ? (float)$auctionRow["startPrice"] 
                  : 0.0;

    $sellerId         = (int)$auctionRow["sellerId"];
    $auctionStartTime = $auctionRow["auctionStartTime"];
    $auctionEndTime   = $auctionRow["auctionEndTime"];
    $auctionStatus    = $auctionRow["auctionStatus"] ?? null; 
// 如果有auctionStatus，就用！如果没有status，那就用null!
// "??"是null合并运算符号：if the value on left side not exist or is null, then use the value on right side.
// 这个地方其实就是=== if (isset($auctionRow["auctionStatus"])) {$auctionStatus = $auctionRow["auctionStatus"];} 
// else {$auctionStatus = null;}
    $nowTimestamp     = time();

    // 3.1 Seller cannot bid on his/her own auction
    // 禁止卖家给自己拍卖出价！！
    if ((int)$buyerId === $sellerId) {
        return [
            "success" => false,
            "message" => "Sorry, you cannot place a bid on your own auction."
        ];
    }

    // 3.2 If the auction has been cancelled, not allowed to place bid
    // 如果拍卖已被手动取消，禁止出价
    if ($auctionStatus === 'cancelled') {
        return [
            "success" => false,
            "message" => "This auction is not available for bidding anymore."
        ];
    }

    // 3.3 Auction not started yet 拍卖未开始，不允许出价
    if (!empty($auctionStartTime)) {
        $startTimestamp = strtotime($auctionStartTime);
        if ($nowTimestamp < $startTimestamp) {
            return [
                "success" => false,
                "message" => "Sorry, this auction has not started yet, you cannot place a bid."
            ];
        }
    }

    // 3.4 Auction already ended 拍卖已结束，不允许再出价
    if (!empty($auctionEndTime)) {
        $endTimestamp = strtotime($auctionEndTime);
        if ($nowTimestamp >= $endTimestamp) {
            return [
                "success" => false,
                "message" => "Sorry, this auction has already ended, you cannot place a bid anymore."
            ];
        }
    }


// 4. Check the current highest bid price 检查当前最高出价
    $currentHighest = getHighestBidForAuction($auctionId);

    // --- First bid rule: no existing bids (首次出价规则) ---
if ($currentHighest === null) {
    if ($startPrice > 0 && $bidPrice < $startPrice) {
        return [
            "success" => false,
            "message" => "Your first bid must be at least the start price (£" . number_format($startPrice, 2) . ")."
        ];
    }
}

    // currentHighest is not null → compare new bid with highest bid
    // 如果currentHighest === null：说明目前还没有任何出价，
    // 只要bidPrice > 0就已经通过了（上面第 1 步已经检查 > 0 了），不用再写一个 if。
    if ($currentHighest !== null) {
    $currentHighestPrice = (float)$currentHighest["bidPrice"];
    $currentHighestBuyer = (int)$currentHighest["buyerId"];

    // 4.1 Already highest bidder 查看买家是否已经是最高出价者
    if ($currentHighestBuyer === (int)$buyerId) {
        return [
            "success" => false,
            "message" => "You are already the highest bidder for this auction."
        ];
    }

    // 4.2 Minimum increase for bid 最小的加价幅度
    $minIncrease = 5.00;  // 可按需要调整
    if ($bidPrice < $currentHighestPrice + $minIncrease) {
        return [
            "success" => false,
            "message" => "Please increase your bid by at least £5.00."
        ];
    }
}

    
// 5. Insert the new bid into bids table 把新的出价插入导入bids表
    $sql = "
    INSERT INTO bids (auctionId, buyerId, bidPrice, bidTime)
    VALUES (?, ?, ?, NOW())";

// 6. prepare sql 预处理sql
    $stmt = $db -> prepare($sql);
    if ($stmt === false) {
        return [
            "success" => false,
            "message" => "Sorry, something went wrong. Please try again later."
        ];
    }

// 7. bind parameters 绑定参数
// ""iid" is an arguments list, representing VALUE(?, ?, ?)"
// "iid" means:
// i:         integer
// d:         double (decimal/float)
// auctionId: int
// buyerId:   int
// bidPrice:  double
    $stmt -> bind_param("iid", $auctionId, $buyerId, $bidPrice);

// 8. execute quesry 执行查询
    $result = $stmt->execute();
    if ($result === false) {
        $stmt->close();
        return [
            "success" => false,
            "message" => "Sorry, something went wrong. Please try again later."
        ];
    }

// 9. if success, get the new bidId created by database(AUTO_INCREMENT)
// insert_id means get the lastest ID of the last insert operation, doesn't mean insert an ID etc.
    $newBidId = $db -> insert_id;
    
// 10. close statement 关闭语句 (must write close BEFORE return!)
    $stmt -> close();

// 11. return the array 返回数组
    return array(
        "success" => true,
        "message" => "Congratulations! You placed the bid successfully.",
        "bidId"   => $newBidId
    );
}




// ******************************************************************************************************************************
// 5. View Bids On My Auctions:
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
// 1. connect to db 连接数据库
$db = get_db_connection();

// 2. sql 写sql
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

WHERE a.sellerId = ?
ORDER BY 
    FIELD(a.auctionStatus, 'running', 'scheduled', 'ended', 'cancelled'),
    a.auctionStartTime DESC,
    b.bidTime DESC
"; // ORDER BY 这样做可以有“状态优先级”，和Buyer's view一致。

// 3. Prepare sql 预处理sql
// if there is no value, then return an empty array for user, avoid show error.
// [] = array().
$stmt = $db -> prepare($sql);
if ($stmt === false) {
    return array();
}

// 4. bind parameter 绑定参数
$stmt -> bind_param("i", $sellerId);

// 5. execute query 执行查询
$stmt -> execute();

// 6. result 拿到结果
$result = $stmt -> get_result();

// 7. put all results in one array 把所有结果都放进一个数组
// 记住:[] = array()
$rows = [];
while ($line = $result -> fetch_assoc()) {
    $rows[] = $line;
}

// 8. close statement 关闭语句
// always remember close BEFORE return!
$stmt -> close();

// 9. return array 返回数组
return $rows;
}
