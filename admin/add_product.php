<?php
include('header.php');
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

            <h2>Add Product</h2>
            <div class="table-responsive">
                <div class="mx-auto container">
                    <form id="create-form" enctype="multipart/form-data" method="POST" action="create_product.php">
                        <!-- Title -->
                        <div class="form-group mt-2">
                            <label>Title</label>
                            <input type="text" 
                                   name="title" 
                                   placeholder="Product Name" 
                                   required 
                                   class="form-control">
                        </div>

                        <!-- Description -->
                        <div class="form-group mt-2">
                            <label>Description</label>
                            <textarea name="product_description" 
                                      placeholder="Product Description" 
                                      required 
                                      class="form-control"></textarea>
                        </div>

                        <!-- Price -->
                        <div class="form-group mt-2">
                            <label>Price</label>
                            <input type="number" 
                                   name="product_price" 
                                   step="0.01" 
                                   placeholder="Price" 
                                   required 
                                   class="form-control">
                        </div>

                        <!-- Offer (Optional) -->
                        <div class="form-group mt-2">
                            <label>Sale/Offer</label>
                            <input type="number" 
                                   name="product_special_offer" 
                                   step="0.01" 
                                   placeholder="Optional Discount" 
                                   class="form-control">
                        </div>

                        <!-- Category -->
                        <div class="form-group mt-2">
                            <label>Category</label>
                            <select name="product_category" required class="form-select">
                                <option value="khaftan">Khaftan</option>
                                <option value="agbada">Agbada</option>
                                <option value="casual_wear">Casual Wear</option>
                            </select>
                        </div>

                        <!-- Image Upload -->
                        <div class="form-group mt-2">
                            <label>Image</label>
                            <input type="file" 
                                   name="image" 
                                   required 
                                   class="form-control">
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group my-3">
                            <input type="submit" 
                                   name="create_product" 
                                   value="Add Product" 
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