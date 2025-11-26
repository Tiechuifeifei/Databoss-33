<?php
require_once("db_connect.php");   

/*
|--------------------------------------------------------------------------
| Image FUNCTIONS
|--------------------------------------------------------------------------
| This file contains all Image-related backend logic:
| - 1. upload image 第一个
| - 2.1 getimage 
| - 2.2 getimageid 
| - 3. set primary image
| - 4. delete image 
| - 5. update image 
| - 6. getPrimaryImage($itemId) ---- 这个listing的时候要用！！
| - 7. count images 限制最多3张（max 3 pics)
|---------------------------------------------------------------------------
*/


//1: upload image

function uploadImage($itemId, $imageUrl, $isPrimary = 0) {
    global $conn;

    // Step 0: 限制最大 3 张
    $currentCount = countImagesByItemId($itemId);
    if ($currentCount >= 3) {
        return "MAX_LIMIT_REACHED";   // 标记超过上限
    }

    // Step 1: 正常插入
    $sql = "INSERT INTO images (itemId, imageUrl, isPrimary)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $itemId, $imageUrl, $isPrimary);

    if ($stmt->execute()) {
        return $conn->insert_id;
    } else {
        return false;
    }
}


//2.1 get image
function getImagesByItemId($itemId) {
    global $conn;

    $sql = "SELECT imageId, imageUrl, isPrimary
            FROM images
            WHERE itemId = ?
            ORDER BY isPrimary DESC, imageId ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// 2.2 getimageid 

function getImageById($imageId) {
    global $conn;

    $sql = "SELECT * FROM images WHERE imageId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $imageId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}


// 3. set primary image
function setPrimaryImage($imageId, $itemId) {
    global $conn;

    // 先把原本的主图设置为 0
    $sql1 = "UPDATE images SET isPrimary = 0 WHERE itemId = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $itemId);
    $stmt1->execute();

    // 给新的图片设置主图
    $sql2 = "UPDATE images SET isPrimary = 1 WHERE imageId = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $imageId);
    
    return $stmt2->execute();
}

// 4. delete image 
function deleteImage($imageId) {
    global $conn;

    $sql = "DELETE FROM images WHERE imageId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $imageId);

    return $stmt->execute();
}

// 5. change photo

function updateImageUrl($imageId, $newUrl) {
    global $conn;

    $sql = "UPDATE images SET imageUrl = ? WHERE imageId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newUrl, $imageId);

    return $stmt->execute();
}

// 6. getPrimaryImage($itemId)
function getPrimaryImage($itemId) {
    global $conn;

    $sql = "SELECT imageUrl FROM images WHERE itemId = ? AND isPrimary = 1 LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

// 7. count image 
function countImagesByItemId($itemId) {
    global $conn;

    $sql = "SELECT COUNT(*) AS total FROM images WHERE itemId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc()['total'];
}


?>