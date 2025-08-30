<?php
if (!defined('APP_ROOT'))
    define('APP_ROOT', __DIR__);
if (!defined('CONFIG_DIR'))
    define('CONFIG_DIR', APP_ROOT . '/config');

if (session_status() === PHP_SESSION_NONE) {
    require_once('session_config.php');
    configureSession();
}
?>