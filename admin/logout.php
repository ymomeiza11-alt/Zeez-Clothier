<?php 
session_start();
    if(isset($_GET['logout'])) {
        if(isset($_SESSION['admin_logged_in'])) {
            unset($_SESSION['admin_logged_in']);
            unset($_SESSION['admin_name']);
            unset($_SESSION['admin_email']);
            header("Location: login.php");
            exit();
        }
    }
?>
