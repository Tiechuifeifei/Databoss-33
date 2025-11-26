<?php
require_once("db_connect.php");
require_once("image_functions.php");

$imageId = $_GET['imageId'];
$itemId = $_GET['itemId'];

if (!$imageId || !$itemId) {
    die("Missing imageId or itemId");
}

// 调用已经写好的 function 在image_functions.php里面
setPrimaryImage($imageId, $itemId);

echo "<h3>Primary image updated!</h3>";
echo "<a href='listing_item_test.php?itemId=$itemId'>Back to item</a>";
