<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("db_connect.php");
require_once("Auction_functions.php");
include_once("header.php");
?>

<div class="container my-5">

<?php
// ----------- STEP 1: Validate request method -----------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "<div class='alert alert-danger'>Invalid request.</div>";
    exit();
}

// ----------- STEP 2: Extract POST data -----------
$itemId = intval($_POST['itemId']);
$auctionTitle = trim($_POST['auctionTitle']);
$startPrice   = $_POST['startPrice'];
$reservePrice = $_POST['reservePrice'];
$startTime    = $_POST['startTime'];
$endTime      = $_POST['endTime'];

// ----------- STEP 2B: Check image existence -----------
$db = get_db_connection();   // ← MUST HAVE THIS

$stmt = $db->prepare("SELECT COUNT(*) as cnt 
                      FROM images 
                      WHERE itemId = ? AND isPrimary = 1");
$stmt->bind_param("i", $itemId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row['cnt'] == 0) {
    echo "<div class='alert alert-danger'>
            You must upload a primary image before creating an auction.
          </div>";
    echo "<a class='btn btn-warning mt-3' href='edit_item.php?itemId=$itemId'>
            Upload / Set primary image
          </a>";
    exit();
}

// ----------- STEP 3: Validate fields -----------
$errors = [];

if ($auctionTitle === "") $errors[] = "Auction title is required.";

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
    $minStart = time()+24*60*60;

    if ($startTS < $minStart) {
        $errors[] = "Start time must be from 24 hours onwards";
    }

    if ($endTS<=$startTS) {
        $errors[] = "End time must be after start time.";
    }

    if (($endTS-$startTS)<24*60*60){
        $errors[] = "End time must be at least 24 hours after the start time.";
    }
}

if (!empty($errors)) {
    echo "<div class='alert alert-danger'><strong>Errors:</strong><ul>";
    foreach ($errors as $err) echo "<li>$err</li>";
    echo "</ul></div>";

    // Add return button
    echo "<a class='btn btn-primary mt-3' href='create_auction.php?itemId=$itemId'>
            Go back to Create Auction
          </a>";

    exit();
}


// ----------- STEP 4: Insert auction -----------
$newAuctionId = createAuction(
    $itemId,
    $startPrice,
    $reservePrice,
    $startTime,
    $endTime
);

if (!$newAuctionId) {
    echo "<div class='alert alert-danger'>Failed to create auction. Please try again.</div>";
    exit();
}

// ----------- STEP 5: Success message -----------
echo "
<div class='text-center alert alert-success'>
    Auction successfully created! <br><br>
    <a class='btn btn-success' href='listing.php?auctionId={$newAuctionId}'>
        View your new listing
    </a>
</div>";
?>

</div>

<?php include_once("footer.php") ?>
