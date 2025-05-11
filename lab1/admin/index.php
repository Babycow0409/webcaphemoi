<?php
<<<<<<< HEAD
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
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
$admin = $_SESSION["admin"];
=======
$page_title = "Dashboard";
include 'includes/header.php';
>>>>>>> 36298127f1deb9f590e3222166af251932b728fd

// Thống kê tổng quan
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
<<<<<<< HEAD
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'] ?? 0;
$totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;

// Đơn hàng gần đây
$recentOrders = [];
$ordersQuery = $conn->query("SHOW TABLES LIKE 'orders'");
if ($ordersQuery->num_rows > 0) {
    // Kiểm tra cấu trúc bảng orders để biết các cột có sẵn
    $orders_columns = $conn->query("SHOW COLUMNS FROM orders");
    $has_created_at = false;
    $order_column = "id"; // Mặc định sắp xếp theo id
    
    if ($orders_columns) {
        while ($col = $orders_columns->fetch_assoc()) {
            if ($col['Field'] == 'created_at') {
                $has_created_at = true;
                break;
            }
            if ($col['Field'] == 'order_date') {
                $order_column = "order_date";
            }
        }
    }
    
    // Sử dụng created_at nếu có, nếu không dùng order_date hoặc id
    $order_by = $has_created_at ? "o.created_at" : "o.$order_column";
    
    $recentOrdersQuery = $conn->query("SELECT o.*, u.fullname FROM orders o 
                                      LEFT JOIN users u ON o.user_id = u.id 
                                      ORDER BY $order_by DESC LIMIT 5");
    if ($recentOrdersQuery) {
        $recentOrders = $recentOrdersQuery;
    }
}

// Sản phẩm mới nhất
$topProducts = $conn->query("SELECT id, name, price, 0 as order_count 
                            FROM products 
                            ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cà Phê Đậm Đà</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container-fluid {
            padding: 0;
        }
        .sidebar {
            background-color: #343a40;
            color: white;
            min-height: 100vh;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .content {
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .stats-card {
            color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .stats-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .stats-card p {
            font-size: 24px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h4 class="text-center mb-4">Admin Panel</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products/index.php">
                            <i class="fas fa-coffee mr-2"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders/index.php">
                            <i class="fas fa-shopping-cart mr-2"></i> Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users/index.php">
                            <i class="fas fa-users mr-2"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="statistics/top-customers.php">
                            <i class="fas fa-chart-bar mr-2"></i> Thống kê
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <div class="col-md-10">
                <div class="header d-flex justify-content-between align-items-center">
                    <h2>Dashboard</h2>
                    <div>
                        <i class="fas fa-user mr-1"></i> 
                        <?php echo $admin["name"]; ?>
                    </div>
                </div>
                
                <div class="content">
                    <h3>Chào mừng đến với trang quản trị!</h3>
                    <p>Đây là trang quản trị của website Cà Phê Đậm Đà.</p>
                    
                    <!-- Thống kê -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="stats-card" style="background-color: #3498db;">
                                <h3>Sản phẩm</h3>
                                <p><?php echo $totalProducts; ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background-color: #2ecc71;">
                                <h3>Người dùng</h3>
                                <p><?php echo $totalUsers; ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background-color: #e74c3c;">
                                <h3>Đơn hàng</h3>
                                <p><?php echo $totalOrders; ?></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="background-color: #f39c12;">
                                <h3>Doanh thu</h3>
                                <p><?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="m-0">Chức năng có sẵn</h5>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li>Quản lý sản phẩm: Thêm, sửa, xóa sản phẩm</li>
                                <li>Quản lý đơn hàng: Xem và cập nhật trạng thái đơn hàng</li>
                                <li>Quản lý người dùng: Xem danh sách người dùng đã đăng ký</li>
                                <li>Thống kê: Xem thống kê top khách hàng theo doanh số</li>
                            </ul>
                            
                            <p>Hãy sử dụng menu bên trái để truy cập các chức năng quản trị.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 
=======
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
?>

<h2>Chào mừng đến với trang quản trị!</h2>
<p>Đây là trang quản trị của website Cà Phê Đậm Đà.</p>

<div class="dashboard-stats">
    <div class="row" style="display: flex; margin: 0 -15px;">
        <div class="col" style="flex: 1; padding: 0 15px;">
            <div class="stat-box" style="background-color: #3498db; color: white; padding: 20px; border-radius: 5px;">
                <h3>Sản phẩm</h3>
                <p style="font-size: 24px; margin: 0;"><?php echo $totalProducts; ?></p>
            </div>
        </div>
        <div class="col" style="flex: 1; padding: 0 15px;">
            <div class="stat-box" style="background-color: #2ecc71; color: white; padding: 20px; border-radius: 5px;">
                <h3>Người dùng</h3>
                <p style="font-size: 24px; margin: 0;"><?php echo $totalUsers; ?></p>
            </div>
        </div>
        <div class="col" style="flex: 1; padding: 0 15px;">
            <div class="stat-box" style="background-color: #e74c3c; color: white; padding: 20px; border-radius: 5px;">
                <h3>Đơn hàng</h3>
                <p style="font-size: 24px; margin: 0;"><?php echo $totalOrders; ?></p>
            </div>
        </div>
        <div class="col" style="flex: 1; padding: 0 15px;">
            <div class="stat-box" style="background-color: #f39c12; color: white; padding: 20px; border-radius: 5px;">
                <h3>Doanh thu</h3>
                <p style="font-size: 24px; margin: 0;"><?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ</p>
            </div>
        </div>
    </div>
</div>

<div style="margin-top: 30px;">
    <h3>Chức năng có sẵn:</h3>
    <ul>
        <li>Quản lý sản phẩm: Thêm, sửa, xóa sản phẩm</li>
        <li>Quản lý đơn hàng: Xem và cập nhật trạng thái đơn hàng</li>
        <li>Quản lý người dùng: Xem danh sách người dùng đã đăng ký</li>
        <li>Thống kê: Xem thống kê top khách hàng theo doanh số</li>
    </ul>
    
    <p>Hãy sử dụng menu bên trái để truy cập các chức năng quản trị.</p>
</div>

<?php
include 'includes/footer.php';
?> 
>>>>>>> 36298127f1deb9f590e3222166af251932b728fd
