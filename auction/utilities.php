<?php
/****************************************************
 * utilities.php — drop-in replacement (paste all)
 * 优先使用带点的库名：auction.web.table
 * 若失败，自动回退到：auction_web_table
 ****************************************************/

error_reporting(E_ALL);
ini_set('display_errors', '1');

/** 建立连接（不立即选库），然后用 SQL 的 USE 来选择库 */
function get_db_connection(): mysqli {
  $host = 'localhost';
  $user = 'root';
  $pass = '';

  $db = new mysqli($host, $user, $pass);   // 不在这里传库名
  if ($db->connect_errno) {
    die('DB connect error: ' . $db->connect_error);
  }
  $db->set_charset('utf8mb4');

  // 先尝试带点的库名
  if (!$db->query('USE `auction.web.table`')) {
    // 回退为下划线库名
    if (!$db->query('USE `auction_web_table`')) {
      die('DB select error: neither `auction.web.table` nor `auction_web_table` exists. '
        .'Create one in phpMyAdmin, or rename/copy your database accordingly.');
    }
  }
  return $db;
}

/** 安全转义 */
function h(?string $s): string {
  return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** 显示剩余时间 */
function display_time_remaining(DateInterval $interval): string {
  if ($interval->days == 0 && $interval->h == 0) {
    return $interval->format('%im %Ss');      // < 1 小时
  } elseif ($interval->days == 0) {
    return $interval->format('%hh %im');      // < 1 天
  } else {
    return $interval->format('%ad %hh');      // ≥ 1 天
  }
}

/** 渲染列表项 <li> */
function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time): void {
  if (!($end_time instanceof DateTime)) {
    $end_time = new DateTime($end_time);
  }

  $desc = (string)$desc;
  $desc_short = strlen($desc) > 250 ? substr($desc, 0, 250) . '...' : $desc;

  $bid_label = ((int)$num_bids === 1) ? ' bid' : ' bids';

  $now = new DateTime();
  if ($now > $end_time) {
    $time_remaining = 'This auction has ended';
  } else {
    $time_remaining = display_time_remaining(date_diff($now, $end_time)) . ' remaining';
  }

  echo '
    <li class="list-group-item d-flex justify-content-between">
      <div class="p-2 mr-5">
        <h5><a href="listing.php?item_id=' . (int)$item_id . '">' . h($title) . '</a></h5>'
        . h($desc_short) .
      '</div>
      <div class="text-center text-nowrap">
        <span style="font-size: 1.5em">£' . number_format((float)$price, 2) . '</span><br/>'
        . (int)$num_bids . $bid_label . '<br/>' . h($time_remaining) .
      '</div>
    </li>';
}
