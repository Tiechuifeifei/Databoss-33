<?php
require_once 'utilities.php';
//***********************************************************************************************************************
// bids_functions.php:
//***********************************************************************************************************************
// 5 Core Functions:
//
//  1. Get Highest Bid For Auction
//     Returns the highest bid for a specific auction (or null if there are no bids).
//
//  2. Get Bids By Auction
//     "Auction's View": Users can view all bids placed on a specific auction.
//
//  3. Get Bids By User
//     "Buyer's View": Shows all bids placed by a buyer, including item & auction info.
//
//  4. View Bids On My Auctions
//     "Seller's View": Sellers can view all bids placed on their auctions.
//
//  5. Place Bid
//     Allows a logged-in buyer to place a bid on an auction item,
//     with multiple validation checks (see list below).
//***********************************************************************************************************************
// 9 Validations:
//
//  1. Login check (must be logged in):
//     Only logged-in users can place bids. This is the second-layer protection
//     in addition to place_bid.php.
//
//  2. Auction existence check:
//     Users cannot bid if the auction does not exist.
//
//  3. Prevent self-bidding:
//     Sellers cannot place bids on their own auctions.
//
//  4. Auction status checks:
//     Users cannot bid on cancelled, scheduled, or already-ended auctions.
//
//  5. Minimum bid requirement (no existing bids):
//     The first bid must be strictly higher than the starting price set by the seller.
//
//  6. Minimum bid requirement (with existing bids):
//     Any new bid must be strictly higher than the current highest bid.
//
//  7. Basic input validation:
//     Users cannot place a bid≤£0.00 (this also checks non-numeric input).
//
//  8. Basic anti-spam rule:
//     The current highest bidder cannot place another bid on the same auction.
//
//  9. Soft reminder (non-blocking):
//     Please double-check your bid amount and confirm carefully.
//     Bids cannot be cancelled or withdrawn.
//***********************************************************************************************************************



    
// 1. Get Highest Bid For Auction: 
//    Returns the highest bid for a specific auction (or null if there is no bids)

function getHighestBidForAuction($auctionId)
{
$db = get_db_connection();

$sql = "
  SELECT
  b.bidId,
  b.buyerId,
  b.auctionId,
  b.bidPrice,
  b.bidTime,
  u.userName as buyerName

  FROM bids AS b
  JOIN users AS u on b.buyerId = u.userId

  WHERE b.auctionId = ?
  ORDER BY b.bidPrice DESC, b.bidTime ASC
  LIMIT 1
";

    $stmt = $db -> prepare($sql);
   
    $stmt -> bind_param("i", $auctionId);
    $stmt -> execute();
    $result = $stmt -> get_result();
    $row = $result -> fetch_assoc();
    $stmt -> close();

    if (!$row) {
        return null;
    }
    return $row;
}


    
    
// 2. Get Bids By Auction: 
// "Auction's View" - to check all bid/bids in an auction.
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
    ";

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
            i.sellerId,
            u.userName AS sellerName,
            a.auctionId,
            a.auctionStartTime,
            a.auctionEndTime,
            a.auctionStatus,
            a.winningBidId,
            a.startPrice
        FROM bids AS b
        JOIN auctions AS a ON b.auctionId = a.auctionId
        JOIN items   AS i ON a.itemId     = i.itemId
        JOIN users   AS u ON i.sellerId   = u.userId
        WHERE b.buyerId = ?
        ORDER BY FIELD(a.auctionStatus, 'running', 'scheduled', 'ended', 'cancelled'),
                 b.bidTime DESC
    ";


    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $bids = [];
    while ($row = $result->fetch_assoc()) {
        $bids[] = $row;
    }

    $stmt->close();
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
a.winningBidId,

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
";
$stmt = $db -> prepare($sql);
    
$stmt -> bind_param("i", $sellerId);
$stmt -> execute();
$result = $stmt -> get_result();
$rows = [];
while ($line = $result -> fetch_assoc()) {
    $rows[] = $line;
}
$stmt -> close();

return $rows;
}




// 5. Place Bid: 
//    A user(buyer) to place a bid on an item in an auction.
// How it works:
// 1) Validate login status (buyer must be logged in).
// 2) Validate bid price (> 0).
// 3) Load auction info and check existence / seller / timing / status.
// 4) Apply bidding rules:
//    - First bid must be > start price.
//    - Current highest bidder cannot bid again.
//    - New bid must be > current highest bid.
//    - Seller cannot bid on own auction.
// 5) Insert the bid if all checks pass and return a result array.

function placeBid($buyerId, $auctionId, $bidPrice)
{
    //0) users must register and logedin first
    if (empty($buyerId)) {
        return [
            "success" => false,
            "message" => "Sorry, you are not user yet, you must be logged in to place a bid."
        ];
    }

    // 1)Validate bid price>0 before everything
    if ($bidPrice <= 0) { 
        return [
            "success" => false,
            "message" => "Sorry, your bid amount must be higher than £0.00."
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
$auctionRow = $resultAuction -> fetch_assoc();
$stmtAuction -> close();

    // (2.1)Auction must exist, otherwise return reminder.
    if (!$auctionRow) {
        return [
            "success" => false,
            "message" => "Sorry, this auction does not exist."
        ];
    }
        // Check the if there is a startPrice, avoid error
    $startPrice = isset($auctionRow["startPrice"]) 
                  ? (float)$auctionRow["startPrice"] 
                  : 0.0;
        // SellerId for self-bidding validation later
    $sellerId = (int)$auctionRow["sellerId"];
        // get the time for time-based auction check later.
    $auctionStartTime = $auctionRow["auctionStartTime"];
    $auctionEndTime = $auctionRow["auctionEndTime"];
    $auctionStatus = $auctionRow["auctionStatus"] ?? null; 
        // get the current time for time-validation later.
    $nowTimestamp     = time();

    // (2.2) Seller cannot bid on his/her own auction, return a reminder
    if ((int)$buyerId === $sellerId) {
        return [
            "success" => false,
            "message" => "Sorry, you cannot place a bid on your own auction."
        ];
    }
    // (2.3) if auction has been cancelled, return reminder
    if ($auctionStatus === 'cancelled') {
        return [
            "success" => false,
            "message" => "Sorry, this auction is not available for bidding anymore."
        ];
    }
    // (2.4) cannot bid if auction not started yet then return reminder
    if (!empty($auctionStartTime)) {
        $startTimestamp = strtotime($auctionStartTime);
        if ($nowTimestamp < $startTimestamp) {
            return [
                "success" => false,
                "message" => "Sorry, this auction has not started yet, you cannot place a bid."
            ];
        }
    }
    // (2.5) Auction already ended, return reminder.
    if (!empty($auctionEndTime)) {
        $endTimestamp = strtotime($auctionEndTime);
        if ($nowTimestamp >= $endTimestamp) {
            return [
                "success" => false,
                "message" => "Sorry, this auction has already ended, you cannot place a bid anymore."
            ];
        }
    }


// 3)Get the Current highest bid for bid validations later. 
$currentHighest = getHighestBidForAuction($auctionId);

// (3.1)first bid must higher than starting price if there is no existing bids

if ($currentHighest === null) {

    if ($startPrice > 0 && $bidPrice <= $startPrice) {
        return [
            "success" => false,
            "message" => "Sorry, your bid must be higher than the starting price (£" . number_format($startPrice, 2) . ")."
        ];
    }

} else {


    $currentHighestPrice = (float)$currentHighest["bidPrice"];
    $currentHighestBuyer = (int)$currentHighest["buyerId"];

    // (3.2)highest bidder can’t bid again,avoid double-bidding or spam.
    if ($currentHighestBuyer === (int)$buyerId) {
        return [
            "success" => false,
            "message" => "Sorry, you are already the highest bidder for this auction, no need to bid again."
        ];
    }

    // (3.3) bid must be higher than the current highest bid, if there is/are bid/bids already.
    if ($bidPrice <= $currentHighestPrice) {
        return [
            "success" => false,
            "message" => "Sorry, your bid must be higher than the current highest bid (£" . number_format($currentHighestPrice, 2) . ")."
        ];
    }
}
    $sql = "
    INSERT INTO bids (auctionId, buyerId, bidPrice, bidTime)
    VALUES (?, ?, ?, NOW())";


    $stmt = $db -> prepare($sql);

    $stmt -> bind_param("iid", $auctionId, $buyerId, $bidPrice);
    $result = $stmt->execute();

    $newBidId = $db -> insert_id;
    
    $stmt -> close();
    return array(
        "success" => true,
        "message" => "Congratulations! You placed the bid successfully.",
        "bidId"   => $newBidId
    );
}
