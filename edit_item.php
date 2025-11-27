<?php
// 显示错误
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once("db_connect.php");
require_once("Item_function.php");

$itemId = $_GET['itemId'] ?? null;
if (!$itemId) {
    die("Invalid itemId.");
}

$item = getItemById($itemId);
if (!$item) {
    die("Item not found.");
}

if (!isset($_SESSION['userId']) || $_SESSION['userId'] != $item['sellerId']) {
    die("You are not allowed to edit this item.");
}

if ($item['itemStatus'] !== 'inactive') {
    echo "<p style='color:red;font-weight:bold'>This item cannot be edited because it is not inactive.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $itemName        = $_POST['itemName'];
    $itemDescription = $_POST['itemDescription'];
    $categoryId      = $_POST['categoryId'];
    $itemCondition   = $_POST['itemCondition'];

    $success = updateItem($itemId, $itemName, $itemDescription, $categoryId, $itemCondition);

    if ($success) {
        header("Location: mylistings.php");
        exit;
    } else {
        echo "<p style='color:red'>Failed to update item.</p>";
    }
}

?>

<?php include("header.php"); ?>
<div class="container mt-4">
    <h3>Edit Item</h3>

    <form method="POST">

        <div class="form-group">
            <label>Item Name</label>
            <input type="text" name="itemName" class="form-control" 
                   value="<?= htmlspecialchars($item['itemName']) ?>" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="itemDescription" class="form-control" rows="4" required><?= 
                htmlspecialchars($item['itemDescription']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Category:</label>
             <select name="categoryId" class="form-control" required>
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


        <div class="form-group">
            <label>Condition:</label>
            <select name="itemCondition" class="form-control">
                <option value="new"         <?= ($item['itemCondition'] == 'new' ? 'selected' : '') ?>>New</option>
                <option value="used"        <?= ($item['itemCondition'] == 'used' ? 'selected' : '') ?>>Used</option>
                <option value="refurbished" <?= ($item['itemCondition'] == 'refurbished' ? 'selected' : '') ?>>Refurbished</option>
            </select>
        </div>


        <button class="btn btn-primary mt-3">Save Changes</button>
    </form>
</div>

<?php include("footer.php"); ?>
