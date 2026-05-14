<?php
// Internal System - Redirect to login or dashboard
require_once 'includes/config.php';

if (isLoggedIn()) {
    header("Location: " . BASE_URL . "/dashboard.php");
} else {
    header("Location: " . BASE_URL . "/login.php");
}
exit();
?>
