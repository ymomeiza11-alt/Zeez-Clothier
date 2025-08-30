<?php
function send_order_email($to, $subject, $template, $data) {
    // Render HTML template
    ob_start();
    extract($data);
    include("../email/$template.php");
    $message = ob_get_clean();
    
    // Plain text fallback
    $plain_text = strip_tags($message);
    
    // Critical headers
    $headers = [
        'From: Zeez Clothier <zeezclothier@gmail.com>',
        'Reply-To: <zeezclothier@gmail.com>',
        'Return-Path: <zeezclothier@gmail.com>', // Add this line
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion(),
        'Cc: zeezclothier@gmail.com', 
        'MIME-Version: 1.0'
    ];
    
    // Combine headers
    $headers = implode("\r\n", $headers);
    
    // Add invisible plain text version
    $full_message = $message . "\r\n\r\n<!--\r\n" . $plain_text . "\r\n-->";
    
    return mail($to, $subject, $full_message, $headers);
}

// Prevent email flooding
$last_sent = $_SESSION['last_email_sent'] ?? 0;
if (time() - $last_sent < 15) { // 15-second delay
    error_log("Email rate limit exceeded");
    return false;
}
$_SESSION['last_email_sent'] = time();

?>