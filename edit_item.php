<link rel="stylesheet" href="css/custom_2.css">

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once("db_connect.php");
require_once("Item_function.php");
require_once("Image_functions.php");  
require_once("utilities.php");

$conn = get_db_connection();

$itemId = $_GET['itemId'] ?? null;
if (!$itemId) {
    die("Invalid itemId.");
}

$item = getItemById($itemId);
if (!$item) {
    die("Item not found.");
}

// 权限校验
if (!isset($_SESSION['userId']) || $_SESSION['userId'] != $item['sellerId']) {
    die("You are not allowed to edit this item.");
}

// 状态必须是 inactive（对应 scheduled auction）
if ($item['itemStatus'] !== 'inactive') {
    echo "<p style='color:red;font-weight:bold'>This item cannot be edited because it is not inactive.</p>";
    exit;
}

// 如果保存 item 基本信息（但不跳走）
$updateMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_info'])) {

    $itemName        = $_POST['itemName'];
    $itemDescription = $_POST['itemDescription'];
    $categoryId      = $_POST['categoryId'];
    $itemCondition   = $_POST['itemCondition'];

    $success = updateItem($itemId, $itemName, $itemDescription, $categoryId, $itemCondition);

    if ($success) {
        $updateMessage = "<p class='text-success'>Basic info updated successfully!</p>";
    } else {
        $updateMessage = "<p class='text-danger'>Failed to update item info.</p>";
    }
}

?>

<?php include("header.php"); ?>

<div class="container mt-4 edit-item-page">
    <h3 class="edit-item-title">Edit Item</h3>

    <?= $updateMessage ?>

    <!-- 编辑基本信息 -->
    <form method="POST" class="edit-item-form">
        <input type="hidden" name="save_info" value="1">

        <div class="form-group">
            <label class="edit-label">Item Name</label>
            <input type="text" name="itemName" class="edit-input" 
                   value="<?= htmlspecialchars($item['itemName']) ?>" required>
        </div>

        <div class="edit-field">
            <label class="edit-label">Description</label>
            <textarea name="itemDescription" class="edit-input" rows="4" placeholder="e.g. This is a Chanel vintage coat..." required><?= 
                htmlspecialchars($item['itemDescription']) ?></textarea>
        </div>

        <div class="edit-row">

            <div class="edit-field edit-field-half">
            <label class="edit-label">Category:</label>
            <select name="categoryId" class="edit-select" required>
                <option value="" disabled>select a category</option>
                <?php 
                $catSql = "SELECT categoryId, categoryName FROM categories ORDER BY categoryId ASC";
                $catResult = $conn->query($catSql);
                 
                while ($row = $catResult->fetch_assoc()) { 
                    $selected = ($row['categoryId'] == $item['categoryId']) ? "selected" : "";
                ?>
                <option value="<?= $row['categoryId'] ?>" <?= $selected ?>>
                    <?= $row['categoryName'] ?>
                </option>
                <?php } ?>
            </select>
            </div>
            
        

        <div class="edit-field edit-field-half">
            <label class="edit-label">Condition:</label>
            <select name="itemCondition" class="edit-select" required>
                <option value="new"         <?= ($item['itemCondition'] == 'new' ? 'selected' : '') ?>>New</option>
                <option value="used"        <?= ($item['itemCondition'] == 'used' ? 'selected' : '') ?>>Used</option>
                <option value="refurbished" <?= ($item['itemCondition'] == 'refurbished' ? 'selected' : '') ?>>Refurbished</option>
            </select>
        </div>
    </div>

    <div class="edit-actions">
        <button class="edit-next-btn" type="submit">Save Changes</button>
    </div>
    </form>



    <!-- ----------------- 图片管理区 --------------------- -->
    <hr class="my-4">
    <h4 class="edit-label">Manage Images</h4>

    <?php 
    $images = getImagesByItemId($itemId);
    ?>

    <?php if (empty($images)): ?>
        <p style="font-weight:200;">No images uploaded for this item.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($images as $img): ?>
                <div class="col-md-3 text-center mb-3">
                    <img src="<?= htmlspecialchars($img['imageUrl']) ?>" 
                         class="img-fluid border" 
                         style="max-height:150px; object-fit:cover;">

                    <?php if ($img['isPrimary'] == 1): ?>
                        <div class="mt-2">
                            <span class="badge badge-edit-success">Primary</span>
                        </div>
                    <?php else: ?>
                        <a href="set_primary_image.php?imageId=<?= $img['imageId'] ?>&itemId=<?= $itemId ?>" 
                           class="edit-primary-btn">Set as primary</a>
                    <?php endif; ?>

                    <a href="delete_image.php?itemId=<?= $itemId ?>&imageId=<?= $img['imageId'] ?>"
                       class="edit-delete-btn"
                       onclick="return confirm('Delete this image?');">
                       Delete
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <!-- 上传新图片 -->
    <form method="POST" action="upload_image.php" enctype="multipart/form-data" class="mt-3">
        <input type="hidden" name="itemId" value="<?= $itemId ?>">
        <div class="form-group">
            <label style="font-weight:200;">Upload new images (max 3)</label>
            <input type="file" name="itemImages[]" class="form-control-file" multiple required>
        </div>
        <button type="submit" class="edit-btn edit-btn-small">Upload Images</button>
    </form>


    <!-- 完成编辑 -->
    <hr class="my-4">
    <a href="mylistings.php" class="edit-btn">Finish Editing</a>

</div>

<?php include("footer.php"); ?>
