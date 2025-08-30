<?php
session_start();
require_once(__DIR__ . '/common.php');
require_once('server/connection.php');

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Handle speculative connections and SSL issues
if (php_sapi_name() === 'cli-server') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_GET)) {
        header("HTTP/1.1 204 No Content");
        exit;
    }

    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Config validation
$configFile = CONFIG_DIR . '/paystack.php';
if (!file_exists($configFile)) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Payment system configuration error (CFG01)");
}

$paystack_config = include($configFile);

if (!is_array($paystack_config)) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Payment configuration error (CFG04: Invalid format)");
}

if (!array_key_exists('mode', $paystack_config)) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Payment configuration error (CFG02: Missing mode)");
}

$mode = $paystack_config['mode'];
if (empty($mode)) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Payment configuration error (CFG02: Empty mode)");
}

$required = [];

if ($mode === 'test') {
    $required = ['test_secret_key', 'test_public_key'];
} elseif ($mode === 'live') {
    $required = ['live_secret_key', 'live_public_key'];
} else {
    header("HTTP/1.1 500 Internal Server Error");
    die("Payment configuration error (CFG03: Invalid mode)");
}

$missingKeys = [];
foreach ($required as $key) {
    if (!array_key_exists($key, $paystack_config) || empty($paystack_config[$key])) {
        $missingKeys[] = $key;
    }
}

if (!empty($missingKeys)) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Payment configuration error (CFG02: Missing keys)");
}

// Reference validation
if (!isset($_GET['reference']) || empty($_GET['reference'])) {
    header("HTTP/1.1 400 Bad Request");
    header("Location: account.php");
    exit();
}

$reference = trim($_GET['reference']);
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $reference)) {
    header("HTTP/1.1 400 Bad Request");
    header("Location: account.php");
    exit();
}

// Payment verification
if ($_SERVER['HTTP_HOST'] === 'localhost:8000' && $paystack_config['mode'] === 'test') {
    $result = (object) [
        'status' => true,
        'data' => (object) [
            'status' => 'success',
            'reference' => $reference,
            'amount' => 7500000,
            'customer' => (object) [
                'email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'test@example.com'
            ],
            'metadata' => (object) [
                'custom_fields' => [
                    (object) ['value' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0]
                ]
            ]
        ]
    ];
} else {
    $result = verifyWithPaystackAPI($reference, $paystack_config);
}

// Process verification result
processVerification($result, $reference);

function verifyWithPaystackAPI($reference, $config)
{
    $secret_key = $config[$config['mode'] . '_secret_key'];
    $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $secret_key",
            "Cache-Control: no-cache"
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return false;
    }

    $decoded = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }

    return $decoded;
}

function processVerification($result, $reference)
{
    if (!$result || !is_object($result) || !property_exists($result, 'status') || !$result->status) {
        header("HTTP/1.1 402 Payment Required");
        header("Location: account.php?payment_error=1&ref=" . urlencode($reference));
        exit();
    }

    if (
        !property_exists($result, 'data') || !is_object($result->data) ||
        !property_exists($result->data, 'status') || !property_exists($result->data, 'reference')
    ) {
        header("HTTP/1.1 500 Internal Server Error");
        header("Location: account.php?payment_error=3");
        exit();
    }

    if ($result->data->status === 'success') {
        try {
            updateOrderStatus($reference);
            session_regenerate_id(true);

            if (!headers_sent()) {
                header("Location: order_success.php?order_id=" . getOrderIdFromReference($reference));
                exit();
            } else {
                echo '<script>window.location="order_success.php?order_id=' . getOrderIdFromReference($reference) . '"</script>';
                exit();
            }
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            header("Location: account.php?payment_error=2");
            exit();
        }
    }

    header("HTTP/1.1 402 Payment Required");
    header("Location: account.php?payment_error=1");
    exit();
}

function updateOrderStatus($reference)
{
    global $connection;

    if (!$connection || $connection->connect_error) {
        throw new Exception("Database connection failed");
    }

    $connection->begin_transaction();

    try {
        $stmt = $connection->prepare("
            SELECT id, user_id, order_status 
            FROM orders 
            WHERE paystack_reference = ? 
            FOR UPDATE
        ");
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) {
            throw new Exception("Order not found for reference: $reference");
        }

        if ($order['order_status'] === 'paid') {
            $connection->rollback();
            return;
        }

        $stmt = $connection->prepare("
            UPDATE orders 
            SET order_status = 'paid', 
                payment_status = 'completed',
                status_updated_at = NOW() 
            WHERE paystack_reference = ?
        ");
        $stmt->bind_param("s", $reference);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows affected when updating order status");
        }

        $stmt = $connection->prepare("
            INSERT INTO order_status_history 
            (order_id, old_status, new_status, changed_by, changed_at)
            VALUES (?, ?, 'paid', 'system', NOW())
        ");
        $old_status = $order['order_status'];
        $stmt->bind_param("is", $order['id'], $old_status);
        $stmt->execute();

        $connection->commit();

        if (isset($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }

    } catch (Exception $e) {
        $connection->rollback();
        throw $e;
    }
}

function getOrderIdFromReference($reference)
{
    $parts = explode('_', $reference);
    if (count($parts) < 2) {
        throw new Exception("Invalid reference format: $reference");
    }

    $order_id = (int) $parts[1];
    if ($order_id <= 0) {
        throw new Exception("Invalid order ID in reference: $reference");
    }

    return $order_id;
}