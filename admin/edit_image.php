<?php
include('header.php');

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if(isset($_GET["product_id"])) {
    $product_id = (int)$_GET["product_id"];
    
    $stmt = $connection->prepare("SELECT product_name FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result($product_name);
    $stmt->fetch();
    $stmt->close();
    
    if(empty($product_name)) {
        header("Location: products.php?error=Product+not+found");
        exit();
    }
} else {
    header("Location: products.php");
    exit();
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
                        <!-- Optional buttons -->
                    </div>
                </div>
            </div>

            <h2>Update Product Image</h2>
            <div class="table-responsive">
                <div class="mx-auto container">
                    <!-- Form: Ensure ALL names match create_product.php -->
                    <form id="create-form" enctype="multipart/form-data" method="POST" action="update_image.php">
                        <input type="hidden" name="product_id" value="<?php echo $product_id?>">
                        <!-- Image -->
                        <div class="form-group mt-2">
                            <label>Name</label>
                            <p class="form-control-plaintext"><?php echo $product_name; ?></p>
                        </div>
                        <!-- Image -->
                        <div class="form-group mt-2">
                            <label>Image</label>
                            <input type="file" 
                                   name="image" 
                                   placeholder="Immage" 
                                   required 
                                   class="form-control">
                        </div>
                        <!-- Submit Button -->
                        <div class="form-group my-3">
                            <input type="submit" 
                                   name="create_product" 
                                   value="Update" 
                                   class="btn btn-primary">
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>