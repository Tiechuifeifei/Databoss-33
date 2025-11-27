<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (session_status() === PHP_SESSION_NONE) session_start();

require_once("db_connect.php");
require_once("image_functions.php");
require_once("Item_function.php");

// 如果item已经创建（带itemId） if the item has already been created
$itemId = isset($_GET["itemId"]) ? intval($_GET["itemId"]) : null;
if ($itemId) {
    $status = getItemStatus($itemId);

    if ($status === 'active') {
        echo "<p style='color:red;font-weight:bold'>
                This item is currently in an active auction and cannot be edited.
              </p>";
        exit;
    }
}


// 第一次提交（创建item，不含图片） first time creation
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$itemId) {
    if (!isset($_SESSION['userId'])) {
        die("Please log in before creating an item.");
    }
    $itemName = $_POST["itemName"];
    $itemDescription = $_POST["itemDescription"];
    $sellerId = $_SESSION["userId"]; 
    $categoryId = $_POST["categoryId"];
    $itemCondition = $_POST["itemCondition"];

    // 插入items表
    $sql = "INSERT INTO items (itemName, itemDescription, sellerId, categoryId, itemCondition)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiis", $itemName, $itemDescription, $sellerId, $categoryId, $itemCondition);
    $stmt->execute();

    $itemId = $conn->insert_id;

    // 上传图片
    header("Location: create_item.php?itemId=" . $itemId);
    exit();
}

// 图片上传 
if (isset($_POST["uploadImage"]) && $itemId) {

    if (isset($_FILES["itemImages"])) {

        $images = $_FILES["itemImages"];
        $allowed = ["jpg","jpeg","png","gif"];

        for ($i = 0; $i < count($images["name"]); $i++) {

            if ($images["error"][$i] !== 0) continue;

            $ext = strtolower(pathinfo($images["name"][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) continue;

            // 限制 3 张
            if (countImagesByItemId($itemId) >= 3) break;

            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir);

            $newFile = uniqid() . "." . $ext;
            $savePath = $targetDir . $newFile;

            move_uploaded_file($images["tmp_name"][$i], $savePath);

            $isPrimary = (countImagesByItemId($itemId) === 0 ? 1 : 0);
            uploadImage($itemId, $savePath, $isPrimary);
        }
    }

    header("Location: create_item.php?itemId=$itemId");
    exit();
}

// 删除图片
// 这里将来可以写成只要是auction开始之前都可以删除图片  place holder: write the images could be changed before the auction in the future if I have capacity 
if (isset($_GET["deleteImage"]) && $itemId) {
    deleteImage($_GET["deleteImage"]);
    header("Location: create_item.php?itemId=$itemId");
    exit();
}

// set the primary photo
if (isset($_GET["setPrimary"]) && $itemId) {
    setPrimaryImage($_GET["setPrimary"], $itemId);
    header("Location: create_item.php?itemId=$itemId");
    exit();
}

?>

<!DOCTYPE html>
<html>
<body>

<?php if (isset($_GET['relist'])): ?>
    <div style="padding:10px;background:#fff3cd;border:1px solid #ffeeba;">
        <strong>You are relisting this item.</strong><br>
        You can edit the item details or images before starting a new auction.
    </div>
<?php endif; ?>


<h2>Create Item</h2>

<?php if (!$itemId): ?>

<!-- create Item  -->
<form method="POST">

    <label>Item Name:</label><br>
    <input type="text" name="itemName" required><br><br>

    <label>Description:</label><br>
    <textarea name="itemDescription"></textarea><br><br>

    <label>Category:</label><br>
    <select name="categoryId" required>
        <?php 
        $catSql = "SELECT categoryId, categoryName FROM categories ORDER BY categoryId ASC";
        $catResult = $conn->query($catSql);
        while ($row = $catResult->fetch_assoc()) { ?>
            <option value="<?= $row['categoryId'] ?>"><?= $row['categoryName'] ?></option>
        <?php } ?>
    </select>
    <br><br>

    <label>Condition:</label><br>
    <select name="itemCondition">
        <option value="new">New</option>
        <option value="used">Used</option>
        <option value="refurbished">Refurbished</option>
    </select>
    <br><br>

    <button type="submit">Next: Upload Images</button>

</form>

<?php else: ?>

<!-- upload image -->

<h3>Upload Images (max 3)</h3>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="itemImages[]" multiple accept="image/*">
    <button type="submit" name="uploadImage">Upload</button>
</form>

<hr>

<h3>Images</h3>

<?php
$images = getImagesByItemId($itemId);
if (empty($images)) {
    echo "<p>No images uploaded.</p>";
} else {
    foreach ($images as $img) { ?>
        <div style="margin-bottom:10px;">
            <img src="<?= $img['imageUrl'] ?>" width="120"><br>

            <!-- primary -->
            <?php if ($img["isPrimary"] == 1): ?>
                <strong>⭐ Primary Image</strong>
            <?php else: ?>
                <a href="create_item.php?itemId=<?= $itemId ?>&setPrimary=<?= $img['imageId'] ?>">
                    Set as Primary
                </a>
            <?php endif; ?>

            &nbsp; | &nbsp;

            <a href="create_item.php?itemId=<?= $itemId ?>&deleteImage=<?= $img['imageId'] ?>">
                Delete
            </a>
        </div>
<?php }} ?>

<hr>

<!-- item is created, jump to auction -->
<a href="create_auction.php?itemId=<?= $itemId ?>">
    <button>Next: Create Auction</button>
</a>

<?php endif; ?>

</body>
</html>
