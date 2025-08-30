<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['total_quantity'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['product_quantity'];

    if ($product_id < 1 || $quantity < 1) {
        $_SESSION['error'] = "Invalid product or quantity";
        header("Location: view_product.php?product_id=$product_id");
        exit;
    }

    require_once('server/connection.php');

    $stmt = $connection->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Product not found";
        header("Location: view_product.php?product_id=$product_id");
        exit;
    }

    $product = $result->fetch_assoc();

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = [
            'product_name' => $product['product_name'],
            'product_price' => $product['product_price'],
            'product_quantity' => $quantity,
            'product_image' => $product['product_image']
        ];
    }

    $_SESSION['total_quantity'] += $quantity;

    $_SESSION['success'] = "Added to cart!";
    header("Location: view_product.php?product_id=$product_id");
    exit;
}

header("Location: index.php");
exit;
?>