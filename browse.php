<?php include_once("header.php")?>
<?php require("utilities.php")?>

<div class="container">

<h2 class="my-3">Browse listings</h2>

<div id="searchSpecs">
<form method="get" action="browse.php">
  <div class="row">
    <div class="col-md-5 pr-0">
      <div class="form-group">
        <label for="keyword" class="sr-only">Search keyword:</label>
	    <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-transparent pr-0 text-muted">
              <i class="fa fa-search"></i>
            </span>
          </div>
          <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search for anything"
          value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
        </div>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" id="cat">
          <option selected value="all">All categories</option>
          <?php 
          $sql="SELECT * FROM categories";
          $result=mysqli_query($conn,$sql);
          while ($row = mysqli_fetch_assoc($result)) {
          echo '<option value="' . $row['categoryId'] . '">'
            . htmlspecialchars($row['categoryName']) .
            '</option>';
          }
          ?>
        </select>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-inline">
        <label class="mx-2" for="order_by">Sort by:</label>
        <select class="form-control" id="order_by" name="order_by">
          <option value="pricelow"
          <?php echo (!isset($_GET['order_by']) || $_GET['order_by']=="pricelow") ? "selected" : ""; ?>> 
            Price (low to high)</option>
          <option value="pricehigh"
          <?php echo (isset($_GET['order_by']) && $_GET['order_by']=="pricehigh") ? "selected" : ""; ?>>
          Price (high to low)</option>
          <option value="date" <?php echo (isset($_GET['order_by']) && $_GET['order_by']=="date") ? "selected" : ""; ?>>
            Soonest expiry</option>
        </select>
      </div>
    </div>
    <div class="col-md-1 px-0">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
</div> 


</div>

<?php
  // Retrieve these from the URL
  if (!isset($_GET['keyword'])) {
    $keyword = "";
  }
  else {
    $keyword = $_GET['keyword'];
  }

  if (!isset($_GET['cat'])) {
    $category="all";
  }
  else {
    $category = $_GET['cat'];
  }
  
  if (!isset($_GET['order_by'])) {
    $ordering = "date";
  }
  else {
    $ordering = $_GET['order_by'];
  }
  
  if (!isset($_GET['page'])) {
    $curr_page = 1;
  }
  else {
    $curr_page = $_GET['page'];
  }

    // This is base sql to make foundamental sql
     $base_sql = "
     FROM items i
     JOIN auctions a ON i.itemId=a.itemId
     LEFT JOIN bids b ON a.auctionId=b.auctionId
     JOIN images im ON i.itemId=im.itemId AND im.isPrimary =1
     JOIN categories c ON i.categoryId=c.categoryId
     WHERE 1=1";

          // This is keyword search sql query
     if ($keyword !==""){
      $safe_kw = "%" . $conn->real_escape_string($keyword) . "%";
      $base_sql .= " AND (i.itemName LIKE '$safe_kw' OR i.itemDescription LIKE '$safe_kw')";
     }

     // This is category filter sql query
     if ($category !== "all") {
      $base_sql .= " AND i.categoryId= ".intval($category);
     }
  //this is for counting number of item in single page
  $sql_count = "SELECT COUNT(*) AS total " . $base_sql;
  $count_result = mysqli_query($conn, $sql_count);
  $row_count = mysqli_fetch_assoc($count_result);
  $num_results = (int)$row_count['total'];
  $results_per_page = 10;
  $max_page = max(1,ceil($num_results / $results_per_page));
  $curr_page = max(1, min((int)$curr_page, $max_page));
  $offset = ($curr_page - 1) * $results_per_page;

   // This is for order by query
  $sql_item = "SELECT
    i.itemId,
    i.itemName,
    i.itemDescription,
    a.auctionEndTime,
    a.startPrice,
    im.imageUrl,
    COUNT(b.bidId) AS num_bids
    " . $base_sql . "
    GROUP BY i.itemId";
  
  if ($ordering ==="pricelow"){
    $sql_item .= " ORDER BY a.startPrice ASC";
  } 
  elseif ($ordering === "pricehigh"){
    $sql_item .= " ORDER BY a.startPrice DESC";
  }
  else {
    $sql_item .= " ORDER BY a.auctionEndTime";
  }

  $sql_item .= " LIMIT $results_per_page OFFSET $offset";
  $result_item = mysqli_query($conn, $sql_item);
  if (!$result_item){
    die("SQL error:" . mysqli_error($conn) ."<br>Query was: " . $sql_item);
  };
?>

<div class="container mt-5">
<!-- This is for printing text to notify there is no result found -->
  <?php
  if ($num_results == 0) {
    echo "<div>No Listing Found</div>";
  }
  ?>

<ul class="list-group">
<!--This is for printing the list group-->
  <?php 
  while ($row = mysqli_fetch_assoc($result_item)) {
    $item_id = $row["itemId"];
    $title = $row["itemName"];
    $desc = $row["itemDescription"];
    $price = $row["startPrice"];
    $num_bids = $row["num_bids"];
    $end_time = new DateTime($row["auctionEndTime"]);

    print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time);
  }
  ?>

</ul>

<!-- Pagination for results listings -->
<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">
  
<?php

  // Copy any currently-set GET variables to the URL.
  $querystring = "";
  foreach ($_GET as $key => $value) {
    if ($key != "page") {
      $querystring .= "$key=$value&amp;";
    }
  }
  
  $high_page_boost = max(3 - $curr_page, 0);
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);
  
  if ($curr_page != 1) {
    echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
  }
    
  for ($i = $low_page; $i <= $high_page; $i++) {
    if ($i == $curr_page) {
      // Highlight the link
      echo('
    <li class="page-item active">');
    }
    else {
      // Non-highlighted link
      echo('
    <li class="page-item">');
    }
    
    // Do this in any case
    echo('
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }
  
  if ($curr_page != $max_page) {
    echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
  }
?>

  </ul>
</nav>


</div>



<?php include_once("footer.php")?>