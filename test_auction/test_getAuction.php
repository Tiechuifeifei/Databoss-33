<?php
require_once("../Auction_functions.php");   
require_once("../db_connect.php");


$auction = getAuctionById(1001);

echo "<pre>";
print_r($auction);
echo "</pre>";
