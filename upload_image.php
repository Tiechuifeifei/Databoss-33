<?php
ini_set("display_errors",1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("db_connect.php");
require_once("Image_functions.php");
require_once("Item_function.php");



// 1. 
// =======================================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mylistings.php?msg=" . urlencode("⚠ Invalid upload request"));
    exit;
}



// 2.
// =======================================================================
$itemId = $_POST['itemId'] ?? null;

if (!$itemId) {
    exit_redirect("Invalid item selected.", null);
}

$item = getItemById($itemId);
if (!$item) {
    exit_redirect("Item not found.", null);
}


// 3.
// =======================================================================
if (!isset($_SESSION['userId']) || $_SESSION['userId'] != $item['sellerId']) {
    die("Unauthorized.");
}

if ($item['itemStatus'] !== 'inactive') {
    die("Only inactive items can be edited.");
}



// 4.
// =======================================================================
if (
    !isset($_FILES['itemImages']) ||
    empty($_FILES['itemImages']['name']) ||
    $_FILES['itemImages']['name'][0] === ''
) {
    exit_redirect("Please choose at least one file to upload.", $itemId);
}



// 5.
// =======================================================================
$allowed = ['jpg','jpeg','png','gif','webp','heic','heif'];

$existingImgs = getImagesByItemId($itemId);
$existing = count($existingImgs);

$files = $_FILES['itemImages']['name'];

if ($existing >= 3) {
    exit_redirect("You already uploaded the maximum of 3 images.", $itemId);
}

$uploadCount = count($files);

if ($existing + $uploadCount > 3) {
    $left = 3 - $existing;
    exit_redirect("You can only upload $left more image(s).", $itemId);
}


// 6.
// =======================================================================
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}



// 7.
// =======================================================================
$ok = 0;
$badExt = 0;
$failMove = 0;

for ($i = 0; $i < count($files); $i++) {

    $err = $_FILES['itemImages']['error'][$i];
    if ($err !== UPLOAD_ERR_OK) {
        continue;
    }

    $origName = $files[$i];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $badExt++;
        continue;
    }

    $newName = "item_{$itemId}_" . time() . "_" . rand(1000,9999) . "." . $ext;
    $path = $uploadDir . $newName;
    
    if (!move_uploaded_file($_FILES['itemImages']['tmp_name'][$i], $path)) {
        $failMove++;
        continue;
    }

    uploadImage($itemId, $path, 0);
    $ok++;
}



// 8. 
// =======================================================================
$msg = "";

if ($ok > 0) {
    $msg .= "$ok image(s) uploaded successfully. ";
}
if ($badExt > 0) {
    $msg .= "$badExt invalid file(s) skipped (wrong format). ";
}
if ($failMove > 0) {
    $msg .= "$failMove file(s) failed to save to server. ";
}

if ($ok === 0) {
    $msg = "No valid images uploaded.";
}

exit_redirect($msg, $itemId);



// Helper
// =======================================================================

function exit_redirect($msg, $itemId) {
    if ($itemId) {
        header("Location: edit_item.php?itemId=$itemId&msg=" . urlencode($msg));
    } else {
        header("Location: mylistings.php?msg=" . urlencode($msg));
    }
    exit;
}

