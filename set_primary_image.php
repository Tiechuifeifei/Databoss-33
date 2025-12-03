<?php
require_once("db_connect.php");
require_once("image_functions.php");

$imageId = $_GET['imageId'] ?? null;
$itemId  = $_GET['itemId'] ?? null;

if (!$imageId || !$itemId) {
    die("Missing imageId or itemId");
}


setPrimaryImage($imageId, $itemId);

header("Location: edit_item.php?itemId=".$itemId);
exit;
