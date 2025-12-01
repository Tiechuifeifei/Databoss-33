<?php
require './utilities.php';

$sent = sendEmail('mekialkhan1707@gmail.com', 'Test Email', 'Hello! This is a test from PHPMailer.');
if ($sent) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email. Check email_log.txt for details.";
}
