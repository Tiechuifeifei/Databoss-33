<?php
require_once("db_connect.php");
require_once("image_functions.php");

$imageId = $_GET['imageId'] ?? null;
$itemId  = $_GET['itemId'] ?? null;

if (!$imageId || !$itemId) {
    die("Missing imageId or itemId");
}

// 调用函数设置主图
setPrimaryImage($imageId, $itemId);

header("Location: edit_item.php?itemId=".$itemId);
exit;
