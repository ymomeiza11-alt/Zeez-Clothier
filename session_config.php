<?php
// session_config.php
function configureSession()
{
    // Check if session is already started
    if (session_status() !== PHP_SESSION_NONE) {
        error_log("Session already started: " . session_id());
        return;
    }

    // Use consistent settings
    $isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1');
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $isLocalhost ? '' : $_SERVER['HTTP_HOST'],
        'secure' => $isLocalhost ? false : $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // Start the session
    session_start();

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    error_log("Session configured: " . session_id());
}
?>