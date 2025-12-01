<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Get database connection //
function get_db_connection(): mysqli {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbName = 'auction_website'; // modular DB


    $db = new mysqli($host, $user, $pass, $dbName);
    if ($db->connect_errno) {
        die('DB connect error: ' . $db->connect_error);
    }

    $db->set_charset('utf8mb4');
    return $db;
}

function sendEmail($to, $subject, $body)
{
    // Always keep a local log for audit/debug
    $logFile = __DIR__ . '/email_log.txt';
    $logMsg  = "----\nTo: {$to}\nSubject: {$subject}\n\n{$body}\n\n";
    file_put_contents($logFile, $logMsg, FILE_APPEND);

    // SMTP configuration - set these as env vars or replace with your values
    $smtpHost    = 'smtp.gmail.com';
    $smtpPort    = 587;
    $smtpUser    = 'ninjaboss1707@gmail.com';
    $smtpPass    = 'imuirngouskdmtaj'; // Gmail app password
    $smtpSecure  = 'tls';
    $fromAddress = 'ninjaboss1707@gmail.com';
    $fromName    = 'Auction Website';



    // Load PHPMailer (install with Composer)
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        file_put_contents($logFile, "PHPMailer autoload not found. Run: composer require phpmailer/phpmailer\n", FILE_APPEND);
        return false;
    }
    require_once $autoload;

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port       = (int)$smtpPort;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom($fromAddress, $fromName);
        $mail->addAddress($to);

        // Content
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->isHTML(false);

        $mail->send();

        file_put_contents($logFile, "Sent: {$to} | Subject: {$subject}\n", FILE_APPEND);
        return true;
    } catch (Exception $e) {
        $err = "PHPMailer Error: {$mail->ErrorInfo} | Exception: {$e->getMessage()}\n";
        file_put_contents($logFile, $err, FILE_APPEND);
        return false;
    }
}


// Escape HTML //
function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Time remaining helper */
function display_time_remaining(DateInterval $interval): string {
    if ($interval->days == 0 && $interval->h == 0) {
        return $interval->format('%im %Ss');
    } elseif ($interval->days == 0) {
        return $interval->format('%hh %im');
    } else {
        return $interval->format('%ad %hh');
    }
}

// Render listing item
// YH DEBUG: We should use auctionId instead of userID
// YH DEBUG: auctionId not auction_id
// YH DEBUG: seperate scheduled/ running and ended auctions
function print_listing_li($auctionId, $title, $desc, $price, $num_bids, $endTime, $startTime, $status, $winnerName)
{
    $now = new DateTime();

    // Determine status text + badge
    if ($status === 'scheduled') {

        $interval = $now->diff($startTime);
        $time_text = "Starts in " . display_time_remaining($interval);
        $badge = "<span class='badge bg-info text-dark'>Not started</span>";

    } elseif ($status === 'running') {

        $interval = $now->diff($endTime);
        $time_text = display_time_remaining($interval) . " remaining";
        $badge = "<span class='badge bg-success'>Running</span>";

    } else { // ended

        $time_text = "Auction ended";
        $badge = "<span class='badge bg-secondary'>Ended</span>";
    }

    echo "<li class='list-group-item'>
            <div class='d-flex justify-content-between'>

                <!-- LEFT -->
                <div>
                    <a href='listing.php?auctionId=$auctionId' class='fw-bold'>$title</a><br>
                    <small class='text-muted'>$desc</small><br>
                    $badge
                </div>

                <!-- RIGHT -->
                <div class='text-end'>
                    <strong>£" . number_format($price, 2) . "</strong><br>
                    <small>$num_bids " . ($num_bids == 1 ? "bid" : "bids") . "</small><br>
                    <small class='text-muted'>$time_text</small><br>";

                    // NEW: only show winner if auction ended
                    if ($status === 'ended') {
                        if ($winnerName) {
                            echo "<small><strong>Winner:</strong> " . htmlspecialchars($winnerName) . "</small>";
                        } else {
                            echo "<small><strong>No bids were placed</strong></small>";
                        }
                    }

    echo       "</div>

            </div>
          </li>";
}
