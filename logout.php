<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    $admin_id   = $_SESSION['admin_id'];
    $admin_name = $_SESSION['admin_name'];
    $action = "Logout dari sistem internal";
    $stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, admin_name, action) VALUES (?,?,?)");
    $stmt->bind_param("iss", $admin_id, $admin_name, $action);
    $stmt->execute();
}

session_destroy();
header("Location: " . BASE_URL . "/login.php");
exit();
?>
