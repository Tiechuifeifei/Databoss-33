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

// create item(new)
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$itemId) {
    if (!isset($_SESSION['userId'])) {
        die("Please log in before creating an item.");
    }

    $itemName        = $_POST["itemName"];
    $itemDescription = $_POST["itemDescription"];
    $sellerId        = $_SESSION["userId"];
    $categoryId      = $_POST["categoryId"];
    $itemCondition   = $_POST["itemCondition"];


    $itemId = createItem($itemName, $itemDescription, $sellerId, $categoryId, $itemCondition);

    if ($itemId === false) {
        die("Failed to create item.");
    }

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

<head>
    <link rel="stylesheet" href="css/custom_2.css">
</head>

<body>

<?php if (isset($_GET['relist'])): ?>
    <div style="padding:10px;background:#fff3cd;border:1px solid #ffeeba;">
        <strong>You are relisting this item.</strong><br>
        You can edit the item details or images before starting a new auction.
    </div>
<?php endif; ?>

<div class="form-container">
    <h1 class="create-title">Create Item</h1>

    <?php if (!$itemId): ?>

<!-- Create Item Form -->
    <form method="POST">
        <div class="form-grid">

            <div class="input-group full-width">
                <label class="input-detail-title">Item Name</label><br>
                <input type="text" name="itemName" class="create-item-input" placeholder="e.g. Vintage coat" required>
            </div>

            <div class="input-group full-width">
                <label class="input-detail-title">Description</label><br>
                <textarea name="itemDescription" class="create-item-input" placeholder="e.g. This is a Chanel vintage coat..."></textarea>
            </div>

            <div class="create-item-group">
                <label class="input-detail-title">Category</label><br>
                <select name="categoryId" class="create-item-input" required>
                    <option value="" disabled selected>select a category</option>
                <?php 
                $catSql = "SELECT categoryId, categoryName FROM categories ORDER BY categoryId ASC";
                $catResult = $conn->query($catSql);
                while ($row = $catResult->fetch_assoc()) { ?>
                    <option value="<?= $row['categoryId'] ?>"><?= $row['categoryName'] ?></option>
                <?php } ?>
                </select>
            </div>

            <div class="create-item-group">
                <label class="input-detail-title">Condition</label><br>
                <select name="itemCondition" class="create-item-input" required>
                    <option value="" disabled selected>select a condition</option>
                    <option value="new">New</option>
                    <option value="used">Used</option>
                    <option value="refurbished">Refurbished</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn-next">Next: Upload Images</button>
            </div>
        </div>
    </form>
</div>

<?php else: ?>

<!-- Upload Images -->
<div class="form-container">

    <div style="margin-bottom: 40px;">
        <label class="input-detail-title">Select Images - Max 3</label>
        
        <form method="POST" enctype="multipart/form-data"
            style="display:flex; justify-content: space-between; align-items:center; 
            border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 10px;">
            
            <input type="file" name="itemImages[]" multiple accept="image/*"
                style="border:none; font-family: monospace; font-size: 14px;">
            
            <button type="submit" name="uploadImage" class="btn-next"
                style="padding: 8px 20px; margin: 0; border: 1px solid #ccc;">
                UPLOAD
            </button>
        </form>
    </div>

    <h3 class="input-detail-title" style="text-align: center; margin-bottom: 30px;">Selected Images</h3>

    <?php
    $images = getImagesByItemId($itemId);
    $imageCount = count($images);

    if ($imageCount === 0) {
        echo "<div style='text-align:center; padding:40px; color:#999;'>";
        echo "No images uploaded yet.";
        echo "</div>"; 
    } else {
        echo '<div class="image-gallery" style="display:flex; gap:20px; flex-wrap:wrap;">';

        foreach ($images as $img) { ?>
            <div class="image-card">
                <img src="<?= $img['imageUrl'] ?>" class="image-preview"><br>
                
                <div class="image-actions">
                    <?php if ($img["isPrimary"] == 1): ?>
                        <span class="image-info" style="font-weight:200; color:black;">
                            Primary
                        </span>
                    <?php else: ?>
                        <a href="create_item.php?itemId=<?= $itemId ?>&setPrimary=<?= $img['imageId'] ?>"
                        style="text-decoration:none; color:#666;">
                            Set Primary
                        </a>
                    <?php endif; ?>

                    <span style="margin: 0 5px; color:#eee;">|</span>

                    <a href="create_item.php?itemId=<?= $itemId ?>&deleteImage=<?= $img['imageId'] ?>"
                    class="delete-link" style="text-decoration:none;">
                        Delete
                    </a>
                </div>
            </div>
    <?php } 
        echo '</div>'; 
    } ?>

    <div style="margin-top: 60px;"></div>

    <div class="form-footer" style="display:flex; justify-content:flex-end; align-items:center;">
        <?php if ($imageCount === 0): ?>
            <div style="display:flex; align-items:center; gap:15px;">
                <p style="color:#d9534f; font-weight:200; font-size:12px; margin:0;">
                    * Please upload at least one image
                </p>
                <button class="btn-next" style="opacity:0.5; cursor:not-allowed;" disabled>
                    Next: Create Auction
                </button>
            </div>
        <?php else: ?>
            <a href="create_auction.php?itemId=<?= $itemId ?>" style="text-decoration:none;">
                <button class="btn-next">
                    Next: Create Auction &rarr;
                </button>
            </a>
        <?php endif; ?>
    </div>

</div>

<?php endif; ?>
<?php include "footer.php" ?>
</body>
</html>
