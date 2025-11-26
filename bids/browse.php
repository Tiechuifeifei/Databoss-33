<?php
// browse.php — 使用真实数据库数据（auctions + items + bids）

include_once("header.php");
require_once("utilities.php");
require_once("bids_functions.php");

// 读取搜索/排序参数（现在可以先不用，预留在这）
$keyword   = isset($_GET['keyword'])   ? trim($_GET['keyword'])   : '';
$category  = isset($_GET['cat'])       ? $_GET['cat']             : 'all';
$order_by  = isset($_GET['order_by'])  ? $_GET['order_by']        : 'pricelow';

// 连接数据库
$db = get_db_connection();

// 基础 SQL：从 auctions + items 取真实数据
$sql = "
    SELECT 
        a.auctionId,
        a.itemId,
        a.auctionStartTime,
        a.auctionEndTime,
        a.auctionStatus,
        a.startPrice,
        i.itemName,
        i.itemDescription
    FROM auctions AS a
    JOIN items    AS i ON a.itemId = i.itemId
    ORDER BY a.auctionId ASC
";

$result = $db->query($sql);
$auctions = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $auctions[] = $row;
    }
}
?>

<div class="container">

    <h2 class="my-3">Browse listings</h2>

    <!-- 搜索栏（样式先保留，将来你们想实现再说，现在不影响功能） -->
    <div id="searchSpecs">
        <form method="get" action="browse.php">
            <div class="row">
                <div class="col-md-5 pr-0">
                    <div class="form-group">
                        <label for="keyword" class="sr-only">Search keyword:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent pr-0 text-muted">
                                  <i class="fa fa-search"></i>
                                </span>
                            </div>
                            <input type="text"
                                   class="form-control border-left-0"
                                   id="keyword"
                                   name="keyword"
                                   placeholder="Search for anything"
                                   value="<?php echo h($keyword); ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 pr-0">
                    <div class="form-group">
                        <label for="cat" class="sr-only">Search within:</label>
                        <select class="form-control" id="cat" name="cat">
                            <option value="all" <?php if ($category === 'all') echo 'selected'; ?>>All categories</option>
                            <!-- 将来想按 categoryId 过滤，可以在这里填选项 -->
                        </select>
                    </div>
                </div>
                <div class="col-md-3 pr-0">
                    <div class="form-inline">
                        <label class="mx-2" for="order_by">Sort by:</label>
                        <select class="form-control" id="order_by" name="order_by">
                            <option value="pricelow"  <?php if ($order_by === 'pricelow')  echo 'selected'; ?>>Price (low to high)</option>
                            <option value="pricehigh" <?php if ($order_by === 'pricehigh') echo 'selected'; ?>>Price (high to low)</option>
                            <option value="date"      <?php if ($order_by === 'date')      echo 'selected'; ?>>Soonest expiry</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-1 px-0">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>
    </div><!-- end search specs bar -->

</div>

<div class="container mt-5">

<ul class="list-group">

<?php if (empty($auctions)): ?>

    <li class="list-group-item">
        <p class="mb-0">No auctions found.</p>
    </li>

<?php else: ?>

    <?php foreach ($auctions as $a): ?>

        <?php
        $auctionId   = (int)$a['auctionId'];
        $itemName    = $a['itemName'];
        $description = $a['itemDescription'];
        $startPrice  = (float)$a['startPrice'];

        // 用你的 bids_functions 来算当前最高价和出价次数
        $highestBidRow = getHighestBidForAuction($auctionId);
        $bids          = getBidsByAuctionId($auctionId);
        $numBids       = is_array($bids) ? count($bids) : 0;

        // 如果有出价，用最高出价；否则用起拍价
        if ($highestBidRow && $numBids > 0) {
            $displayPrice = (float)$highestBidRow['bidPrice'];
            $priceLabel   = 'Current highest bid';
        } else {
            $displayPrice = $startPrice;
            $priceLabel   = 'Starting price';
        }

        // 判断是否已结束（简单用时间 + status）
        $now     = new DateTime();
        $endTime = new DateTime($a['auctionEndTime']);
        $isEnded = ($now >= $endTime) || ($a['auctionStatus'] !== 'active');
        ?>

        <li class="list-group-item">
            <div class="row">
                <div class="col-md-8">
                    <!-- 用真实 auctionId 跳转 -->
                    <a href="listing.php?auctionId=<?php echo $auctionId; ?>">
                        <h5><?php echo h($itemName); ?></h5>
                    </a>
                    <!-- 显示 Auction ID，方便检查 -->
                    <p class="text-muted mb-1">
                        Auction ID: <?php echo $auctionId; ?>
                    </p>
                    <p><?php echo h($description); ?></p>
                </div>

                <div class="col-md-4 text-right">
                    <h5>£<?php echo number_format($displayPrice, 2); ?></h5>
                    <p class="mb-1 text-muted">
                        <?php echo h($priceLabel); ?>
                    </p>
                    <p class="mb-1">
                        <?php echo $numBids; ?> bids
                    </p>
                    <p>
                        <?php if ($isEnded): ?>
                            This auction has ended
                        <?php else: ?>
                            Auction is active
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </li>

    <?php endforeach; ?>

<?php endif; ?>

</ul>

</div>

<?php include_once("footer.php"); ?>
