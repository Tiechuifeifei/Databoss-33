<?php
require_once("db_connect.php");   

//function1: upload image

function uploadImage($itemId, $imageUrl, $isPrimary = 0) {
    global $conn;

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

//function2: get image
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

// set primary image
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

// delete image 
function deleteImage($imageId) {
    global $conn;

    $sql = "DELETE FROM images WHERE imageId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $imageId);

    return $stmt->execute();
}

?>