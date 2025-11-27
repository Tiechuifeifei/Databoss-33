<?php
require_once("db_connect.php");
require_once("image_functions.php");

$itemId = intval($_GET['itemId']);
$imageId = intval($_GET['imageId']);

deleteImage($imageId);

header("Location: edit_item.php?itemId=$itemId");
exit;