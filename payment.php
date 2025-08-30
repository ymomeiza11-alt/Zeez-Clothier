<?php
require_once(__DIR__ . '/common.php');
require_once('server/connection.php');

$configFile = CONFIG_DIR . '/paystack.php';
if (!file_exists($configFile)) {
    die("Payment system configuration error (CFG01)");
}

$paystack_config = include($configFile);

if (!is_array($paystack_config)) {
    die("Payment configuration error (CFG04: Invalid config format");
}

if (!array_key_exists('mode', $paystack_config)) {
    die("Payment configuration error (CFG02: Missing mode)");
}

$mode = $paystack_config['mode'];
if (empty($mode)) {
    die("Payment configuration error (CFG02: Empty mode)");
}

$required = [];

if ($mode === 'test') {
    $required = ['test_secret_key', 'test_public_key'];
} elseif ($mode === 'live') {
    $required = ['live_secret_key', 'live_public_key'];
} else {
    die("Payment configuration error (CFG03: Invalid mode '$mode')");
}

$missingKeys = [];
foreach ($required as $key) {
    if (!array_key_exists($key, $paystack_config) || empty($paystack_config[$key])) {
        $missingKeys[] = $key;
    }
}

if (!empty($missingKeys)) {
    die("Payment configuration error (CFG02: Missing keys)");
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if (!$order_id) {
    header("Location: checkout.php?error=Invalid order ID");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$stmt = $connection->prepare("SELECT order_cost FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: checkout.php?error=Order not found");
    exit();
}

$stmt = $connection->prepare("SELECT user_email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$amount_in_kobo = $order['order_cost'] * 100;
$reference = 'ORD_' . $order_id . '_' . uniqid();

$is_local = ($_SERVER['HTTP_HOST'] === 'localhost:8000');
$callback_url = $is_local
    ? 'http://localhost:8000/payment_verify.php'
    : 'https://' . $_SERVER['HTTP_HOST'] . '/payment_verify.php';

$stmt = $connection->prepare("UPDATE orders SET paystack_reference = ?, payment_method = 'paystack', payment_status = 'pending' WHERE order_id = ?");
$stmt->bind_param("si", $reference, $order_id);
$stmt->execute();
$stmt->close();

include("layouts/header.php");
?>

<section class="my-5 py-5">
    <div class="container">
        <div class="text-center mt-5 mb-4">
            <h2>Complete Payment</h2>
            <p class="lead">Order #<?= $order_id ?> - ₦<?= number_format($order['order_cost'], 2) ?></p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center p-4">
                        <button id="paystack-btn" class="btn btn-primary btn-lg mb-3">
                            <i class="bi bi-credit-card"></i> Pay Now
                        </button>
                        <div id="paystack-status" class="small text-muted mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function initializePaymentSystem() {
        const statusEl = document.getElementById('paystack-status');
        const payButton = document.getElementById('paystack-btn');

        statusEl.textContent = "Initializing payment system...";
        payButton.disabled = true;

        const PAYSTACK_SOURCES = [
            'https://js.paystack.co/v1/inline.js',
            '/js/paystack-inline.js'
        ];

        let currentSourceIndex = 0;

        function attemptLoad() {
            if (currentSourceIndex >= PAYSTACK_SOURCES.length) {
                statusEl.textContent = "Payment system unavailable. Please try again later.";
                return;
            }

            const script = document.createElement('script');
            script.src = PAYSTACK_SOURCES[currentSourceIndex] + '?v=' + Date.now();

            script.onload = function () {
                if (typeof PaystackPop !== 'undefined') {
                    statusEl.textContent = "Payment system ready";
                    setupPaymentButton();
                } else {
                    currentSourceIndex++;
                    attemptLoad();
                }
            };

            script.onerror = function () {
                currentSourceIndex++;
                attemptLoad();
            };

            document.head.appendChild(script);
        }

        function setupPaymentButton() {
            payButton.addEventListener('click', function () {
                statusEl.textContent = "Preparing payment...";

                <?php if (!empty($paystack_config)): ?>
                    try {
                        const paymentHandler = PaystackPop.setup({
                            key: '<?= $paystack_config[$paystack_config['mode'] . '_public_key'] ?>',
                            email: '<?= $user['user_email'] ?>',
                            amount: <?= $amount_in_kobo ?>,
                            ref: '<?= $reference ?>',
                            currency: 'NGN',
                            callback: function (response) {
                                window.location.href = '<?= $callback_url ?>?reference=' + response.reference;
                            },
                            onClose: function () {
                                statusEl.textContent = "Payment cancelled - you may try again";
                            }
                        });

                        paymentHandler.openIframe();
                        statusEl.textContent = "Opening payment window...";

                    } catch (error) {
                        statusEl.textContent = "Error: " + error.message;
                        alert("Payment error occurred. Please try again.");
                    }
                <?php else: ?>
                    statusEl.textContent = "Payment configuration error";
                <?php endif; ?>
            });

            payButton.disabled = false;
        }

        attemptLoad();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializePaymentSystem);
    } else {
        initializePaymentSystem();
    }
</script>
<?php include("layouts/footer.php"); ?>