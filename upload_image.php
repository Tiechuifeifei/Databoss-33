<?php
// =======================================================================
// 必须放最顶部
// =======================================================================
ini_set("display_errors",1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("db_connect.php");
require_once("Image_functions.php");
require_once("Item_function.php");



// =======================================================================
// 1. 只能 POST 访问
// =======================================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mylistings.php?msg=" . urlencode("⚠ Invalid upload request"));
    exit;
}



// =======================================================================
// 2. 获取 itemId
// =======================================================================
$itemId = $_POST['itemId'] ?? null;

if (!$itemId) {
    exit_redirect("Invalid item selected.", null);
}

$item = getItemById($itemId);
if (!$item) {
    exit_redirect("Item not found.", null);
}



// =======================================================================
// 3. 权限检查 + item 状态
// =======================================================================
if (!isset($_SESSION['userId']) || $_SESSION['userId'] != $item['sellerId']) {
    die("Unauthorized.");
}

if ($item['itemStatus'] !== 'inactive') {
    die("Only inactive items can be edited.");
}



// =======================================================================
// 4. 检查有没有真正选择图片文件
// =======================================================================
if (
    !isset($_FILES['itemImages']) ||
    empty($_FILES['itemImages']['name']) ||
    $_FILES['itemImages']['name'][0] === ''
) {
    exit_redirect("Please choose at least one file to upload.", $itemId);
}



// =======================================================================
// 5. 允许的扩展名 + 数量检查
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



// =======================================================================
// 6. 确保 uploads 文件夹存在
// =======================================================================
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}



// =======================================================================
// 7. 执行上传
// =======================================================================
$ok = 0;
$badExt = 0;
$failMove = 0;

for ($i = 0; $i < count($files); $i++) {

    $err = $_FILES['itemImages']['error'][$i];
    if ($err !== UPLOAD_ERR_OK) {
        continue;
    }

    // 获取扩展名
    $origName = $files[$i];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $badExt++;
        continue;
    }

    // 构建文件名
    $newName = "item_{$itemId}_" . time() . "_" . rand(1000,9999) . "." . $ext;
    $path = $uploadDir . $newName;

    // 移动到 uploads 文件夹
    if (!move_uploaded_file($_FILES['itemImages']['tmp_name'][$i], $path)) {
        $failMove++;
        continue;
    }

    // 写入数据库
    uploadImage($itemId, $path, 0);
    $ok++;
}



// =======================================================================
// 8. 返回信息
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



// =======================================================================
// Helper: 统一跳回 edit_item.php
// =======================================================================

function exit_redirect($msg, $itemId) {
    if ($itemId) {
        header("Location: edit_item.php?itemId=$itemId&msg=" . urlencode($msg));
    } else {
        header("Location: mylistings.php?msg=" . urlencode($msg));
    }
    exit;
}

