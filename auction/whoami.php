<?php
session_start();

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
  echo "❌ Not logged in.\n";
  echo "Session contents:\n";
  print_r($_SESSION);
  exit;
}

echo "✅ You are logged in.\n";
echo "user_id = " . $_SESSION['user_id'] . "\n";
if (isset($_SESSION['role'])) {
  echo "role = " . $_SESSION['role'] . "\n";
}
if (isset($_SESSION['userEmail'])) {
  echo "email = " . $_SESSION['userEmail'] . "\n";
}
echo "\nFull session dump:\n";
print_r($_SESSION);
