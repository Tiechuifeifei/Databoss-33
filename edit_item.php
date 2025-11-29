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

<div class="container mt-4">

    <h2>Edit Item</h2>
    <?php if($msg): ?><div class="alert alert-info"><?=htmlspecialchars($msg)?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <input type="hidden" name="finish_editing" value="1">
    <input type="hidden" name="itemId" value="<?= htmlspecialchars($itemId) ?>">

    <!-- BASIC INFO -->
    <div class="form-group mt-3">
        <label>Item Name</label>
        <input name="itemName" required class="form-control"
               value="<?=htmlspecialchars($item['itemName'])?>">
    </div>

    <div class="form-group mt-3">
        <label>Description</label>
        <textarea name="itemDescription" required class="form-control" rows="4"><?= 
            htmlspecialchars($item['itemDescription']) ?></textarea>
    </div>

    <div class="form-group mt-3">
        <label>Category</label>
        <select name="categoryId" class="form-control">
        <?php
            $res = $conn->query("SELECT categoryId,categoryName FROM categories");
            while($c=$res->fetch_assoc()):
        ?>
            <option value="<?=$c['categoryId']?>" 
                   <?=$c['categoryId']==$item['categoryId']?"selected":""?>>
                   <?=$c['categoryName']?></option>
        <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group mt-3">
        <label>Condition</label>
        <select name="itemCondition" class="form-control">
            <option <?=$item['itemCondition']=="new"?'selected':''?>>new</option>
            <option <?=$item['itemCondition']=="used"?'selected':''?>>used</option>
            <option <?=$item['itemCondition']=="refurbished"?'selected':''?>>refurbished</option>
        </select>
    </div>


    <!-- ============= IMAGE MANAGEMENT ============= -->
    <hr class="my-4">
    <h4>Manage Images (max 3)</h4>

    <?php $images = getImagesByItemId($itemId); ?>

    <div class="row">
                <?php foreach($images as $img): ?>
                    <div class="col-md-3 text-center mb-4">

            <!-- image -->
                    <img src="<?=$img['imageUrl']?>" 
                    class="img-fluid border mb-2"
                    style="max-height:150px;object-fit:cover;border-radius:6px;">


            <!-- 上排：Primary / Set Primary (位置保持一致) -->
                <div class="d-flex justify-content-center mb-2" style="gap:10px;">

                    <?php if($img['isPrimary']): ?>
                        <span class="badge bg-success px-3 py-2" style="font-size:14px;">Primary</span>
                <?php else: ?>
                    <a href="set_primary_image.php?imageId=<?=$img['imageId']?>&itemId=<?=$itemId?>"
                    class="btn btn-outline-primary btn-sm">
                    Set Primary
                    </a>
                <?php endif; ?>

            </div>


            <!-- 下排统一 Delete -->
            <a href="delete_image.php?imageId=<?=$img['imageId']?>&itemId=<?=$itemId?>"
            onclick="return confirm('Delete this image?');"
            class="btn btn-outline-danger btn-sm w-75">
            Delete
            </a>

        </div>
        <?php endforeach; ?>



    <!-- Upload Images -->
    <?php if(count($images)<3): ?>
    <div class="form-group">
        <label>Upload Images</label>
        <input type="file" name="itemImages[]" 
               accept=".jpg,.jpeg,.png,.gif,.webp"
               class="form-control-file" multiple>

        <button formaction="upload_image.php" formmethod="POST"
                class="btn btn-secondary mt-2">Upload</button>
    </div>
    <?php endif; ?>


    <!-- FINAL BUTTON -->
    <hr class="my-4">
    <button type="submit" class="btn btn-success btn-lg w-100">
        Finish Editing & Save All Changes
    </button>

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
