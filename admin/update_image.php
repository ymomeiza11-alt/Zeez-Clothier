<?php
session_start();
include("../server/connection.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST["create_product"])) { 
    $product_id = (int)$_POST["product_id"];

    if(!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        header("Location: products.php?imgUpdate_failed=Invalid+file+upload");
        exit();
    }

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image_name = "product-{$product_id}-" . uniqid() . ".$ext";
    $target_path = "../assets/imgs/" . $image_name;

    if(move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        $stmt = $connection->prepare("UPDATE products SET product_image = ? WHERE product_id = ?");
        $stmt->bind_param("si", $image_name, $product_id);
        
        if($stmt->execute()) {
            header("Location: products.php?image_updated=Image+updated+successfully");
        } else {
            header("Location: products.php?imgUpdate_failed=Database+error");
        }
    } else {
        header("Location: products.php?imgUpdate_failed=File+upload+failed");
    }
} else {
    header("Location: products.php");
}
exit();
?>