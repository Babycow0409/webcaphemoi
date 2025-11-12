<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION["admin"]) && !isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy thông tin admin hiện tại
if (isset($_SESSION["admin_id"])) {
    $admin_id = $_SESSION["admin_id"];
    $sql = "SELECT * FROM admin_users WHERE id = $admin_id";
    $result = $conn->query($sql);
    $admin = $result->fetch_assoc();
} else {
    $admin = $_SESSION["admin"];
}

// Xác định URL hiện tại để highlight menu item đang active
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - Cà Phê Đậm Đà</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet"
        href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/includes/admin-style.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header">
                    <h4>Admin Panel</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php' && $current_dir == 'admin') ? 'active' : ''; ?>"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/index.php">
                            <i class="fas fa-tachometer-alt"></i> Tổng quan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_dir == 'products') ? 'active' : ''; ?>"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/products/index.php">
                            <i class="fas fa-coffee"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_dir == 'orders') ? 'active' : ''; ?>"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/orders/index.php">
                            <i class="fas fa-shopping-cart"></i> Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_dir == 'users') ? 'active' : ''; ?>"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/users/index.php">
                            <i class="fas fa-users"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_dir == 'employees') ? 'active' : ''; ?>"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/employees/index.php">
                            <i class="fas fa-user-tie"></i> Nhân viên
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'shifts') !== false && strpos($_SERVER['PHP_SELF'], 'shift-assignments') === false && strpos($_SERVER['PHP_SELF'], 'salary') === false) ? 'active' : ''; ?>"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/employees/shifts/index.php">
                            <i class="fas fa-clock"></i> Ca làm việc
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'shift-assignments') !== false) ? 'active' : ''; ?>"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/employees/shift-assignments/index.php">
                            <i class="fas fa-calendar-alt"></i> Phân ca
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'salary') !== false) ? 'active' : ''; ?>"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/employees/salary/index.php">
                            <i class="fas fa-money-bill-wave"></i> Tính lương
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_dir == 'statistics') ? 'active' : ''; ?>"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/statistics/top-customers.php">
                            <i class="fas fa-chart-bar"></i> Thống kê
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/", 1) - 2); ?>admin/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main content -->
            <div class="content">
                <div class="header">
                    <h2><?php 
                        // Chỉ hiển thị icon cho trang nhân viên
                        if (strpos($_SERVER['PHP_SELF'], 'employees') !== false && isset($header_icon)) {
                            echo '<i class="fas fa-' . $header_icon . ' mr-2"></i>';
                        }
                        echo isset($page_title) ? $page_title : 'Tổng quan'; 
                    ?></h2>
                    <div>
                        <i class="fas fa-user mr-1"></i>
                        <?php echo $admin["name"]; ?>
                    </div>
                </div>


                <div class="main-content"><?php
// Place for success/error messages
if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                    <?php endif; 
                
if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>