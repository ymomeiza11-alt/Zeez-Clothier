<?php
include("server/connection.php");
$stmt = $connection->prepare("SELECT * FROM products where product_category = 'khaftan' LIMIT 4");
$stmt->execute();
$khaftan_products = $stmt->get_result();
?>