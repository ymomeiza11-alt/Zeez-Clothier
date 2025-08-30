<?php
include("../server/connection.php");

if(isset($_POST["create_product"])){
    $product_name = trim($_POST["title"] ?? '');
    $product_description = trim($_POST["product_description"] ?? '');
    $product_price = (float)($_POST["product_price"] ?? 0);
    $product_offer = (int)($_POST["product_special_offer"] ?? 0);
    $product_category = trim($_POST["product_category"] ?? '');
    
    $errors = [];
    if(empty($product_name)) $errors[] = "Product title is required";
    if(empty($product_description)) $errors[] = "Description is required";
    if($product_price <= 0) $errors[] = "Valid price is required";
    
    if(!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Product image is required";
    }

    if(!empty($errors)) {
        header("location: products.php?error=" . urlencode(implode(", ", $errors)));
        exit();
    }

    $upload_dir = "../assets/imgs/";
    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $filename = "product-" . uniqid() . "." . $file_ext;
    $destination = $upload_dir . $filename;

    if(!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        header("location: products.php?error=Failed to save uploaded image");
        exit();
    }

    $stmt = $connection->prepare("INSERT INTO products 
                                (product_name, product_description, product_price, 
                                product_special_offer, product_image, product_category)
                                VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssdiss", 
        $product_name,
        $product_description,
        $product_price,
        $product_offer,
        $filename,
        $product_category
    );

    if($stmt->execute()){
        header("location: products.php?success=Product created successfully");
    } else {
        header("location: products.php?error=Database error: " . urlencode($stmt->error));
    }
    
    $stmt->close();
    $connection->close();
} else {
    header("location: products.php?error=Invalid form submission");
}
?>