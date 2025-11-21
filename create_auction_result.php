<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 引入数据库连接
require_once("db_connect.php");
require_once("Auction_functions.php");
include_once("header.php");
?>


<div class="container my-5">

<?php

// This function takes the form data and adds the new auction to the database.
/* TODO #1: Connect to MySQL database (perhaps by requiring a file that
            already does this). */
// ----------- STEP 1: Check if form submitted -----------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "<div class='alert alert-danger'>Invalid request.</div>";
    exit();
}
/* TODO #2: Extract form data into variables. Because the form was a 'post'
            form, its data can be accessed via $POST['auctionTitle'], 
            $POST['auctionDetails'], etc. Perform checking on the data to
            make sure it can be inserted into the database. If there is an
            issue, give some semi-helpful feedback to user. */
// ----------- STEP 2: Extract POST form data -----------
$auctionTitle   = trim($_POST['auctionTitle']);
$auctionDetails = trim($_POST['auctionDetails']);
$auctionCategory = $_POST['auctionCategory'];
$startPrice     = $_POST['startPrice'];
$reservePrice   = $_POST['reservePrice'];
$startTime      = $_POST['startTime'];
$endTime        = $_POST['endTime'];
// TEMP: Because Item module is not ready yet
// We hardcode an itemId = 1 for now. 
// Later this will come from Create Item page. =====>need come from user 
// Irene：看一下这里的item
$itemId = 1;

/* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */
// ----------- STEP 3: Basic Validation -----------
$errors = [];

if (empty($auctionTitle)) {
    $errors[] = "Auction title is required.";
}

if (empty($auctionDetails)) {
    $errors[] = "Auction details are required.";
}

if (empty($startPrice) || !is_numeric($startPrice)) {
    $errors[] = "Start price must be a valid number.";
}

if (empty($reservePrice) || !is_numeric($reservePrice)) {
    $errors[] = "Reserve price must be a valid number.";
}

if (empty($startTime) || empty($endTime)) {
    $errors[] = "Start and end time are required.";
} elseif (strtotime($endTime) <= strtotime($startTime)) {
    $errors[] = "End time must be after start time.";
}

// If errors, show them and stop
if (!empty($errors)) {
    echo "<div class='alert alert-danger'><strong>Errors:</strong><ul>";
    foreach ($errors as $err) {
        echo "<li>$err</li>";
    }
    echo "</ul></div>";
    exit();
}

// ----------- STEP 4: Insert Auction into DB -----------
$newAuctionId = createAuction(
    $itemId,
    $startPrice,
    $reservePrice,
    $startTime,
    $endTime
);

// createAuction returns false on failure
if (!$newAuctionId) {
    echo "<div class='alert alert-danger'>Failed to create auction. Please try again.</div>";
    exit();
}

// ----------- STEP 5: Success message -----------
echo "
<div class='text-center alert alert-success'>
    Auction successfully created! <br>
    <a href='listing.php?auctionId={$newAuctionId}'>View your new listing</a>
</div>";          

// If all is successful, let user know.
echo('<div class="text-center">Auction successfully created! <a href="FIXME">View your new listing.</a></div>');


?>

</div>


<?php include_once("footer.php")?>