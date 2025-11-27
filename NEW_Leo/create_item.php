<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once("db_connect.php");
require_once("image_functions.php");
require_once("Item_function.php");

// If item already exists
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

// First time creation (no itemId yet)
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$itemId) {
    if (!isset($_SESSION['userId'])) {
        die("Please log in before creating an item.");
    }

    $itemName = $_POST["itemName"];
    $itemDescription = $_POST["itemDescription"];
    $sellerId = $_SESSION["userId"];
    $categoryId = $_POST["categoryId"];
    $itemCondition = $_POST["itemCondition"];

// Leo debug: Fix default itemStatus to 'inactive' when creating a new item,
// otherwise the page wrongly blocks creation as if the item is already in an active auction.
$sql = "INSERT INTO items (
            sellerId,
            itemName,
            itemDescription,
            categoryId,
            itemCondition,
            itemStatus
        )
        VALUES (?, ?, ?, ?, ?, 'inactive')";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "issis",
    $sellerId,
    $itemName,
    $itemDescription,
    $categoryId,
    $itemCondition
);

    $stmt->execute();

    $itemId = $conn->insert_id;

    header("Location: create_item.php?itemId=" . $itemId);
    exit();
}

// Upload images
if (isset($_POST["uploadImage"]) && $itemId) {

    if (isset($_FILES["itemImages"])) {

        $images = $_FILES["itemImages"];
        $allowed = ["jpg","jpeg","png","gif"];

        for ($i = 0; $i < count($images["name"]); $i++) {

            if ($images["error"][$i] !== 0) continue;

            $ext = strtolower(pathinfo($images["name"][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) continue;

            // Max 3 images
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

// Delete image
if (isset($_GET["deleteImage"]) && $itemId) {
    deleteImage($_GET["deleteImage"]);
    header("Location: create_item.php?itemId=$itemId");
    exit();
}

// Set primary
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

<!-- Create Item Form -->
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

<!-- Upload Images -->
<h3>Upload Images (max 3)</h3>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="itemImages[]" multiple accept="image/*">
    <button type="submit" name="uploadImage">Upload</button>
</form>

<hr>

<h3>Images</h3>

<?php
$images = getImagesByItemId($itemId);
$imageCount = count($images);

if ($imageCount === 0) {
    echo "<p>No images uploaded.</p>";
} else {
    foreach ($images as $img) { ?>
        <div style="margin-bottom:10px;">
            <img src="<?= $img['imageUrl'] ?>" width="120"><br>

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

<!-- NEXT BUTTON WITH IMAGE CHECK -->
<?php if ($imageCount === 0): ?>
    <p style="color:red;font-weight:bold;">Please upload at least one image before continuing.</p>
    <button class="btn btn-secondary" disabled>Next: Create Auction</button>
<?php else: ?>
    <a href="create_auction.php?itemId=<?= $itemId ?>">
        <button>Next: Create Auction</button>
    </a>
<?php endif; ?>

<?php endif; ?>

</body>
</html>
