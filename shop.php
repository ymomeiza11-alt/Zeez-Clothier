<?php
include("server/connection.php");

$products = [];
$total_records = 0;
$total_no_of_pages = 0;

$allowed_categories = ['all', 'khaftan', 'casual wear', 'agbada'];
$category = $_GET['category'] ?? 'all';
if (!in_array($category, $allowed_categories)) {
    $category = 'all';
}

$price = floatval($_GET['price'] ?? 999999);
$page_no = intval($_GET['page_no'] ?? 1);
$page_no = max(1, $page_no);

$query = "SELECT * FROM products";
$count_query = "SELECT COUNT(*) AS total_records FROM products";
$where = [];
$params = [];
$types = "";

if ($category !== 'all') {
    $where[] = "product_category = ?";
    $params[] = $category;
    $types .= "s";
}

$where[] = "product_price <= ?";
$params[] = $price;
$types .= "d";

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
    $count_query .= " WHERE " . implode(" AND ", $where);
}

$stmt = $connection->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total_records);
$stmt->fetch();
$stmt->close();

$total_records_per_page = 9;
$offset = ($page_no - 1) * $total_records_per_page;
$total_no_of_pages = ceil($total_records / $total_records_per_page);

if ($page_no > $total_no_of_pages && $total_no_of_pages > 0) {
    $page_no = $total_no_of_pages;
    $offset = ($page_no - 1) * $total_records_per_page;
}

$query .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $total_records_per_page;
$types .= "ii";

$stmt = $connection->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();
?>

<?php include("layouts/header.php"); ?>

<div class="container my-5 py-5">
    <div class="row">
        <div class="col-lg-3 col-md-4 my-4">
            <section id="search" class="padding-ex">
                <div class="">
                    <p class="text-center">Search Products</p>
                    <hr class="mx-auto">
                </div>
                <form action="shop.php" method="GET">
                    <div class="mb-3">
                        <p>Category</p>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" value="all" name="category" id="category1"
                                <?= $category === 'all' ? 'checked' : '' ?>>
                            <label for="category1" class="form-check-label">All</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" value="khaftan" name="category" id="category2"
                                <?= $category === 'khaftan' ? 'checked' : '' ?>>
                            <label for="category2" class="form-check-label">Khaftan</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" value="casual wear" name="category"
                                id="category3" <?= $category === 'casual wear' ? 'checked' : '' ?>>
                            <label for="category3" class="form-check-label">Casual Wear</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" value="agbada" name="category" id="category4"
                                <?= $category === 'agbada' ? 'checked' : '' ?>>
                            <label for="category4" class="form-check-label">Agbada</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <p>Price</p>
                        <div class="d-flex justify-content-between">
                            <span>₦10,000</span>
                            <span>₦999,999</span>
                        </div>
                        <input type="range" class="form-range" name="price" value="<?= htmlspecialchars($price) ?>"
                            min="10000" max="999999" id="priceSlider">
                        <div class="text-center mt-2">
                            Selected: <strong>₦<span id="priceValue"><?= number_format($price, 2) ?></span></strong>
                        </div>
                    </div>
                    <div class="form-group my-3">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </section>
        </div>

        <div class="col-lg-9 col-md-8">
            <section id="featured" class="padding-ex">
                <div class="text-center mt-2">
                    <h3>Products</h3>
                    <hr class="mx-auto">
                    <p>Check out our diverse range of products!</p>
                </div>
                <div class="row mt-4">
                    <?php if ($products->num_rows > 0): ?>
                        <?php while ($row = $products->fetch_assoc()): ?>
                            <div class="product text-center col-lg-4 col-md-6 col-sm-12 mb-4">
                                <a href="view_product.php?product_id=<?= $row['product_id'] ?>">
                                    <img class="card-img mb-3" src="assets/imgs/<?= htmlspecialchars($row['product_image']) ?>"
                                        alt="<?= htmlspecialchars($row['product_name']) ?>">
                                </a>
                                <div class="star">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <a class="text-decoration-none text-black p-name"
                                    href="view_product.php?product_id=<?= $row['product_id'] ?>">
                                    <h5><?= htmlspecialchars($row['product_name']) ?></h5>
                                </a>
                                <h4 class="p-price">₦<?= number_format($row['product_price'], 2) ?></h4>
                                <form action="view_product.php" method="GET">
                                    <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                    <button type="submit" class="buy-btn btn-hover mx-auto">Buy Now</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <h4>No products found matching your criteria</h4>
                            <p>Try adjusting your filters</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($total_no_of_pages > 1): ?>
                    <nav aria-label="Page pagination example" class="mx-auto">
                        <ul class="pagination mt-5 mx-auto">
                            <li class="page-item <?= $page_no <= 1 ? 'disabled' : '' ?>">
                                <a href="?page_no=<?= $page_no - 1 ?>&category=<?= urlencode($category) ?>&price=<?= $price ?>"
                                    class="page-link">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= min(3, $total_no_of_pages); $i++): ?>
                                <li class="page-item <?= $page_no == $i ? 'active' : '' ?>">
                                    <a href="?page_no=<?= $i ?>&category=<?= urlencode($category) ?>&price=<?= $price ?>"
                                        class="page-link"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($total_no_of_pages > 3): ?>
                                <li class="page-item disabled"><a href="#" class="page-link">...</a></li>
                                <li class="page-item <?= $page_no == $total_no_of_pages ? 'active' : '' ?>">
                                    <a href="?page_no=<?= $total_no_of_pages ?>&category=<?= urlencode($category) ?>&price=<?= $price ?>"
                                        class="page-link"><?= $total_no_of_pages ?></a>
                                </li>
                            <?php endif; ?>

                            <li class="page-item <?= $page_no >= $total_no_of_pages ? 'disabled' : '' ?>">
                                <a href="?page_no=<?= $page_no + 1 ?>&category=<?= urlencode($category) ?>&price=<?= $price ?>"
                                    class="page-link">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<script>
    const priceSlider = document.getElementById('priceSlider');
    const priceValue = document.getElementById('priceValue');

    priceSlider.addEventListener('input', function () {
        const value = parseFloat(this.value).toFixed(2);
        priceValue.textContent = new Intl.NumberFormat().format(value);
    });
</script>

<?php include("layouts/footer.php"); ?>