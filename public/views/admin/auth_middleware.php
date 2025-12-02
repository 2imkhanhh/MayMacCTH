<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    header("Location: login.php");
    exit();
}

// error_log("Admin truy cập: " . $_SESSION['admin_name'] . " - " . $_SERVER['REQUEST_URI']);
?>