<?php
include('connection.php');

$stmt = $connection->prepare("SELECT 
    product_id, 
    product_name, 
    product_image, 
    product_price 
    FROM products 
    LIMIT 4");

if (!$stmt) {
    die("Database error: " . $connection->error);
}

$stmt->execute();
$featured_products = $stmt->get_result();

if (!$featured_products) {
    die("Query failed: " . $connection->error);
}
?>