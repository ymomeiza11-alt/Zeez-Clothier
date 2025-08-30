<?php
include("common.php");
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
include("layouts/header.php");
?>

<!-- Home section -->
<section id="home">
    <div class="container">
        <h5>New Arrivals</h5>
        <h1>Best Prices</h1>
        <p>Zeez Clothier offers the best products for the most affordable prices</p>
        <button class="text-uppercase">
            <a href="shop.php" class="text-decoration-none text-white">Shop Now</a>
        </button>
    </div>
</section>

<!-- Featured products -->
<section id="featured">
    <div class="container text-center mt-5">
        <h3>Featured Products</h3>
        <hr>
        <p>Check out our featured products that are trending this season!</p>
    </div>
    <div class="row mx-auto mt-5 container-fluid">
        <?php
        include('server/get_featured_products.php');

        if ($featured_products->num_rows > 0) {
            while ($row = $featured_products->fetch_assoc()) {
                $product_id = (int) $row['product_id'];
                $product_name = htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8');
                $product_image = htmlspecialchars($row['product_image'], ENT_QUOTES, 'UTF-8');
                $product_price = number_format((float) $row['product_price'], 2);
                ?>
                <div class="product text-center col-lg-3 col-md-4 col-sm-12">
                    <img class="card-img mb-3" src="assets/imgs/<?= $product_image ?>" alt="<?= $product_name ?>" loading="lazy"
                        width="300" height="400">
                    <h5 class="p-name"><?= $product_name ?></h5>
                    <h4 class="p-price">₦<?= $product_price ?></h4>
                    <a href="view_product.php?product_id=<?= $product_id ?>">
                        <button class="buy-btn btn-hover mx-auto text-decoration-none">Buy Now</button>
                    </a>
                </div>
            <?php
            }
        } else {
            echo '<div class="col-12 text-center"><p>No featured products found.</p></div>';
        }
        ?>
    </div>
</section>

<!-- Banner -->
<section id="banner" class="mt-5">
    <div class="container-fluid banner-container text-center pt-5">
        <div class="banner-content">
            <h2 class="banner-title">Exclusive Offer</h2>
            <p class="banner-subtitle">Get 20% off on your first purchase!</p>
            <button class="banner-btn btn-shop-now">Shop Now</button>
        </div>
    </div>
</section>

<!-- Khafan products -->
<section id="khafan">
    <div class="container text-center mt-5">
        <h3>Khaftan</h3>
        <hr>
        <p>Check out our best senators</p>
    </div>
    <div class="row mx-auto mt-5 container-fluid">
        <?php
        include('server/get_khaftan.php');

        if ($khaftan_products->num_rows > 0) {
            while ($row = $khaftan_products->fetch_assoc()) {
                $product_id = (int) $row['product_id'];
                $product_name = htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8');
                $product_image = htmlspecialchars($row['product_image'], ENT_QUOTES, 'UTF-8');
                $product_price = number_format((float) $row['product_price'], 2);
                ?>
                <div class="product text-center col-lg-3 col-md-4 col-sm-12">
                    <img class="card-img mb-3" src="assets/imgs/<?= $product_image ?>" alt="<?= $product_name ?>" loading="lazy"
                        width="300" height="400">
                    <h5 class="p-name"><?= $product_name ?></h5>
                    <h4 class="p-price">₦<?= $product_price ?></h4>
                    <a href="view_product.php?product_id=<?= $product_id ?>">
                        <button class="buy-btn btn-hover mx-auto text-decoration-none">Buy Now</button>
                    </a>
                </div>
            <?php
            }
        } else {
            echo '<div class="col-12 text-center"><p>No khafan products found.</p></div>';
        }
        ?>
    </div>
</section>

<?php
include("layouts/footer.php");
?>