<?php
$isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1');

$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

$cookieParams = [
    'lifetime' => 86400,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict'
];

if (!$isLocalhost) {
    $cookieParams['domain'] = $_SERVER['HTTP_HOST'];
}

session_set_cookie_params($cookieParams);
session_start();

require_once('server/connection.php');
require_once('server/mailer.php');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    $_SESSION['redirect_url'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

$user_stmt = $connection->prepare("SELECT user_name, user_email FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_phone = isset($_POST['user_phone']) ? preg_replace('/[^0-9]/', '', $_POST['user_phone']) : '';
    $user_city = htmlspecialchars(trim($_POST['user_city'] ?? ''), ENT_QUOTES, 'UTF-8');
    $user_address = htmlspecialchars(trim($_POST['user_address'] ?? ''), ENT_QUOTES, 'UTF-8');
    $payment_method = 'paystack';

    if (empty($user_phone) || strlen($user_phone) < 10 || empty($user_city) || empty($user_address)) {
        $error = "Please fill all shipping details correctly!";
    } else {
        $_SESSION['shipping_details'] = [
            'user_phone' => $user_phone,
            'user_city' => $user_city,
            'user_address' => $user_address,
            'payment_method' => $payment_method
        ];

        header("Location: measurements.php");
        exit();
    }
}
?>

<?php include("layouts/header.php"); ?>

<!-- Checkout Section -->
<section class="my-5 py-5">
    <div class="container text-center mt-3 pt-5">
        <h2 class="font-weight-bold">Shipping Information</h2>
        <hr class="mx-auto">
    </div>
    <div class="mx-auto container">
        <form id="checkout-form" method="POST" action="checkout.php">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="form-group mb-3">
                <label>Full Name</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['user_name'] ?? '') ?>"
                    readonly>
            </div>
            <div class="form-group mb-3">
                <label>Email Address</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>"
                    readonly>
            </div>
            <div class="form-group mb-3">
                <label for="checkout-phone">Phone Number*</label>
                <input type="tel" class="form-control" id="checkout-phone" name="user_phone"
                    value="<?= htmlspecialchars($_POST['user_phone'] ?? '') ?>" placeholder="e.g., 08012345678"
                    pattern="[0-9]{11}" title="11-digit Nigerian phone number" required>
            </div>
            <div class="form-group mb-3">
                <label for="checkout-city">City*</label>
                <input type="text" class="form-control" id="checkout-city" name="user_city"
                    value="<?= htmlspecialchars($_POST['user_city'] ?? '') ?>" placeholder="e.g., Lagos" required>
            </div>
            <div class="form-group mb-3">
                <label for="checkout-address">Delivery Address*</label>
                <textarea class="form-control" id="checkout-address" name="user_address" rows="3"
                    placeholder="Full address including landmarks and postal code"
                    required><?= htmlspecialchars($_POST['user_address'] ?? '') ?></textarea>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-btn btn-lg btn-block">
                    Proceed to Measurements
                </button>
            </div>
        </form>
    </div>
</section>

<?php include("layouts/footer.php"); ?>