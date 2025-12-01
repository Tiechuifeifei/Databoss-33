<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("header.php") ?>

<?php
if (!isset($_GET["itemId"])) {
    echo "<div class='alert alert-danger'>No item selected for auction.</div>";
    exit();
}
$itemId = intval($_GET["itemId"]);

$db = get_db_connection();
$stmt = $db->prepare("SELECT COUNT(*) AS imgCount FROM images WHERE itemId = ?");
$stmt->bind_param("i", $itemId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row['imgCount'] == 0) {
    echo "<div class='alert alert-danger'>
            Please upload at least one image before creating an auction.
          </div>";
    echo "<a class='btn btn-warning' href='create_item.php?itemId=$itemId'>Upload image</a>";
    include("footer.php");
    exit();
}

?>

<link rel="stylesheet" href="css/custom_2.css">

<div class="form-container">
    

    <h1 class="page-title" style="text-align: center; font-family:monospace,'Monaco';
    letter-space: -1px; padding-bottom: 30px;">
      Create Auction</h1>

    <form method="post" action="create_auction_result.php">
        
        <input type="hidden" name="itemId" value="<?php echo $itemId; ?>">

        <div class="form-grid">

            <div class="input-group">
                <label class="input-detail-title">Starting Price</label>
                <input type="number" step="0.01" name="startPrice" class="styled-input" 
                       placeholder="0.00" required>
            </div>

            <div class="input-group">
                <label class="input-detail-title">Reserve Price</label>
                <input type="number" step="0.01" name="reservePrice" class="styled-input" 
                       placeholder="0.00" required>
            </div>

            <div class="input-group">
                <label class="input-detail-title">Start Date</label>
                <input type="datetime-local" name="startTime" class="styled-input" required>
            </div>

            <div class="input-group">
                <label class="input-detail-title">End Date</label>
                <input type="datetime-local" name="endTime" class="styled-input" required>
            </div>

        </div>


        <div class="form-footer">
            <button type="submit" class="btn-next">
                Create Auction &rarr;
            </button>
        </div>

    </form>
</div>

