<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 正确路径（Image_functions.php 在上一层）
require_once("../Image_functions.php");
require_once("../db_connect.php");

// ===== Step 1: 插入一张测试图片 =====
$itemId = 2020; // 你可以改成真实存在的 itemId

$testImageUrl = "test_image_" . time() . ".jpg";

$imgId = uploadImage($itemId, $testImageUrl, 0);

echo "<h3>Uploaded test image: imageId = $imgId</h3>";

// ===== Step 2: 删除它 =====
$deleted = deleteImage($imgId);

echo "<h3>Delete result: " . ($deleted ? "Success" : "Failed") . "</h3>";

// ===== Step 3: 再次尝试获取该图片，检查是否真的删掉 =====
$sql = "SELECT * FROM images WHERE imageId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $imgId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo "<h3>Image still exists in DB?</h3>";
var_dump($result);
