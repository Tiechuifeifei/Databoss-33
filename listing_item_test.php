<?php
require_once("db_connect.php");
require_once("image_functions.php");

$itemId = $_GET["itemId"];

// 获取 item
$sql = "SELECT * FROM items WHERE itemId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $itemId);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

// 获取 images
$images = getImagesByItemId($itemId);
?>

<h2>Item Detail (Test)</h2>

<h3><?php echo $item["itemName"]; ?></h3>
<p><?php echo $item["itemDescription"]; ?></p>

<h3>Images:</h3>

<?php
foreach ($images as $img) {
    echo "<div style='margin:10px; display:inline-block; text-align:center;'>";

    // 图片
    echo "<img src='" . $img['imageUrl'] . "' width='150'><br>";

    // 如果是主图 → 显示标签
    if ($img['isPrimary'] == 1) {
        echo "<strong>Primary Image</strong><br>";
    } else {
        // 否则给一个按钮设置为主图
        echo "<a href='set_primary_image.php?imageId=" . $img['imageId'] . "&itemId=$itemId'>Set as Primary</a><br>";
    }

    echo "</div>";
}
?>
<br><br>
<a href="upload_image_test.php?itemId=<?php echo $itemId; ?>">Upload more images</a>
