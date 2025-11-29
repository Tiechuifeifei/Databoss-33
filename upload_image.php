<?php
ini_set("display_errors",1);
error_reporting(E_ALL);
session_start();

require_once("db_connect.php");
require_once("Image_functions.php");
require_once("Item_function.php");


// =========== 1. 防止直接访问 ===========
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: mylistings.php?msg=" . urlencode("⚠ Invalid upload request"));
    exit;
}

// =========== 2. 必须有 itemId ===========
$itemId = $_POST['itemId'] ?? null;
if(!$itemId){
    header("Location: mylistings.php?msg=" . urlencode("⚠ No item selected for upload"));
    exit;
}


// =========== 3. 权限检查 ===========
$item = getItemById($itemId);

if(!isset($_SESSION['userId']) || $_SESSION['userId'] != $item['sellerId'])
    die("Unauthorized");

if($item['itemStatus'] !== 'inactive')
    die("Only inactive items can be edited");


// =========== 4. 开始处理上传 ===========
$allowed = ['jpg','jpeg','png','gif','webp'];
$existing = countImagesByItemId($itemId);
$files = $_FILES['itemImages']['name'];

if($existing >= 3){
    exit_redirect("Image limit reached (Max 3)", $itemId);
}

if($existing + count($files) > 3){
    exit_redirect("You may upload only ".(3-$existing)." more", $itemId);
}


// ========== 上传目录 ==========
$uploadDir = "uploads/";
if(!is_dir($uploadDir)) mkdir($uploadDir,0777,true);


$ok=0;
for($i=0;$i<count($files);$i++){

    if($_FILES['itemImages']['error'][$i] != 0) continue;

    $ext = strtolower(pathinfo($files[$i], PATHINFO_EXTENSION));
    if(!in_array($ext, $allowed)) continue;

    $name = "item_{$itemId}_".time().rand(1000,9999).".".$ext;
    $path = $uploadDir.$name;

    if(move_uploaded_file($_FILES['itemImages']['tmp_name'][$i], $path)){
        uploadImage($itemId, $path, 0); // ⭐ ← 使用你真实存在的函数
        $ok++;
    }
}


// ========== 上传完成跳回 ==========
exit_redirect("$ok image(s) uploaded",$itemId);


function exit_redirect($msg,$id){
    header("Location: edit_item.php?itemId=$id&msg=".urlencode($msg));
    exit;
}

?>

