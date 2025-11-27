<?php
require_once 'utilities.php';

//bids_functions.php:

// 5 Core Functions:
// 1. Get Highest Bid For Auction
//    Returns the highest bid for a specific auction (or null if there is no bids)
// 2. Get Bids By Auction
//    "Auction's View": Users can view all the bids for an auction.
// 3. Get Bids By User
//    "Buyer's View": Basically "my bids" function, buyers can view all bids placed by themsleves, with related item & auction info.
// 4. View Bids On My Auctions
//    "Seller's View": Sellers can view all bids placed on his/her auctions.
// 5. Place Bid
//    Allows a buyer to place a bid on an auction item, but with multiple validation checks.
//------------------------------------------------------------------------------------------------------
// 7 Validations:
//
// 1. Auction existence check:
//    Users cannot bid if the auction does not exist.
//
// 2. Prevent self-bidding:
//    Sellers cannot place bids on their own auctions.
//
// 3. Auction status checks:
//    Users cannot place a bid on cancelled, scheduled, or already-ended auctions.
//
// 4. Minimum bid requirement (no existing bids):
//    The first bid must not be less than the start price set by the seller.
//
// 5. Minimum bid requirement (with existing bids):
//    Any new bid must be at least £5.00 higher than the current highest bid.
//
// 6. Basic input validation:
//    Users cannot place a bid that is less than or equal to £0.00 (will also check the non-numeric values too)
//
// 7. Basic anti-spam rule:
//    The current highest bidder cannot place another bid on the same auction.
//-------------------------------------------------------------------------------------------------------------------------------

    
// 1. Get Highest Bid For Auction: 
//    Returns the highest bid for a specific auction (or null if there is no bids)

function getHighestBidForAuction($auctionId)
{
    $db = get_db_connection();

    $sql = "
        SELECT 
            b.bidId,
            b.bidPrice,
            b.buyerId,
            u.userName,
            u.userEmail
        FROM bids AS b
        JOIN users AS u ON b.buyerId = u.userId
        WHERE b.auctionId = ?
        ORDER BY b.bidPrice DESC, b.bidTime ASC
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    if (!$row) {
        return null;
    }
    return $row;
}


    
    
// 2. Get Bids By Auction: 
//    "Auction's View" - to check all bid/bids in an auction.
function getBidsByAuctionId($auctionId)
{

    $db = get_db_connection();
    $sql = "
        SELECT 
            b.*,
            u.userName AS buyerName
        FROM bids AS b
        JOIN users AS u ON b.buyerId = u.userId
        WHERE b.auctionId = ?
        ORDER BY b.bidPrice DESC, b.bidTime ASC
    ";// "ordered by time" must be written in sql, better not to rely on front side.

    $stmt = $db -> prepare($sql);

    $stmt -> bind_param("i", $auctionId);
    $stmt -> execute();
    $result = $stmt -> get_result();

    $bids = [];
    while ($row = $result -> fetch_assoc()) {
        $bids[] = $row;
    }
    $stmt -> close();
    return $bids;
}



// 3. Get Bids By User: 
//    "Buyer's View" - basically "all my bids", a user(buyer) to check all his/her bid/bids.

function getBidsByUser($userId)
{
    $db = get_db_connection();
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

    $stmt = $db -> prepare($sql);
    
    $stmt -> bind_param("i", $userId);
    $stmt -> execute();
    $result = $stmt -> get_result();

    $bids = [];
    while ($row = $result -> fetch_assoc()) {
        $bids[] = $row; // ";" must be in the curly braces!
    }

    $stmt -> close();
    return $bids;
}




// 4. View Bids On My Auctions:
//    "Seller's View" - a user(seller) can view all bids that are placed on his/her auction/auctions.  

function viewBidsOnMyAuctions($sellerId)
{
    
$db = get_db_connection();
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

// if there is no value, then return an empty array for user, avoid show error.
$stmt = $db -> prepare($sql);
    
$stmt -> bind_param("i", $sellerId);
$stmt -> execute();
$result = $stmt -> get_result();
$rows = [];
while ($line = $result -> fetch_assoc()) {
    $rows[] = $line;
}
    //always remember close BEFORE return
$stmt -> close();

// 9) return array 返回数组 (maybe empty)
return $rows;
}




// 5. Place Bid: 
//    A user(buyer) to place a bid on an item in an auction.

// How it works:
// 1) Validate bid price (> 0). (1)
// 2) Load auction info and check existence / seller / timing / status. (3.1, 3.3, 3.4, 3.5)
// 3) Apply bidding rules:
//    - First bid must be >= start price.(4.1)
//    - Current highest bidder cannot bid again. (4.2)
//    -New bid must be at least £5.00 higher than current highest bid. (4.3)
//    - Seller cannot bid on own auction. (3.2)
// 4) Insert the bid if all checks pass and return a result array.

function placeBid($buyerId, $auctionId, $bidPrice)
{
// 1)Validate bid price>0 before everything
    if ($bidPrice <= 0) { 
        return [
            "success" => false,
            "message" => "Sorry,your bid amount must be higher than £0.00."
        ];
    }
    $db = get_db_connection();

// 2)Check auction's status, like:exsistence/start price/status etc.
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

$stmtAuction -> bind_param("i", $auctionId);
$stmtAuction -> execute();
$resultAuction = $stmtAuction -> get_result();
$auctionRow    = $resultAuction -> fetch_assoc();
$stmtAuction   -> close();

    // (2.1)Auction must exist, otherwise return reminder. 拍卖必须要存在，否则提示不存在然后返回。
    if (!$auctionRow) {
        return [
            "success" => false,
            "message" => "Sorry, this auction does not exist."
        ];
    }
        // Check the if there is a startPrice, avoid error. 检查起拍价格是不是存在，避免报错。
    $startPrice = isset($auctionRow["startPrice"]) 
                  ? (float)$auctionRow["startPrice"] 
                  : 0.0;
        // SellerId for self-bidding validation later. 拿到seller的ID，为后面身份检查做准备。
    $sellerId         = (int)$auctionRow["sellerId"];
        // get the time for time-based auction check later 保留开始和结束时间，一会判断拍卖的“未开始”和“已结束”。
    $auctionStartTime = $auctionRow["auctionStartTime"];
    $auctionEndTime   = $auctionRow["auctionEndTime"];
        // Use null if auctionStatus is missing or null. 如果有auctionStatus，就用！如果没有status，那就用null!
        // "??"是null合并运算符号：if the value on left side not exist or is null, then use the value on right side.
        // 这样写其实等同于if (isset($auctionRow["auctionStatus"])) {$auctionStatus = $auctionRow["auctionStatus"];} else {$auctionStatus = null;}
    $auctionStatus    = $auctionRow["auctionStatus"] ?? null; 
        // get the current time for time-validation later.
    $nowTimestamp     = time();

    // (2.2) Seller cannot bid on his/her own auction, return a reminder禁止卖家给自己的拍卖出价
    if ((int)$buyerId === $sellerId) {
        return [
            "success" => false,
            "message" => "Sorry, you cannot place a bid on your own auction."
        ];
    }
    // (2.3) if auction has been cancelled, return reminder 如果拍卖已被手动取消，禁止任何人再出价
    if ($auctionStatus === 'cancelled') {
        return [
            "success" => false,
            "message" => "Sorry, this auction is not available for bidding anymore."
        ];
    }
    // (2.4) cannot bid if auction not started yet then return reminder拍卖如果显示的schedule，也不能出价
    if (!empty($auctionStartTime)) {
        $startTimestamp = strtotime($auctionStartTime);
        if ($nowTimestamp < $startTimestamp) {
            return [
                "success" => false,
                "message" => "Sorry, this auction has not started yet, you cannot place a bid."
            ];
        }
    }
    // (2.5) Auction already ended, return reminder. 拍卖已结束，不允许再出价
    if (!empty($auctionEndTime)) {
        $endTimestamp = strtotime($auctionEndTime);
        if ($nowTimestamp >= $endTimestamp) {
            return [
                "success" => false,
                "message" => "Sorry, this auction has already ended, you cannot place a bid anymore."
            ];
        }
    }
// 3) Get the Current highest bid for bid validations later. 
    $currentHighest = getHighestBidForAuction($auctionId);
    // (3.1) First time bid must higher than the start price & check start price>0 btw, otherwise a reminder.首次出价必须高于卖家设置的起拍价
if ($currentHighest === null) {
    if ($startPrice > 0 && $bidPrice < $startPrice) {
        return [
            "success" => false,
            "message" => "Sorry, you are placing the first bid for this auction, it must be at least the start price (£" . number_format($startPrice, 2) . ")."
        ];
    }
}
// if not 3.1,can compare new bid with CHB
    if ($currentHighest !== null) {
    $currentHighestPrice = (float)$currentHighest["bidPrice"];
    $currentHighestBuyer = (int)$currentHighest["buyerId"];
// (3.2) Current highest biddER cannot bid again, otherwise return reminder. 当前最高出价者不能再出价
    if ($currentHighestBuyer === (int)$buyerId) {
        return [
            "success" => false,
            "message" => "Sorry, you are already the highest bidder for this auction, and cannot place another bid on the same auction."
        ];
    }
    // (3.3) Minimum increase £5 rule for bid otherwise meaningless最小的加价至少要5镑
    $minIncrease = 5.00;  // can be a difference number
    if ($bidPrice < $currentHighestPrice + $minIncrease) {
        return [
            "success" => false,
            "message" => "Sorry, your bid must be at leaset £5 higher than the current highest bid price."
        ];
    }
}
//Insert the new bid into bids table 把新的出价插入导入bids表
    $sql = "
    INSERT INTO bids (auctionId, buyerId, bidPrice, bidTime)
    VALUES (?, ?, ?, NOW())";


    $stmt = $db -> prepare($sql);
// "iid" is an arguments list, representing VALUE(?, ?, ?)"
    $stmt -> bind_param("iid", $auctionId, $buyerId, $bidPrice);
    $result = $stmt->execute();

//if success, get the new bidId created by database(AUTO_INCREMENT)
// REMEMBER insert_id means get the lastest ID of the last insert operation, doesn't mean insert an ID etc.
    $newBidId = $db -> insert_id;
    
    $stmt -> close();
    return array(
        "success" => true,
        "message" => "Congratulations! You placed the bid successfully.",
        "bidId"   => $newBidId
    );
}
