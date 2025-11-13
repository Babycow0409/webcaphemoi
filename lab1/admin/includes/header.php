<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION["admin_id"])) {
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
$admin_id = $_SESSION["admin_id"];
$sql = "SELECT * FROM admin_users WHERE id = $admin_id";
$result = $conn->query($sql);
$admin = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cà Phê Đậm Đà</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    .container {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 250px;
        background-color: #333;
        color: white;
        padding-top: 20px;
    }

    .content {
        flex: 1;
        padding: 20px;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 30px;
    }

    .menu {
        list-style: none;
        padding: 0;
    }

    .menu li {
        padding: 15px;
        border-bottom: 1px solid #444;
    }

    .menu li a {
        color: white;
        text-decoration: none;
    }

    .menu li:hover {
        background-color: #444;
    }

    .header {
        display: flex;
        justify-content: space-between;
        padding: 15px;
        background-color: #f4f4f4;
        border-bottom: 1px solid #ddd;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul class="menu">
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="../products/index.php">Sản phẩm</a></li>
                <li><a href="../orders/index.php">Đơn hàng</a></li>
                <li><a href="../users/index.php">Người dùng</a></li>
                <li><a href="../statistics/top-customers.php">Thống kê</a></li>
                <li><a href="../logout.php">Đăng xuất</a></li>
            </ul>
        </div>

        <div class="content">
            <div class="header">
                <h1><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                <div>
                    Xin chào, <?php echo $admin["name"]; ?>
                </div>
            </div>

            <div class="dashboard-content">