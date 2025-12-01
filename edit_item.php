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
if (!$itemId) die("Invalid itemId.");

$item = getItemById($itemId);
if (!$item) die("Item not found.");

if (!isset($_SESSION['userId']) || $_SESSION['userId'] != $item['sellerId'])
    die("Unauthorized.");

if ($item['itemStatus'] !== 'inactive')
    die("<p style='color:red;font-weight:bold'>Editing only allowed when item is inactive.</p>");

// ================= Final Save (last big button) =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finish_editing'])) {

    $success = updateItem(
        $itemId,
        $_POST['itemName'],
        $_POST['itemDescription'],
        $_POST['categoryId'],
        $_POST['itemCondition']
    );

    if ($success) {
        header("Location: mylistings.php?updated=1");
        exit;
    } else $msg = "Failed to update item info.";
}

$msg = $_GET['msg'] ?? null;
include("header.php");
?>

<div class="container mt-4 edit-item-page">
    <h3 class="edit-item-title">Edit Item</h3>

<form method="POST" enctype="multipart/form-data">

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

    <?php $images = getImagesByItemId($itemId); ?>

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

</form>
</div>

<?php include("footer.php"); ?>这是upload：<?php
ini_set("display_errors",1);
error_reporting(E_ALL);
session_start();
require_once("db_connect.php");
require_once("Image_functions.php");
require_once("Item_function.php");

// ========= 防止直接进入 upload_image.php =========
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: mylistings.php?msg=" . urlencode("⚠ Invalid upload request"));
    exit;
}

// 只有POST才读取 itemId
$itemId = $_POST['itemId'] ?? null;
if(!$itemId){
    header("Location: mylistings.php?msg=" . urlencode("⚠ No item selected for upload"));
    exit;
}


$item=getItemById($itemId);

if(!isset($_SESSION['userId']) || $_SESSION['userId']!=$item['sellerId'])
    die("Unauthorized");

if($item['itemStatus']!=='inactive')
    die("Only inactive items can be edited");

$allowed=['jpg','jpeg','png','gif','webp'];
$existing=count(getImagesByItemId($itemId));
$files=$_FILES['itemImages']['name'];

if($existing>=3)
    exit_redirect("Already have 3 images",$itemId);

if($existing+count($files)>3)
    exit_redirect("You may upload ".(3-$existing)." more",$itemId);

$uploadDir="uploads/";
if(!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

$ok=0;

for($i=0;$i<count($files);$i++){
    if($_FILES['itemImages']['error'][$i]!=0)continue;
    $ext=strtolower(pathinfo($files[$i],PATHINFO_EXTENSION));
    if(!in_array($ext,$allowed))continue;

    $name="item_{$itemId}_".time().rand(1000,9999).".".$ext;
    $path=$uploadDir.$name;

    if(move_uploaded_file($_FILES['itemImages']['tmp_name'][$i],$path)){
        addImage($itemId,$path,0);
        $ok++;
    }
}

exit_redirect("$ok image(s) uploaded",$itemId);


function exit_redirect($msg,$id){
    header("Location: edit_item.php?itemId=$id&msg=".urlencode($msg));
    exit;
}
?>
