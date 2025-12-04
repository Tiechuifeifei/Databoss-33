<?php
require_once("db_connect.php");

/*
|--------------------------------------------------------------------------
| ITEM FUNCTIONS
|--------------------------------------------------------------------------
| This file contains all Item-related backend logic:
| - 1. create item
| - 2/3 fetch item
| - update item
| - update status (inactive, active and sold)
| - search bar
| - images related functions --- seperate file named started with Image_
| - 7/. get item status
|---------------------------------------------------------------------------
*/


// 1. Create a new item (default itemStatus = 'inactive')

function createItem($itemName, $itemDescription, $sellerId, $categoryId, $itemCondition) {
    global $conn;

    $sql = "
        INSERT INTO items
        (itemName, itemDescription, sellerId, categoryId, itemUploadTime, itemStatus, itemCondition)
        VALUES (?, ?, ?, ?, NOW(), 'inactive', ?)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiis", $itemName, $itemDescription, $sellerId, $categoryId, $itemCondition);

    if ($stmt->execute()) {
        return $conn->insert_id;   // return itemId
    } else {
        return false;
    }
}

// 2. Get item details by itemId

function getItemById($itemId) {
    global $conn;

    $sql = "SELECT * FROM items WHERE itemId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}


// 3. Get all items uploaded by a seller

function getItemsBySeller($sellerId) {
    global $conn;

    $sql = "
        SELECT *
        FROM items
        WHERE sellerId = ?
        ORDER BY itemUploadTime DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sellerId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// 4. Update item details

function updateItem($itemId, $itemName, $itemDescription, $categoryId, $itemCondition) {
    global $conn;

    $sql = "
        UPDATE items
        SET itemName = ?, itemDescription = ?, categoryId = ?, itemCondition = ?
        WHERE itemId = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisi", $itemName, $itemDescription, $categoryId, $itemCondition, $itemId);

    return $stmt->execute();
}



// 5. Update item status (inactive / active / sold / withdrawn)
function updateItemStatus($itemId, $newStatus) {
    global $conn;

    $sql = "UPDATE items SET itemStatus = ? WHERE itemId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newStatus, $itemId);

    return $stmt->execute();
}

// 6.search bar
function searchItems($keyword) {
    global $conn;

    $keyword = "%" . $keyword . "%";

    $sql = "
        SELECT *
        FROM items
        WHERE itemName LIKE ?
           OR itemDescription LIKE ?
           OR itemCondition LIKE ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $keyword, $keyword, $keyword);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

//7. get item
function getItemStatus($itemId) {
    global $conn;

    $sql = "SELECT itemStatus FROM items WHERE itemId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['itemStatus'] ?? null;
}

?>
