<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['total_quantity'] = 0;
}

foreach ($_SESSION['cart'] as $product_id => &$item) {
    if (isset($item['price']) || isset($item['quantity'])) {
        if (isset($item['price'])) {
            $item['product_price'] = $item['price'];
            unset($item['price']);
        }
        if (isset($item['quantity'])) {
            $item['product_quantity'] = $item['quantity'];
            unset($item['quantity']);
        }
        if (isset($item['name'])) {
            $item['product_name'] = $item['name'];
            unset($item['name']);
        }
        if (isset($item['image'])) {
            $item['product_image'] = $item['image'];
            unset($item['image']);
        }
    }
}
unset($item);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove'])) {
        foreach ($_POST['remove'] as $product_id => $value) {
            $product_id = (int)$product_id;
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['total_quantity'] -= $_SESSION['cart'][$product_id]['product_quantity'];
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }
    elseif (isset($_POST['edit_qty'])) {
        foreach ($_POST['quantity'] as $product_id => $new_quantity) {
            $product_id = (int)$product_id;
            $new_quantity = (int)$new_quantity;
            
            if (isset($_SESSION['cart'][$product_id]) && $new_quantity > 0) {
                $old_quantity = $_SESSION['cart'][$product_id]['product_quantity'];
                $_SESSION['cart'][$product_id]['product_quantity'] = $new_quantity;
                
                $_SESSION['total_quantity'] += ($new_quantity - $old_quantity);
            }
        }
    }
    
    header("Location: cart.php");
    exit;
}

include("layouts/header.php");
?>

<section class="cart container my-5 py-5">
    <div class="container mt-5">
        <h2 class="font-weight-bold text-center">Your Shopping Cart</h2>
        <hr class="mx-auto">
    </div>
    
    <?php if(empty($_SESSION['cart'])): ?>
        <div class="empty-cart text-center py-5">
            <p>Your cart is empty</p>
            <a href="shop.php"><button class="buy-btn">Continue Shopping</button></a>
        </div>
    <?php else: ?>
        <form method="POST" action="cart.php">
            <table class="table mt-5">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach($_SESSION['cart'] as $product_id => $item): 
                        $price = $item['product_price'] ?? 0;
                        $quantity = $item['product_quantity'] ?? 0;
                        $name = $item['product_name'] ?? 'Unknown Product';
                        $image = $item['product_image'] ?? 'placeholder.jpg';
                        
                        $subtotal = $price * $quantity;
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td class="cart-product">
                            <div class="d-flex align-items-center">
                                <img src="assets/imgs/<?= htmlspecialchars($image) ?>" 
                                     alt="<?= htmlspecialchars($name) ?>" 
                                     width="60" class="me-3">
                                <div>
                                    <p><?= htmlspecialchars($name) ?></p>
                                </div>
                            </div>
                        </td>
                        <td>₦<?= number_format($price, 2) ?></td>
                        <td>
                            <input type="number" name="quantity[<?= $product_id ?>]" 
                                   min="1" 
                                   value="<?= $quantity ?>" class="form-control" style="width: 80px;">
                        </td>
                        <td>₦<?= number_format($subtotal, 2) ?></td>
                        <td>
                            <button type="submit" name="remove[<?= $product_id ?>]" 
                                    class="btn btn-danger btn-sm">Remove</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <button type="submit" name="edit_qty" class="">Update Cart</button>
                </div>
                <div class="text-end">
                    <h4>Total: ₦<?= number_format($total, 2) ?></h4>
                    <a href="checkout.php" class="btn btn-btn mt-2">Proceed to Checkout</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php include("layouts/footer.php"); ?>