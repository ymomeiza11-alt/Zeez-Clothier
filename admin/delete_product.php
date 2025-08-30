<?php
session_start();
require_once "../server/connection.php";

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    header("Location: products.php?delete_error_message=Invalid+product+ID");
    exit();
}

$product_id = (int)$_GET['product_id'];

$stmt = $connection->prepare("DELETE FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);

if($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: products.php?delete_success_message=Product+deleted+successfully");
} else {
    header("Location: products.php?delete_error_message=Error+deleting+product");
}

$stmt->close();
$connection->close();
exit();
?>