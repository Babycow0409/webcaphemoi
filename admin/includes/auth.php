<?php
session_start();

// Kiểm tra đăng nhập admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Yêu cầu đăng nhập
function requireLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: ../login.php");
        exit;
    }
}

// Lấy thông tin admin hiện tại
function getCurrentAdmin($conn) {
    if (isAdminLoggedIn()) {
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return null;
}
?> 