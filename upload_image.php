<?php
require_once("db_connect.php");
require_once("image_functions.php");

echo "<pre>";
print_r($_FILES);
echo "</pre>";

echo "<pre>";
print_r($_POST);
echo "</pre>";


$itemId = $_POST['itemId'];

if (!isset($_FILES['itemImages'])) {
    die("No files uploaded.");
}

$images = $_FILES['itemImages'];
$allowed = ["jpg","jpeg","png","gif"];

// 保存成功的图片数量（用于设定主图）
$uploadedCount = 0;

// 遍历每一张上传的图片
for ($i = 0; $i < count($images['name']); $i++) {

    if ($images['error'][$i] !== 0) {
        continue; // 跳过错误文件
    }

    // 获取扩展名
    $ext = strtolower(pathinfo($images['name'][$i], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        continue;
    }

    // 生成新文件名
    $newFileName = "item_" . time() . "_" . rand(1000,9999) . "." . $ext;

    // 绝对路径（服务器存储路径）
    $uploadPath = __DIR__ . "/uploads/" . $newFileName;

    // 相对路径（给浏览器用）
    $relativeUrl = "uploads/" . $newFileName;

    // 移动图片
    if (!move_uploaded_file($images['tmp_name'][$i], $uploadPath)) {
        echo "Failed to upload: " . $images['name'][$i] . "<br>";
        continue;
    }

    // ---------- 设置主图逻辑 ----------
    $isPrimary = 0;  // 上传的一律不是主图，用户自己设置主图 users chose the primary photo

    // 写入数据库
    uploadImage($itemId, $relativeUrl, $isPrimary);

    $uploadedCount++;
}

// 上传完成
header("Location: edit_item.php?itemId=".$itemId);
exit;
