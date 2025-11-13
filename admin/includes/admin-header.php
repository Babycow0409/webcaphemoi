<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION["admin"]) && !isset($_SESSION["admin_id"])) {
    // Tính toán đường dẫn đúng
    $depth = substr_count($_SERVER['PHP_SELF'], '/') - 2;
    $path = str_repeat('../', $depth) . 'login.php';
    header("Location: " . $path);
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

// Tính toán đường dẫn base cho assets
// Xác định số cấp thư mục từ file hiện tại đến thư mục admin
$script_path = $_SERVER['PHP_SELF'];
$script_dir = dirname($script_path);

// Tìm vị trí /admin trong đường dẫn
$admin_pos = strpos($script_dir, '/admin');
if ($admin_pos !== false) {
    // Lấy phần sau /admin
    $after_admin = substr($script_dir, $admin_pos + 6); // +6 để bỏ qua '/admin'
    if ($after_admin == '' || $after_admin == '/') {
        // Đang ở trong admin/ (không có subdirectory)
        $base_path = '';
    } else {
        // Đang ở trong admin/subdirectory/ (ví dụ: admin/orders/)
        // Cần lên 1 cấp để về admin/
        $base_path = '../';
    }
} else {
    // Nếu không tìm thấy /admin, có thể đang ở root
    $base_path = 'admin/';
}

// Xác định URL hiện tại để highlight menu item đang active
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Xác định active menu
$is_dashboard = ($current_page == 'index.php' && $current_dir == 'admin');
$is_products = ($current_dir == 'products');
$is_orders = ($current_dir == 'orders');
$is_users = ($current_dir == 'users');
$is_statistics = ($current_dir == 'statistics' || $current_dir == 'analytics');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?> - Cà Phê Đậm Đà</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
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
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.2);
            font-weight: bold;
        }
        .content {
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            color: #495057;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .header h2 {
            color: #495057;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
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
                        <a class="nav-link <?php echo $is_dashboard ? 'active' : ''; ?>" href="<?php echo $base_path; ?>index.php">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $is_products ? 'active' : ''; ?>" href="<?php echo $base_path; ?>products/index.php">
                            <i class="fas fa-coffee mr-2"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $is_orders ? 'active' : ''; ?>" href="<?php echo $base_path; ?>orders/index.php">
                            <i class="fas fa-shopping-cart mr-2"></i> Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $is_users ? 'active' : ''; ?>" href="<?php echo $base_path; ?>users/index.php">
                            <i class="fas fa-users mr-2"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $is_statistics ? 'active' : ''; ?>" href="<?php echo $base_path; ?>statistics/top-customers.php">
                            <i class="fas fa-chart-bar mr-2"></i> Thống kê
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>logout.php">
                            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <div class="col-md-10">
                <div class="header d-flex justify-content-between align-items-center">
                    <h2><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h2>
                    <div>
                        <i class="fas fa-user mr-1"></i> 
                        <?php echo isset($admin["name"]) ? $admin["name"] : 'Admin'; ?>
                    </div>
                </div>
                
                <div class="content">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
