<?php
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'self\'; img-src \'self\' data:; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');

if (isset($_GET['product_id'])) {
  $product_id = filter_var($_GET['product_id'], FILTER_VALIDATE_INT);

  if (!$product_id || $product_id < 1) {
    header("Location: index.php");
    exit;
  }

  include('server/connection.php');
  $stmt = $connection->prepare("SELECT * FROM products WHERE product_id = ?");
  $stmt->bind_param("i", $product_id);
  $stmt->execute();

  $product = $stmt->get_result();

  if ($product->num_rows === 0) {
    header("Location: index.php");
    exit;
  }
} else {
  header("Location: index.php");
  exit;
}
?>

<?php include("layouts/header.php"); ?>

<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger text-center"><?= $_SESSION['error'] ?></div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success text-center"><?= $_SESSION['success'] ?></div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- View Product -->
<section class="container padding-ex view-product my-5 pt5">
  <div class="row mt-5">
    <?php while ($row = $product->fetch_assoc()) {
      $escaped_name = htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8');
      $escaped_category = htmlspecialchars($row['product_category'], ENT_QUOTES, 'UTF-8');
      $escaped_description = nl2br(htmlspecialchars($row['product_description'], ENT_QUOTES, 'UTF-8'));
      $escaped_image = htmlspecialchars($row['product_image'], ENT_QUOTES, 'UTF-8');
      $formatted_price = number_format($row['product_price'], 2);
      ?>
      <div class="col-lg-5 col-md-6 col-sm-12">
        <img src="assets/imgs/<?= $escaped_image ?>" class="card-img w-100 pb-1" id="main-img"
          alt="<?= $escaped_name ?>" />
      </div>

      <div class="col-lg-6 col-md-12 col-sm-12">
        <h6><?= $escaped_category ?></h6>
        <h3 class="py-4"><?= $escaped_name ?></h3>
        <h2>₦<?= $formatted_price ?></h2>

        <form method="POST" action="add_to_cart.php">
          <input type="hidden" name="product_id" value="<?= $product_id ?>">
          <input type="number" name="product_quantity" value="1" min="1" class="form-control" required>
          <button class="buy-btn" type="submit" name="add_to_cart">Add to Cart</button>
        </form>

        <h4 class="mt-5 mb-5">Product Details</h4>
        <span><?= $escaped_description ?></span>
      </div>
    <?php } ?>
  </div>
</section>

<!-- Featured products -->
<section id="related">
  <div class="container text-center mt-5">
    <h3>Products like this</h3>
    <hr>
  </div>
  <div class="row mx-auto mt-5 container-fluid">
    <?php
    include('server/get_featured_products.php');

    if ($featured_products->num_rows > 0) {
      while ($row = $featured_products->fetch_assoc()) {
        $rel_id = (int) $row['product_id'];
        $rel_name = htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8');
        $rel_image = htmlspecialchars($row['product_image'], ENT_QUOTES, 'UTF-8');
        $rel_price = number_format($row['product_price'], 2);
        ?>
        <div class="product text-center col-lg-3 col-md-4 col-sm-12">
          <img class="card-img mb-3" src="assets/imgs/<?= $rel_image ?>" alt="<?= $rel_name ?>">
          <h5 class="p-name"><?= $rel_name ?></h5>
          <h4 class="p-price">₦<?= $rel_price ?></h4>
          <a href="view_product.php?product_id=<?= $rel_id ?>">
            <button class="buy-btn btn-hover mx-auto text-decoration-none">Buy Now</button>
          </a>
        </div>
      <?php
      }
    } else {
      echo '<div class="col-12 text-center"><p>No related products found.</p></div>';
    }
    ?>
  </div>
</section>

<?php include("layouts/footer.php"); ?>