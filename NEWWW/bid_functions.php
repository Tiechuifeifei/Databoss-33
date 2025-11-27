<?php
require_once 'utilities.php';

//bids_functions.php:
//--------------------------------------------------------------------------------------------------------------------------------------------
// 5 Core Functions:
// 1. Get Highest Bid For Auction
//    Returns the highest bid for a specific auction (or null if there are no bids).
// 2. Get Bids By Auction
//    "Auction's View": Users can view all the bids for an auction.
// 3. Get Bids By User
//    "Buyer's View": Basically "my bids" function, buyers can view all bids
//    placed by themselves, with related item & auction info.
// 4. View Bids On My Auctions
//    "Seller's View": Sellers can view all bids placed on his/her auctions.
// 5. Place Bid
//    Allows a buyer to place a bid on an auction item, with multiple validation checks.
// -----------------------------------------------------------------------------
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
//    Users cannot place a bid that is less than or equal to £0.00
//    (also checks non-numeric values).
//
// 7. Basic anti-spam rule:
//    The current highest bidder cannot place another bid on the same auction.
// -----------------------------------------------------------------------------

// 1. Get Highest Bid For Auction:
//    Returns the highest bid for a specific auction (or null if there are no bids).
function getHighestBidForAuction($auctionId)
{
    // build the connection with database
    $db = get_db_connection();

    $sql = "
        SELECT *
        FROM bids AS b
        WHERE b.auctionId = ?
        ORDER BY b.bidPrice DESC, b.bidTime ASC
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // not necessary, but better to have it; also, close MUST happen before the return!!
    $stmt->close();

    // 没有出价的话，就返回 null 值。
    if (!$row) {
        return null;
    }
    // 不空的话，就返回数组。
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
    "; // "ordered by time" must be written in SQL, better not to rely on front-end.

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $auctionId);
    $stmt->execute();
    $result = $stmt->get_result();

    $bids = [];
    while ($row = $result->fetch_assoc()) {
        $bids[] = $row;
    }

    $stmt->close();
    return $bids;
}

// 3. Get Bids By User:
//    "Buyer's View" - basically "all my bids", a user(buyer) to check all his/her bids.
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
        ORDER BY FIELD(a.auctionStatus, 'running', 'scheduled', 'ended', 'cancelled'),
                 b.bidTime DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $bids = [];
    while ($row = $result->fetch_assoc()) {
        $bids[] = $row; // ";" must be in the curly braces!
    }

    $stmt->close();
    return $bids;
}

// 4. View Bids On My Auctions:
//    "Seller's View" - a user(seller) can view all bids that are placed on his/her auctions.
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

            u.userName AS buyerName

        FROM auctions AS a 
        JOIN items   AS i ON a.itemId     = i.itemId
        LEFT JOIN bids  AS b ON a.auctionId = b.auctionId
        LEFT JOIN users AS u ON b.buyerId   = u.userId

        WHERE i.sellerId = ?
        ORDER BY 
            FIELD(a.auctionStatus, 'running', 'scheduled', 'ended', 'cancelled'),
            a.auctionStartTime DESC,
            b.bidTime DESC
    "; // ORDER BY 这样做可以有“状态优先级”，和 Buyer's view 一致。

    // if there is no value, then return an empty array for user, avoid show error.
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $sellerId);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($line = $result->fetch_assoc()) {
        $rows[] = $line;
    }

    // always remember close BEFORE return
    $stmt->close();

    // return array (maybe empty)
    return $rows;
}

// 5. Place Bid:
//    A user(buyer) places a bid on an item in an auction.
//
// How it works:
// 1) Validate bid price (> 0 and numeric).
// 2) Load auction info and check existence / seller / timing / status.
// 3) Apply bidding rules:
//    - First bid must be >= start price.
//    - Current highest bidder cannot bid again.
//    - New bid must be at least £5.00 higher than the current highest bid.
//    - Seller cannot bid on own auction.
// 4) Insert the bid if all checks pass and return a result array.
function placeBid($buyerId, $auctionId, $bidPrice)
{
    // 1) Validate bid price before everything
    if (!is_numeric($bidPrice)) {
        return [
            "success" => false,
            "message" => "Sorry, your bid amount must be a valid number."
        ];
    }

    // cast to float to avoid string comparison issues
    $bidPrice = (float)$bidPrice;

    if ($bidPrice <= 0) {
        return [
            "success" => false,
            "message" => "Sorry, your bid amount must be higher than £0.00."
        ];
    }

    $db = get_db_connection();

    // 2) Check auction's status, like: existence / start price / status etc.
    $sqlAuction = "
        SELECT 
            i.sellerId,
            a.auctionStartTime,
            a.auctionEndTime,
            a.auctionStatus,
            a.startPrice
        FROM auctions a
        JOIN items   i ON a.itemId = i.itemId
        WHERE a.auctionId = ?
        LIMIT 1
    ";

    $stmtAuction = $db->prepare($sqlAuction);
    $stmtAuction->bind_param("i", $auctionId);
    $stmtAuction->execute();
    $resultAuction = $stmtAuction->get_result();
    $auctionRow    = $resultAuction->fetch_assoc();
    $stmtAuction->close();

    // (2.1) Auction must exist, otherwise return reminder.
    if (!$auctionRow) {
        return [
            "success" => false,
            "message" => "Sorry, this auction does not exist."
        ];
    }

    // Check the startPrice to avoid error.
    $startPrice = isset($auctionRow["startPrice"])
        ? (float)$auctionRow["startPrice"]
        : 0.0;

    // SellerId for self-bidding validation later.
    $sellerId = (int)$auctionRow["sellerId"];

    // get the time for time-based auction checks later.
    $auctionStartTime = $auctionRow["auctionStartTime"];
    $auctionEndTime   = $auctionRow["auctionEndTime"];

    // Use null if auctionStatus is missing or null.
    // "??" is the null coalescing operator: if the value on the left side does not exist
    // or is null, then use the value on the right side.
    $auctionStatus = $auctionRow["auctionStatus"] ?? null;

    // get the current time for time-validation later.
    $nowTimestamp = time();

    // (2.2) Seller cannot bid on his/her own auction.
    if ((int)$buyerId === $sellerId) {
        return [
            "success" => false,
            "message" => "Sorry, you cannot place a bid on your own auction."
        ];
    }

    // (2.3) If auction has been cancelled, return reminder.
    if ($auctionStatus === 'cancelled') {
        return [
            "success" => false,
            "message" => "Sorry, this auction is not available for bidding anymore."
        ];
    }

    // (2.4) Cannot bid if auction not started yet.
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

    // 3) Get the current highest bid for validations later.
    $currentHighest = getHighestBidForAuction($auctionId);

    // (3.1) First-time bid must be >= start price (if startPrice > 0).
    if ($currentHighest === null) {
        if ($startPrice > 0 && $bidPrice < $startPrice) {
            return [
                "success" => false,
                "message" => "Sorry, you are placing the first bid for this auction; it must be at least the start price (£" . number_format($startPrice, 2) . ")."
            ];
        }
    }

    // If not (3.1), we can compare new bid with current highest bid.
    if ($currentHighest !== null) {
        $currentHighestPrice = (float)$currentHighest["bidPrice"];
        $currentHighestBuyer = (int)$currentHighest["buyerId"];

        // (3.2) Current highest bidder cannot bid again.
        if ($currentHighestBuyer === (int)$buyerId) {
            return [
                "success" => false,
                "message" => "Sorry, you are already the highest bidder for this auction and cannot place another bid on the same auction."
            ];
        }

        // (3.3) Minimum increase £5 rule.
        $minIncrease = 5.00;  // can be a different number
        if ($bidPrice < $currentHighestPrice + $minIncrease) {
            return [
                "success" => false,
                "message" => "Sorry, your bid must be at least £5 higher than the current highest bid price."
            ];
        }
    }

    // 4) Insert the new bid into bids table.
    $sql = "
        INSERT INTO bids (auctionId, buyerId, bidPrice, bidTime)
        VALUES (?, ?, ?, NOW())
    ";

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        return [
            "success" => false,
            "message" => "Sorry, something went wrong while preparing your bid. Please try again later."
        ];
    }

    // "iid" is an arguments list, representing VALUE(?, ?, ?)
    $stmt->bind_param("iid", $auctionId, $buyerId, $bidPrice);
    $result = $stmt->execute();

    if ($result === false) {
        $stmt->close();
        return [
            "success" => false,
            "message" => "Sorry, something went wrong while saving your bid. Please try again later."
        ];
    }

    // if success, get the new bidId created by database (AUTO_INCREMENT)
    // insert_id means the ID of the last successful INSERT operation.
    $newBidId = $db->insert_id;

    $stmt->close();

    return [
        "success" => true,
        "message" => "Congratulations! You placed the bid successfully.",
        "bidId"   => $newBidId
    ];
}
