<?php require_once("header.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/utilities.php';
require_once __DIR__.'/db_connect.php';

$currentUrl = $_SERVER['REQUEST_URI'] ?? 'index.php';

$category = $_GET["categoryId"] ?? null;

$sql= "SELECT a.auctionId,
              a.auctionEndTime,
              a.auctionStartTime,
              a.startPrice,
              a.auctionStatus,
              i.itemId,
              i.itemName,
              i.itemDescription,
              im.imageUrl,
              c.categoryId,
              c.categoryName
        FROM auctions a
        JOIN items i ON a.itemId=i.itemId
        JOIN images im ON i.itemId=im.ItemId
        JOIN categories c on i.categoryId=c.categoryId
        WHERE im.isPrimary=1
          AND a.auctionStatus IN ('scheduled','running')
        ORDER BY RAND()
        LIMIT 8";
        
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

require_once 'header.php';
?>

<link rel="stylesheet" href="custom.css">

<main>
  <section>
  <!--main picture secton-->
  <section class="hero-banner">
    <div class="hero-content">
      <h1 class="hero-title display-5 fw-bold">Auction Website</h1>
    
    <a href="browse.php" class="btn btn-light btn-lg mt-3">
      Explore More
    </a>
    </div>
  </section>
  
  <!--recommendation section-->
  <section class="section section-products py-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-baseline mb-4 section-header">
        <h2 class="mb-0">Recommendations</h2>
      </div>

    <!--This is auction recommendations-->
    <div class="row auction-grid">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-12 col-sm-6 col-md-3 mb-4">
              
            <article class="card item_card h-100">
                <a href="listing.php?item_id=<?= urlencode($row['itemId']); ?>"
                class="text-decoration-none text-dark">

            <div class="product-image-wrapper">
                <img src="<?=htmlspecialchars($row['imageUrl'])?>"
                class="card-img-top item-image"
                alt="<?= htmlspecialchars($row['itemName'])?>">
            </div>

                <div class="card-body">
                  <h3 class="item-title">
                    <?=htmlspecialchars($row['itemName'])?>
                  </h3>
                  <p class="item-meta">Auction Ends:
                    <?=htmlspecialchars($row['auctionEndTime'])?>
                  </p>
                  <p class="card-text mb-1 small text-muted">Start Price:£
                    <?=htmlspecialchars($row['startPrice'])?>
                  </p>
                </div>
              </a>
              </article>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No active auctions at the moment.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

</main>

<?php require_once 'footer.php'; ?>
  