<?php
session_start();

$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (!isset($_SESSION["logged_in"])) {
    header("Location: login.php");
    exit();
}

include("layouts/header.php");

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$payment_method = isset($_GET['payment_method']) ? htmlspecialchars($_GET['payment_method']) : 'pod';

if($order_id < 1) {
    header("Location: index.php");
    exit();
}
?>

<section class="my-5 py-5">
    <div class="container text-center mt-3 pt-5">
        <div class="alert alert-success py-4">
            <i class="bi bi-check-circle display-1 text-success"></i>
            <h2 class="mt-3">Order Placed Successfully!</h2>
        </div>
        <hr class="mx-auto w-50">
    </div>
    <div class="mx-auto container text-center py-4" style="max-width: 600px;">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="lead">Your order ID: <strong>#<?= $order_id ?></strong></p>
                
                <?php if ($payment_method == 'paystack'): ?>
                    <div class="alert alert-success mt-4">
                        <h5><i class="bi bi-check-circle-fill"></i> Payment Successful</h5>
                        <p>Your payment has been processed successfully via Paystack.</p>
                        <p class="mb-0">Transaction Reference: <?= isset($_GET['trxref']) ? htmlspecialchars($_GET['trxref']) : '' ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <p>We've sent order details to your email</p>
                    <a href="account.php" class="btn btn-outline-primary">
                        <i class="bi bi-person-circle"></i> View Orders
                    </a>
                    <a href="shop.php" class="btn btn-primary ml-2">
                        <i class="bi bi-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include("layouts/footer.php"); ?>