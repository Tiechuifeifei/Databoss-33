<?php
require_once("db_connect.php");

/*
|--------------------------------------------------------------------------
| ITEM FUNCTIONS
|--------------------------------------------------------------------------
| This file contains all Item-related backend logic:
| - 1. create item 第一个
| - 2/3 fetch item 第二个，第三个（找到seller）
| - update item 第四个
| - update status (inactive, active and sold) 第五个
| - search bar 第六个
| - images related functions --- seperate file named started with Image_
| - 7/. get item status
|---------------------------------------------------------------------------
*/


// 1. Create a new item (default itemStatus = 'inactive') 新建

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

// 2. Get item details by itemId(根据itemid查看item，单个item) 这个是lisitng 拍卖详情页需要的东西，auction也需要这个来知道item的具体信息

function getItemById($itemId) {
    global $conn;

    $sql = "SELECT * FROM items WHERE itemId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}


// 3. Get all items uploaded by a seller = 查看用户上传的所有items

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


// 4. Update item details 更新

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



// 5. Update item status (inactive / active / sold / withdrawn) 和auction是呼应的状态
function updateItemStatus($itemId, $newStatus) {
    global $conn;

    $sql = "UPDATE items SET itemStatus = ? WHERE itemId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newStatus, $itemId);

    return $stmt->execute();
}

// 6. search bar
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
// 7. Get item status by itemId —— 用于判断是否允许编辑
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
