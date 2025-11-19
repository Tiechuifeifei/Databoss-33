<?php
$host = 'localhost';
$dbname = 'auction_website';   // 数据库名称 name
$username = 'root';
$password = '';               // XAMPP 

// create mysqli connections 
$conn = new mysqli($host, $username, $password, $dbname);

// check if connected correctly
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
