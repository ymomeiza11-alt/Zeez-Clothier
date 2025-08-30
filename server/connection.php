<?php
// Start output buffering at the very top
ob_start();

// =============================================================================
// ENVIRONMENT CONFIGURATION
// =============================================================================

// Determine environment (development vs production)
$is_localhost = ($_SERVER['SERVER_NAME'] == 'localhost' ||
    $_SERVER['SERVER_ADDR'] == '127.0.0.1' ||
    $_SERVER['SERVER_ADDR'] == '::1');

if ($is_localhost) {
    // Local development settings
    $host = '127.0.0.1';
    $username = 'root';
    $password = '';
    $database = 'school_project';

    // Show errors in development
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Enable detailed MySQLi error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

} else {
    // Production settings - YOU WILL UPDATE THESE WITH YOUR HOSTING INFO
    $host = 'localhost'; // Often remains 'localhost' on shared hosting
    $username = 'your_production_db_username'; // From your hosting panel
    $password = 'your_strong_production_password'; // From your hosting panel
    $database = 'your_production_db_name'; // From your hosting panel

    // Hide errors from users in production
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);

    // But still log them for debugging
    ini_set('log_errors', 1);
    // Your host will specify the path, common ones are:
    // ini_set('error_log', '/home/your_cpanel_username/php_errors.log');
    // ini_set('error_log', '/var/log/apache2/php_errors.log');

    // Less verbose reporting in production, but still throws exceptions
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

// =============================================================================
// DATABASE CONNECTION
// =============================================================================

try {
    $connection = new mysqli($host, $username, $password, $database);
    $connection->set_charset("utf8mb4");

} catch (Exception $e) {
    // Log the detailed error for admin review
    error_log("[" . date('Y-m-d H:i:s') . "] Database connection failed: " . $e->getMessage());
    error_log("Connection attempted with: host=$host, user=$username, db=$database");

    // Clear buffer before redirecting
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Show appropriate error page
    if ($is_localhost) {
        // Detailed error for development
        die("<h2>Database Connection Failed</h2>
             <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
             <p>Check your XAMPP/WAMP server is running and database exists.</p>");
    } else {
        // Generic error for production users
        header("Location: maintenance.html");
        exit();
    }
}

// Optional: Set timezone if not set in php.ini
date_default_timezone_set('UTC');

// Flush the output buffer if we haven't redirected
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>