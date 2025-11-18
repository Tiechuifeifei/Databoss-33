<?php
session_start();

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_SESSION['userId'])) {
    echo "❌ Not logged in.\n";
    echo "Session contents:\n";
    print_r($_SESSION);
    exit;
}

echo "✅ You are logged in.\n";
echo "userId = " . $_SESSION['userId'] . "\n";
if (isset($_SESSION['username'])) {
    echo "username = " . $_SESSION['username'] . "\n";
}
if (isset($_SESSION['role'])) {
    echo "role = " . $_SESSION['role'] . "\n";
}
echo "\nFull session dump:\n";
print_r($_SESSION);
