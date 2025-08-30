<?php 
include('header.php');
    if(isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
        $stmt = $connection->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $products = $stmt->get_result();
    }else if(isset($_POST["edit_btn"])){
        $product_id = $_POST['product_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $sales = $_POST['sales'];

        $stmt = $connection->prepare("UPDATE products SET product_name = ?, product_description = ?, product_price = ?, product_category = ?, product_special_offer = ? WHERE product_id = ?");
        $stmt->bind_param("sssssi", $title, $description, $price, $category, $sales, $product_id);
        if($stmt->execute()) {
            header("Location: products.php?edit_success_message=Product updated successfully");
        } else {
            header("Location: products.php?edit_failure_message=Failed to update product");
        }
    }
    else{
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
                        <!-- Your buttons here -->
                    </div>
                </div>
            </div>

            <h2>Edit products</h2>
            <div class="table-responsive">
                <div class="mx-auto container">
                    <form method="POST" action="edit_product.php" id="edit-form">
                        <p style="color: red;"><?php if(isset($_GET['error'])) { echo $_GET['error']; } ?></p>
                        <div class="form-group mt-2">
                            <?php foreach ($products as $product) { ?>
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            <label for="">Title</label>
                            <input type="text" class="form-control" id="product-name" value="<?php echo $product['product_name']; ?>" name="title" placeholder="Enter product title">
                        </div>
                        <div class="form-group mt-2">
                            <label for="">Description</label>
                            <input type="text" class="form-control" id="product-description" name="description" value="<?php echo $product['product_description']; ?>" placeholder="Enter product description">
                        </div>
                        <div class="form-group mt-2">
                            <label for="">Price</label>
                            <input type="text" class="form-control" id="product-price" name="price" value="<?php echo $product['product_price']; ?>" placeholder="Enter product price">
                        </div>
                        <div class="form-group mt-2">
                            <label for="">Category</label>
                            <select name="category" required id="" class="form-select">
                                <option value="khaftan">Khaftan</option>
                                <option value="agbada">Agbada</option>
                                <option value="casual_wear">Casual Wear</option>
                            </select>
                        </div>
                        <div class="form-group mt-2">
                            <label for="">Sales</label>
                            <input type="text" class="form-control" value="<?php echo $product['product_special_offer']; ?>" id="product-sales" name="sales" placeholder="Enter product sales">
                        </div>
                        <div class="form-group mt-2">
                            <input type="submit" name="edit_btn" value="Edit" class="btn btn-primary">
                        </div>
                        <?php } ?>
                    </form>
                </div>
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