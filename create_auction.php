<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("header.php") ?>

<?php
// 从URL获取上一步传来的itemId
if (!isset($_GET["itemId"])) {
    echo "<div class='alert alert-danger'>No item selected for auction.</div>";
    exit();
}
$itemId = intval($_GET["itemId"]);
?>

<div class="container">

<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Create new auction</h2>
  <div class="card">
    <div class="card-body">

      <form method="post" action="create_auction_result.php">

        <input type="hidden" name="itemId" value="<?php echo $itemId; ?>">

        <div class="form-group row">
          <label class="col-sm-2 col-form-label text-right">Title of auction</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" 
                   name="auctionTitle"
                   placeholder="e.g. Vintage vase auction"
                   required>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label text-right">Starting price</label>
          <div class="col-sm-10">
            <input type="number" step="0.01" class="form-control" 
                   name="startPrice"
                   required>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label text-right">Reserve price</label>
          <div class="col-sm-10">
            <input type="number" step="0.01" class="form-control" 
                   name="reservePrice"
                   required>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label text-right">Start date</label>
          <div class="col-sm-10">
            <input type="datetime-local"
                   class="form-control"
                   name="startTime"
                   required>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label text-right">End date</label>
          <div class="col-sm-10">
            <input type="datetime-local" class="form-control" 
                   name="endTime"
                   required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary form-control">Create Auction</button>

      </form>

    </div>
  </div>
</div>

<?php include_once("footer.php") ?>
