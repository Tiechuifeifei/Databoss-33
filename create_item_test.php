<?php
require_once("db_connect.php");

// ---------- 加载 categories 选项 ----------
$catSql = "SELECT categoryId, categoryName FROM categories ORDER BY categoryId ASC";
$catResult = $conn->query($catSql);

// ---------- 如果表单提交 ----------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $itemName = $_POST["itemName"];
    $itemDescription = $_POST["itemDescription"];
    $sellerId = 1; // 测试用。真实情况用 $_SESSION['userId']
    $categoryId = $_POST["categoryId"];
    $itemCondition = $_POST["itemCondition"];

    // 插入 items 表
    $sql = "INSERT INTO items (itemName, itemDescription, sellerId, categoryId, itemCondition)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiis", $itemName, $itemDescription, $sellerId, $categoryId, $itemCondition);
    $stmt->execute();

    $itemId = $conn->insert_id;

    echo "<h3>Item created! ID = $itemId</h3>";
    echo "<a href='upload_image_test.php?itemId=$itemId'>Upload Images for this item</a>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<body>

<h2>Create Item (Test Version)</h2>

<form method="POST">

    <label>Item Name:</label><br>
    <input type="text" name="itemName" required><br><br>

    <label>Description:</label><br>
    <textarea name="itemDescription"></textarea><br><br>

    <label>Category:</label><br>
    <select name="categoryId" required>
        <?php 
        if ($catResult && $catResult->num_rows > 0) {
            while ($row = $catResult->fetch_assoc()) { ?>
                <option value="<?php echo $row['categoryId']; ?>">
                    <?php echo $row['categoryName']; ?>
                </option>
        <?php 
            }
        } else {
            echo "<option>No categories available</option>";
        }
        ?>
    </select>
    <br><br>

    <label>Condition:</label><br>
    <select name="itemCondition">
        <option value="new">New</option>
        <option value="used">Used</option>
        <option value="refurbished">Refurbished</option>
    </select>
    <br><br>

    <button type="submit">Create Item</button>

</form>

</body>
</html>
