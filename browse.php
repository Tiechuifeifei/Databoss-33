<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db_connect.php");
require_once("utilities.php");
require_once("Auction_functions.php");
require_once("Item_function.php");
require_once("Image_functions.php");
require_once("bid_functions.php");
include_once("header.php");
refreshAllAuctions();
?>

<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/custom_2.css">

<div class="container">

    <h2 class="browse-title">Browse</h2>

    <div id="searchSpecs" class="search-flex-container">
        <form method="get" action="browse.php">
            
          <div class="search-row">
                
            <!--Keyword Search-->
            <div class="search-col wide">
              <label for="keyword" class="browse-label">Search</label>
                <div class="icon-wrapper">
                  <i class="fa fa-search"></i>
                  <input type="text" class="browse-control" id="keyword" name="keyword" placeholder="Search for anything"
                    value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                </div>
            </div>

            <!--Category-->
              <div class="search-col">
                <label for="cat" class="browse-label">Search within</label>
                  <select class="browse-control" id="cat" name="cat">
                    <option selected value="all">All categories</option>
                      <?php 
                        if(isset($conn)) {
                            $sql="SELECT * FROM categories";
                            $result=mysqli_query($conn,$sql);
                            while ($row = mysqli_fetch_assoc($result)) {
                                refreshAuctionStatus($row['auctionId']);
                                echo '<option value="' . $row['categoryId'] . '">' . htmlspecialchars($row['categoryName']) . '</option>';
                            }
                        }
                      ?>
                  </select>
              </div>

                <!--Sort By-->
              <div class="search-col">
                <label class="browse-label" for="order_by">Sort by</label>
                  <select class="browse-control" id="order_by" name="order_by">
                    <option value="pricelow" <?php echo (!isset($_GET['order_by']) || $_GET['order_by']=="pricelow") ? "selected" : ""; ?>> 
                      Price (low to high)</option>
                    <option value="pricehigh" <?php echo (isset($_GET['order_by']) && $_GET['order_by']=="pricehigh") ? "selected" : ""; ?>>
                      Price (high to low)</option>
                    <option value="date" <?php echo (isset($_GET['order_by']) && $_GET['order_by']=="date") ? "selected" : ""; ?>>
                      Soonest expiry</option>
                  </select>
              </div>

                <!--Submit Button-->
              <div class="search-col narrow">
                <button type="submit" class="btn-browse">Search</button>
              </div>

          </div>
      </form>
  </div> 

</div>


</div>

<?php
  //Deal with keywords search situations(if user haven't enter anything)
  if (!isset($_GET['keyword'])) {
    $keyword = "";
  }
  else {
    $keyword = $_GET['keyword'];
  }

//Deal with category filter situations
  if (!isset($_GET['cat'])) {
    $category="all";
  }
  else {
    $category = $_GET['cat'];
  }
  
//Deal with order_by situations, default order is by date
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

 //This is base sql to make foundamental sql
     $base_sql = "
     FROM items i
     JOIN auctions a ON i.itemId=a.itemId
     LEFT JOIN bids b ON a.auctionId=b.auctionId
     JOIN images im ON i.itemId=im.itemId AND im.isPrimary =1
     JOIN categories c ON i.categoryId=c.categoryId
     WHERE 1=1";

     $base_sql .= " AND a.auctionStatus IN ('scheduled', 'running', 'ended')";

     //This is keyword search sql query
     if ($keyword !==""){
      $safe_kw = "%" . $conn->real_escape_string($keyword) . "%";
      $base_sql .= " AND (i.itemName LIKE '$safe_kw' OR i.itemDescription LIKE '$safe_kw')";
     }

     //This is category filter sql query
     if ($category !== "all") {
      $base_sql .= " AND i.categoryId= ".intval($category);
     }
  

    
  //This is for counting numbers of items in single page
  $sql_count = "SELECT COUNT(*) AS total " . $base_sql;
  $count_result = mysqli_query($conn, $sql_count);
  $row_count = mysqli_fetch_assoc($count_result);
  $num_results = (int)$row_count['total'];
  $results_per_page = 10;
  $max_page = max(1,ceil($num_results / $results_per_page));
  $curr_page = max(1, min((int)$curr_page, $max_page));
  $offset = ($curr_page - 1) * $results_per_page;

  // This is for order query

  $sql_item = "
  SELECT
      i.itemId,
      i.itemName,
      i.itemDescription,
      a.auctionId,
      a.auctionEndTime,
      a.auctionStatus,
      a.auctionStartTime,
      a.startPrice,
      im.imageUrl,
      COUNT(b.bidId) AS num_bids,
      IFNULL(MAX(b.bidPrice), a.startPrice) AS max_bid
  " 
  . $base_sql .
  "
  GROUP BY i.itemId
  ";
  
  
  if ($ordering === "pricelow") {
      $sql_item .= " ORDER BY max_bid ASC";
  } elseif ($ordering === "pricehigh") {
      $sql_item .= " ORDER BY max_bid DESC";
  } else {
      $sql_item .= " ORDER BY a.auctionEndTime ASC";
  }

  $sql_item .= " LIMIT $results_per_page OFFSET $offset";
  $result_item = mysqli_query($conn, $sql_item);
  if (!$result_item){
    die("SQL error:" . mysqli_error($conn) ."<br>Query was: " . $sql_item);
  };

?>

<div class="listing-card">
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
    refreshAuctionStatus($row['auctionId']);
    $auction_id = $row["auctionId"];   
    $title = $row["itemName"];
    
    $desc = $row["itemDescription"];

    $highestBid = getHighestBidForAuction($auction_id);

    if ($row["auctionStatus"] === 'ended') {
        //No bids at all, hide
        if (!$highestBid) continue;

        //Has bids but below reserved price, hide
        if ($highestBid['bidPrice'] < $row['startPrice']) continue;
    }
    

    //find the highest bid
    $highestBid = getHighestBidForAuction($auction_id);

    //find the final price 
    $price = $highestBid ? $highestBid['bidPrice'] : $row["startPrice"];

    //find the winner
    $winnerName = null;
    if ($highestBid) {
        $winnerId = $highestBid['buyerId'];
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT userName FROM users WHERE userId = ?");
        $stmt->bind_param("i", $winnerId);
        $stmt->execute();
        $winnerRow = $stmt->get_result()->fetch_assoc();
        $winnerName = $winnerRow['userName'] ?? null;
    }

    $num_bids = $row["num_bids"];
    $end_time = new DateTime($row["auctionEndTime"]);
    $start_time = new DateTime($row["auctionStartTime"]);
    $status = $row["auctionStatus"];
    print_listing_li(
      $auction_id,
      $title,
      $desc,
      $price,
      $num_bids,
      $end_time,
      $start_time,
      $status,
      $winnerName,
      
  );
  
}

  ?>

</ul>


<nav aria-label="Search results pages" class="mt-5 browse-pagination">
  <ul class="pagination justify-content-center">
  
<?php

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

      echo('
    <li class="page-item active">');
    }
    else {

      echo('
    <li class="page-item">');
    }
    

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
