<?php
session_start();
require_once('server/connection.php');

if (!isset($_SESSION['shipping_details']) || !isset($_SESSION['measurements']) || empty($_SESSION['cart'])) {
    header("Location: checkout.php");
    exit();
}

$shipping = $_SESSION['shipping_details'];
$measurements = $_SESSION['measurements'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $connection->begin_transaction();

        $order_cost = 0;
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $product_stmt = $connection->prepare("SELECT product_price FROM products WHERE product_id = ?");
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();
            $product = $product_stmt->get_result()->fetch_assoc();
            $product_stmt->close();
            $order_cost += ($product['product_price'] * $item['product_quantity']);
        }

        $order_date = date('Y-m-d H:i:s');

        $order_stmt = $connection->prepare("INSERT INTO orders 
            (order_cost, order_status, user_id, user_phone, user_city, user_address, order_date, payment_method) 
            VALUES (?, 'not paid', ?, ?, ?, ?, ?, ?)");

        $order_stmt->bind_param(
            "diissss",
            $order_cost,
            $_SESSION['user_id'],
            $shipping['user_phone'],
            $shipping['user_city'],
            $shipping['user_address'],
            $order_date,
            $shipping['payment_method']
        );

        if (!$order_stmt->execute()) {
            throw new Exception("Order creation failed");
        }

        $order_id = $connection->insert_id;
        $order_stmt->close();

        $item_stmt = $connection->prepare("INSERT INTO order_items 
            (order_id, product_id, product_name, product_image, product_price, product_quantity, user_id, order_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($_SESSION['cart'] as $product_id => $item) {
            $product_stmt = $connection->prepare("SELECT product_name, product_image, product_price FROM products WHERE product_id = ?");
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();
            $product = $product_stmt->get_result()->fetch_assoc();
            $product_stmt->close();

            $item_stmt->bind_param(
                "iissdiis",
                $order_id,
                $product_id,
                $product['product_name'],
                $product['product_image'],
                $product['product_price'],
                $item['product_quantity'],
                $_SESSION['user_id'],
                $order_date
            );
            $item_stmt->execute();
        }
        $item_stmt->close();

        $agbada_full_length = $measurements['agbada_full_length'] ?? null;
        $agbada_full_width = $measurements['agbada_full_width'] ?? null;

        $measurement_stmt = $connection->prepare("INSERT INTO measurements 
            (order_id, user_id, neck, lap, round_head, length, trouser_length, shoulder, hand, shape_bust, agbada_full_length, agbada_full_width, time_span) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $measurement_stmt->bind_param(
            "iiddddddddddi",
            $order_id,
            $_SESSION['user_id'],
            $measurements['neck'],
            $measurements['lap'],
            $measurements['round_head'],
            $measurements['length'],
            $measurements['trouser_length'],
            $measurements['shoulder'],
            $measurements['hand'],
            $measurements['shape_bust'],
            $agbada_full_length,
            $agbada_full_width,
            $measurements['time_span']
        );
        $measurement_stmt->execute();
        $measurement_stmt->close();

        $connection->commit();

        unset($_SESSION['shipping_details']);
        unset($_SESSION['measurements']);

        $_SESSION['current_order_id'] = $order_id;

        header("Location: payment.php?order_id=$order_id");
        exit();

    } catch (Exception $e) {
        $connection->rollback();
        $error = "Order processing failed. Please try again or contact support.";
    }
}
?>

<?php include("layouts/header.php"); ?>

<section class="my-5 py-5">
    <div class="container text-center mt-3 pt-5">
        <h2 class="font-weight-bold">Order Summary</h2>
        <hr class="mx-auto">
    </div>

    <div class="mx-auto container">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Shipping Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?= htmlspecialchars($user['user_name'] ?? '') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['user_email'] ?? '') ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($shipping['user_phone'] ?? '') ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($shipping['user_city'] ?? '') ?>,
                    <?= htmlspecialchars($shipping['user_address'] ?? '') ?>
                </p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Body Measurements (cm)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Neck:</strong> <?= $measurements['neck'] ?></p>
                        <p><strong>Lap:</strong> <?= $measurements['lap'] ?></p>
                        <p><strong>Head:</strong> <?= $measurements['round_head'] ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Body Length:</strong> <?= $measurements['length'] ?></p>
                        <p><strong>Trouser Length:</strong> <?= $measurements['trouser_length'] ?></p>
                        <p><strong>Shoulder:</strong> <?= $measurements['shoulder'] ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Arm Length:</strong> <?= $measurements['hand'] ?></p>
                        <p><strong>Bust/Chest:</strong> <?= $measurements['shape_bust'] ?></p>
                        <p><strong>Production Time:</strong> <?= $measurements['time_span'] ?> weeks</p>
                    </div>
                </div>

                <?php if (isset($measurements['agbada_full_length'])): ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Agbada Length:</strong> <?= $measurements['agbada_full_length'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Agbada Width:</strong> <?= $measurements['agbada_full_width'] ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $product_id => $item) {
                        $subtotal = $item['product_price'] * $item['product_quantity'];
                        $total += $subtotal;
                        ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                <span class="font-weight-bold"><?= htmlspecialchars($item['product_name'] ?? '') ?></span>
                                <span class="text-muted">x<?= $item['product_quantity'] ?></span>
                            </div>
                            <span>₦<?= number_format($subtotal, 2) ?></span>
                        </li>
                        <?php
                    }
                    ?>
                    <li class="list-group-item d-flex justify-content-between font-weight-bold">
                        <span>Total:</span>
                        <span>₦<?= number_format($total, 2) ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="form-group mt-4">
            <form method="POST" action="order_summary.php">
                <button type="submit" class="btn btn-btn btn-lg btn-block">
                    Proceed to Payment
                </button>
            </form>
        </div>
    </div>
</section>

<?php include("layouts/footer.php"); ?>