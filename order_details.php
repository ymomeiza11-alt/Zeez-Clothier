<?php
declare(strict_types=1);
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

include("server/connection.php");

if (!isset($_SESSION["logged_in"]) || !isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id || !isset($_POST["order_details_btn"])) {
    header("Location: account.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];
$stmt = $connection->prepare("SELECT o.order_status, o.order_date, o.user_phone, 
                            o.user_city, o.user_address, o.order_cost
                            FROM orders o
                            WHERE o.order_id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: account.php?error=Order not found");
    exit();
}

$order_data = $order_result->fetch_assoc();
$order_status = htmlspecialchars($order_data['order_status'], ENT_QUOTES, 'UTF-8');
$order_date = date('M j, Y g:i A', strtotime($order_data['order_date']));
$order_cost = number_format($order_data['order_cost'], 2);

$stmt = $connection->prepare("SELECT product_name, product_image, product_price, product_quantity 
                            FROM order_items 
                            WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();
?>

<?php include("layouts/header.php"); ?>

<section class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2>Order #<?= $order_id ?> Details</h2>
        <div class="d-flex justify-content-center mb-3">
            <span class="badge badge-<?=
                $order_status === 'delivered' ? 'success' :
                ($order_status === 'shipped' ? 'primary' : 'warning')
                ?> p-2">
                Status: <?= ucfirst($order_status) ?>
            </span>
        </div>
        <p class="text-muted">Placed on <?= $order_date ?></p>
        <hr class="mx-auto w-50">
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Items Ordered</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-right">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $order_items->fetch_assoc()):
                                $name = htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8');
                                $image = htmlspecialchars($item['product_image'], ENT_QUOTES, 'UTF-8');
                                $price = number_format($item['product_price'], 2);
                                $qty = (int) $item['product_quantity'];
                                $subtotal = number_format($item['product_price'] * $qty, 2);
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="assets/imgs/<?= $image ?>" alt="<?= $name ?>"
                                                class="img-thumbnail mr-3" width="80">
                                            <div><?= $name ?></div>
                                        </div>
                                    </td>
                                    <td class="text-right">₦<?= $price ?></td>
                                    <td class="text-center"><?= $qty ?></td>
                                    <td class="text-right">₦<?= $subtotal ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>₦<?= $order_cost ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span>₦0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between font-weight-bold">
                        <span>Total:</span>
                        <span>₦<?= $order_cost ?></span>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-info">
                            <h6>Payment Method</h6>
                            <p class="mb-0">Pay on Delivery (Cash)</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1">
                        <strong>Phone:</strong>
                        <?= htmlspecialchars($order_data['user_phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="mb-1">
                        <strong>City:</strong>
                        <?= htmlspecialchars($order_data['user_city'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="mb-0">
                        <strong>Address:</strong>
                        <?= htmlspecialchars($order_data['user_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

            <?php if ($order_status === 'not paid'): ?>
                <div class="card border-warning mt-4">
                    <div class="card-body text-center">
                        <h6>Payment Instructions</h6>
                        <p class="mb-2">Please prepare <strong>₦<?= $order_cost ?></strong> in cash</p>
                        <p class="mb-0">Payment will be collected upon delivery</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include("layouts/footer.php"); ?>