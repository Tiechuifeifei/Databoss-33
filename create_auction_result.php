<link rel="stylesheet" href="css/custom_2.css">

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db_connect.php");
require_once("Auction_functions.php");
include_once("header.php");
?>

<div class="form-container" style="margin-top:50px;">

<?php
//Validate request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "<div class='message-box error'>
    <h3>Invalid request.</h3>
    </div>";
    echo"</div>";
    include_once("footer.php");
    exit();
}

//Extract POST data
$itemId = intval($_POST['itemId']);
$startPrice   = $_POST['startPrice'];
$reservePrice = $_POST['reservePrice'];
$startTime    = $_POST['startTime'];
$endTime      = $_POST['endTime'];

//Check image existence
$db = get_db_connection();   // ← MUST HAVE THIS

$stmt = $db->prepare("SELECT COUNT(*) as cnt 
                      FROM images 
                      WHERE itemId = ? AND isPrimary = 1");
$stmt->bind_param("i", $itemId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row['cnt'] == 0) {
    echo "<div class='message-box error'>
            <h3>You must upload a primary image before creating an auction.</h3>
          </div>";

    echo "<a class='btn-link-style' href='edit_item.php?itemId=$itemId'>
            Upload / Set primary image
          </a>";
    exit();
}

//Validate fields
$errors = [];

if ($startPrice === "" || !is_numeric($startPrice)) {
    $errors[] = "Start price must be a valid number.";
}

if ($reservePrice === "" || !is_numeric($reservePrice)) {
    $errors[] = "Reserve price must be a valid number.";
}

if ($reservePrice<$startPrice){
    $errors[] = "Reserve price must higher or equal to start price.";
}

if ($startTime === "" || $endTime === "") {
    $errors[] = "Start and end time are required.";
} else {
 $startTS=strtotime($startTime);
    $endTS=strtotime($endTime);
    $tomorrowTS = strtotime('tomorrow');
    $now=time();

    if ($startTS < $now) {
        $errors[] = "Invalid Time, please choose again";
    }

    if ($endTS<=$startTS) {
        $errors[] = "End time must be after start time.";
    }
}

if (!empty($errors)) {
    echo "<div class='message-box error'";
    echo "<h1 class='result-title'>Errors:</h1><ul>";
    echo "<ul class='error-list'";
    foreach ($errors as $err) echo "<li>$err</li>";
    echo "</ul>";
    echo "</div>";

    // Add return button
    echo "<a class='btn-link-style' href='create_auction.php?itemId=$itemId'
    style='justify-content:center;'>
            Go back to Create Auction</a>";
    echo"</div>";

    echo"</div>";
    include_once("footer.php");

    exit();
}


//Insert auction
$newAuctionId = createAuction(
    $itemId,
    $startPrice,
    $reservePrice,
    $startTime,
    $endTime
);

if (!$newAuctionId) {
    echo "<div class='message-box error'>Failed to create auction. Please try again.</div>";
    echo "</div>";
    include_once("footer.php");
    exit();
}
?>

<!--create successully-->
<div class="message-box success">
        <h3 class="result-title">Auction Created!</h3>
        <p style="color: #666;">Your item is now live on the marketplace.</p>
    </div>

    <div class="form-footer" style="justify-content: center;">
        <a class="btn-link-style primary" href="listing.php?auctionId=<?php echo $newAuctionId; ?>">
            View Listing
        </a>
    </div>

</div>

<?php include_once("footer.php") ?>
