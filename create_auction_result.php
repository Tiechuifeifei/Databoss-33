<?php
ini_set('display_errors', 1);
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
$auctionTitle   = trim($_POST['auctionTitle']);
$auctionDetails = trim($_POST['auctionDetails']);
$startPrice     = $_POST['startPrice'];
$reservePrice   = $_POST['reservePrice'];
$startTime      = $_POST['startTime'];
$endTime        = $_POST['endTime'];

// ----------- STEP 3: Validate fields -----------
$errors = [];

if ($auctionTitle === "")   $errors[] = "Auction title is required.";
if ($auctionDetails === "") $errors[] = "Auction details are required.";


if ($startPrice === "" || !is_numeric($startPrice)) {
    $errors[] = "Start price must be a valid number.";
}

if ($reservePrice === "" || !is_numeric($reservePrice)) {
    $errors[] = "Reserve price must be a valid number.";
}

if ($startTime === "" || $endTime === "") {
    $errors[] = "Start and end time are required.";
} elseif (strtotime($endTime) <= strtotime($startTime)) {
    $errors[] = "End time must be after start time.";
}

if (!empty($errors)) {
    echo "<div class='alert alert-danger'><strong>Errors:</strong><ul>";
    foreach ($errors as $err) echo "<li>$err</li>";
    echo "</ul></div>";
    exit();
}

// ----------- STEP 4: Insert auction -----------
// createAuction($itemId, $startPrice, $reservePrice, $startTime, $endTime)
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
    🎉 Auction successfully created! <br><br>
    <a class='btn btn-success' href='listing.php?auctionId={$newAuctionId}'>
        View your new listing
    </a>
</div>";
?>

</div>

<?php include_once("footer.php") ?>
