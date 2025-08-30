<?php 
include('header.php');

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

try {
    $products = [];
    $total_no_of_pages = 1;
    $page_no = isset($_GET["page_no"]) ? (int)$_GET["page_no"] : 1;
    $page_no = max(1, $page_no);

    if(isset($_POST["search"])) {
        $category = isset($_POST['category']) ? $connection->real_escape_string($_POST['category']) : 'all';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 999999;

        $query = "SELECT * FROM products";
        $count_query = "SELECT COUNT(*) AS total_records FROM products";
        $where = [];
        $params = [];
        $types = "";
        
        if($category !== 'all') {
            $where[] = "product_category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        $where[] = "product_price <= ?";
        $params[] = $price;
        $types .= "d";

        if(!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
            $count_query .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $connection->prepare($count_query);
        if(!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $stmt->bind_result($total_records);
        $stmt->fetch();
        $stmt->close();

        $total_records_per_page = 9;
        $offset = ($page_no - 1) * $total_records_per_page;
        $total_no_of_pages = max(1, ceil($total_records / $total_records_per_page));

        $query .= " LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $total_records_per_page;
        $types .= "ii";

        $stmt = $connection->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC); 
    } else {
        $stmt = $connection->prepare("SELECT COUNT(*) AS total_records FROM products");
        $stmt->execute();
        $stmt->bind_result($total_records);
        $stmt->fetch();
        $stmt->close();

        $total_records_per_page = 9;
        $offset = ($page_no - 1) * $total_records_per_page;
        $total_no_of_pages = max(1, ceil($total_records / $total_records_per_page));

        $stmt = $connection->prepare("SELECT * FROM products LIMIT ?, ?");
        $stmt->bind_param("ii", $offset, $total_records_per_page);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC); 
    }
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while processing your request. Please try again later.");
}
?>

<div class="container-fluid">
    <div class="row" style="min-height: 1000px;">
        <?php include('sidemenu.php'); ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <!-- Your buttons here -->
                    </div>
                </div>
            </div>

            <h2>Products</h2>
            <!-- edit messages -->
            <?php if(isset($_GET['edit_success_message'])) { ?>
                <div class="alert text-center alert-success">
                    <?php echo $_GET['edit_success_message']; ?>
                </div>
            <?php } ?>
            <?php if(isset($_GET['edit_failure_message'])) { ?>
                <div class="alert text-center alert-danger">
                    <?php echo $_GET['edit_failure_message']; ?>
                </div>
            <?php } ?>
            <!-- delete messages -->
            <?php if(isset($_GET['delete_success_message'])) { ?>
                <div class="alert text-center alert-success">
                    <?php echo $_GET['delete_success_message']; ?>
                </div>
            <?php } ?>
            <?php if(isset($_GET['delete_failure_message'])) { ?>
                <div class="alert text-center alert-danger">
                    <?php echo $_GET['delete_failure_message']; ?>
                </div>
            <?php } ?>
            <!-- Add Product messages -->
            <?php if(isset($_GET['product_created'])) { ?>
                <div class="alert text-center alert-success">
                    <?php echo $_GET['product_created']; ?>
                </div>
            <?php } ?>
            <?php if(isset($_GET['prodCreate_failed'])) { ?>
                <div class="alert text-center alert-danger">
                    <?php echo $_GET['prodCreate_failed']; ?>
                </div>
            <?php } ?>
            <!-- Update image messages -->
            <?php if(isset($_GET['image_updated'])) { ?>
                <div class="alert text-center alert-success">
                    <?php echo $_GET['image_updated']; ?>
                </div>
            <?php } ?>
            <?php if(isset($_GET['imgUpdate_failed'])) { ?>
                <div class="alert text-center alert-danger">
                    <?php echo $_GET['imgUpdate_failed']; ?>
                </div>
            <?php } ?>


            <div class="table-responsive">
                <table class="table table-stripped table-sm">
                    <thead>
                        <th scope="col">Product ID</th>
                        <th scope="col">Product image</th>
                        <th scope="col">Product Name</th>
                        <th scope="col">Product Price</th>
                        <th scope="col">Product Category</th>
                        <th scope="col">Product Offer</th>
                        <th scope="col">Update Image</th>
                        <th scope="col">Edit</th>
                        <th scope="col">Delete</th>
                    </thead>
                    <tbody>
                        <?php foreach($products as $product) { ?>
                            <tr>
                                <td><?php echo $product['product_id']; ?></td>
                                <td><img src="<?php echo "../assets/imgs/" . $product['product_image']; ?>" style="width: 70px; height: 70px;" alt="<?php echo $product['product_name']; ?>" width="50"></td>
                                <td><?php echo $product['product_name']; ?></td>
                                <td><?php echo "₦" .$product['product_price']; ?></td>
                                <td><?php echo $product['product_category']; ?></td>
                                <td><?php echo $product['product_special_offer']; ?></td>
                                <td>
                                    <a href="edit_image.php?product_id=<?php echo $product['product_id']; ?>&product_name=<?php echo urlencode($product['product_name']); ?>" 
                                    class="btn btn-sm btn-warning">
                                        Update Image
                                    </a>
                                </td>
                                <td>
                                    <a href="javascript:void(0);" 
                                        onclick="window.location.href='edit_product.php?product_id=<?php echo $product['product_id']; ?>';"
                                        class="btn btn-sm btn-primary">
                                        Edit
                                    </a>
                                </td>
                                <td><a href="delete_product.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger">Delete</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <nav aria-label="Page pagination example" class="mx-auto">
                <ul class="pagination mt-5 mx-auto">
                    <li class="page-item <?php if($page_no<=1){echo 'disabled';}?>">
                    <a href="<?php if($page_no<=1){echo "#";}else{echo "?page_no=".($page_no-1);}?>" class="page-link">Previous</a>
                    </li>
                    <li class="page-item"><a href="?page_no=1" class="page-link">1</a></li>
                    <li class="page-item"><a href="?page_no=2" class="page-link">2</a></li>
                    <?php if($page_no>=3){?>
                    <li class="page-item"><a href="#" class="page-link">...</a></li>
                    <li class="page-item"><a href="<?php echo "?page_no=".$page_no; ?>" class="page-link"><?php echo $page_no; ?></a></li>
                    <?php }?>
                    <li class="page-item <?php if($page_no>=$total_no_of_pages){echo 'disabled';}?>">
                    <a href="<?php if($page_no>=$total_no_of_pages){echo "#";}else{echo "?page_no=".($page_no+1);}?>" class="page-link">Next</a>
                    </li>
                </ul>
                </nav>
            </div>
        </main>
    </div>
</div>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"
></script>
</body>
</html>