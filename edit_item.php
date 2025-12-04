<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("db_connect.php");
require_once("Item_function.php");
require_once("Image_functions.php");
require_once("utilities.php");

$conn = get_db_connection();

$itemId = $_GET['itemId'] ?? null;
if (!$itemId) die("Invalid itemId.");

$item = getItemById($itemId);
if (!$item) die("Item not found.");

if (!isset($_SESSION['userId']) || $_SESSION['userId'] != $item['sellerId'])
    die("Unauthorized.");

if ($item['itemStatus'] !== 'inactive')
    die("<p style='color:red;font-weight:bold'>Editing only allowed on inactive items.</p>");


// =========================================================
// SAVE TEXT INFO
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_item_info'])) {

    updateItem(
        $itemId,
        $_POST['itemName'],
        $_POST['itemDescription'],
        $_POST['categoryId'],
        $_POST['itemCondition']
    );


    header("Location: mylistings.php?updated=1");
    exit;
}

$msg = $_GET['msg'] ?? null;

include("header.php");
?>
<link rel="stylesheet" href="css/custom_2.css">

<div class="container mt-4 edit-item-page">

    <h3 class="edit-item-title">Edit Item</h3>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- =========================================================
           A.
    ========================================================= -->
    <form id="itemForm" method="POST">

        <div class="form-group">
            <label class="edit-label">Item Name</label>
            <input type="text" name="itemName" class="edit-input"
                   value="<?= htmlspecialchars($item['itemName']) ?>" required>
        </div>

        <div class="edit-field">
            <label class="edit-label">Description</label>
            <textarea name="itemDescription" class="edit-input" rows="4" required><?= 
                htmlspecialchars($item['itemDescription']) ?></textarea>
        </div>

        <div class="edit-row">
            <div class="edit-field edit-field-half">
                <label class="edit-label">Category:</label>
                <select name="categoryId" class="edit-select" required>
                    <?php
                    $catSql = "SELECT * FROM categories ORDER BY categoryId ASC";
                    $catResult = $conn->query($catSql);
                    while ($row = $catResult->fetch_assoc()):
                        $selected = ($row['categoryId'] == $item['categoryId']) ? "selected" : "";
                    ?>
                        <option value="<?= $row['categoryId'] ?>" <?= $selected ?>>
                            <?= $row['categoryName'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="edit-field edit-field-half">
                <label class="edit-label">Condition:</label>
                <select name="itemCondition" class="edit-select" required>
                    <option value="new" <?= ($item['itemCondition']=='new'?'selected':'') ?>>New</option>
                    <option value="used" <?= ($item['itemCondition']=='used'?'selected':'') ?>>Used</option>
                    <option value="refurbished" <?= ($item['itemCondition']=='refurbished'?'selected':'') ?>>Refurbished</option>
                </select>
            </div>
        </div>

        <!-- no button -->
    </form>

    <hr class="my-4">

    <!-- =========================================================
         B.
    ========================================================= -->
    <h4 class="edit-label">Current Images</h4>

    <?php $images = getImagesByItemId($itemId); ?>

    <?php if (empty($images)): ?>
        <p>No images uploaded yet.</p>
    <?php else: ?>
        <div class="row mb-4">

        <?php foreach ($images as $img): ?>
            <div class="col-md-3 text-center mb-3">

                <img src="<?= htmlspecialchars($img['imageUrl']) ?>" 
                     class="img-fluid border"
                     style="max-height:150px; object-fit:cover;">

                <?php if ($img['isPrimary'] == 1): ?>
                    <div><span class="badge badge-edit-success mt-2">Primary</span></div>
                <?php else: ?>
                    <a href="set_primary_image.php?imageId=<?= $img['imageId'] ?>&itemId=<?= $itemId ?>"
                       class="edit-primary-btn">Set Primary</a>
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


    <hr class="my-4">

    <!-- =========================================================
         C. 
    ========================================================= -->
    <h4 class="edit-label">Upload New Images (max 3)</h4>

    <form method="POST" action="upload_image.php" enctype="multipart/form-data">
        <input type="hidden" name="itemId" value="<?= $itemId ?>">

        <input type="file"
               name="itemImages[]"
               class="form-control-file"
               multiple
               accept=".jpg,.jpeg,.png,.gif,.webp,.heic,.heif,image/*">

        <button type="submit" class="edit-btn edit-btn-small mt-3">
            Upload Images
        </button>
    </form>

    <!-- =========================================================
         D. 
    ========================================================= -->
    <div class="edit-actions mt-5">
        <button
            type="submit"
            class="edit-next-btn"
            form="itemForm"
            name="save_item_info"
            value="1">
            Save
        </button>

        <a href="mylistings.php" class="edit-btn edit-btn-small ml-3">
            Cancel
        </a>
    </div>

</div>

<?php include("footer.php"); ?>

