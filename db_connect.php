<?php

date_default_timezone_set("Europe/London");

$host = 'localhost';
$dbname = 'auction_website';
$username = 'root';
$password = '';

//create mysqli connections 
$conn = new mysqli($host, $username, $password, $dbname);

//check if connected correctly
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
