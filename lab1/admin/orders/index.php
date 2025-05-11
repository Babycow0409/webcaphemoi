<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

// Kết nối CSDL
$host = "localhost";
$username = "root"; 
$password = "";
$database = "lab1";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Cập nhật trạng thái đơn hàng nếu có yêu cầu
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $order_id = intval($_GET['id']);
    $new_status = $_GET['status'];
    
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $success_message = "Cập nhật trạng thái đơn hàng thành công!";
        } else {
            $error_message = "Không thể cập nhật trạng thái đơn hàng.";
        }
    }
}

// Kiểm tra bảng orders có tồn tại không
$orders_exist = $conn->query("SHOW TABLES LIKE 'orders'")->num_rows > 0;

if (!$orders_exist) {
    // Tạo bảng orders nếu chưa tồn tại
    $sql_create_orders = "CREATE TABLE `orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `total_amount` decimal(10,2) NOT NULL,
        `status` varchar(50) NOT NULL DEFAULT 'pending',
        `payment_method` varchar(50) NOT NULL DEFAULT 'cash',
        `shipping_address` text NOT NULL,
        `order_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    )";
    $conn->query($sql_create_orders);
    
    echo "<div class='alert alert-info'>Bảng orders vừa được tạo. Chưa có đơn hàng nào.</div>";
    $orders = [];
} else {
    // Kiểm tra cấu trúc bảng orders để biết các cột có sẵn
    $orders_columns = $conn->query("SHOW COLUMNS FROM orders");
    $has_order_date = false;
    $order_column = "id"; // Mặc định sắp xếp theo id
    
    if ($orders_columns) {
        while ($col = $orders_columns->fetch_assoc()) {
            if ($col['Field'] == 'order_date') {
                $has_order_date = true;
                break;
            }
            if ($col['Field'] == 'created_at') {
                $order_column = "created_at";
            }
        }
    }
    
    // Nếu không có cột order_date, thử thêm vào
    if (!$has_order_date) {
        $add_column_sql = "ALTER TABLE orders ADD COLUMN order_date datetime DEFAULT CURRENT_TIMESTAMP";
        $conn->query($add_column_sql);
        
        // Cập nhật giá trị order_date từ cột created_at nếu có
        if ($order_column == "created_at") {
            $update_sql = "UPDATE orders SET order_date = created_at";
            $conn->query($update_sql);
        }
    }
    
    // Sử dụng order_date nếu có, nếu không dùng created_at hoặc id
    $order_by = $has_order_date ? "o.order_date" : ($order_column == "created_at" ? "o.created_at" : "o.id");
    
    // Lấy danh sách đơn hàng với các bộ lọc
    $where_clauses = [];
    $params = [];
    $param_types = "";

    // Lọc theo trạng thái
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where_clauses[] = "o.status = ?";
        $params[] = $_GET['status'];
        $param_types .= "s";
    }

    // Lọc theo khoảng thời gian
    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $date_from = date('Y-m-d 00:00:00', strtotime($_GET['date_from']));
        $where_clauses[] = "o.order_date >= ?";
        $params[] = $date_from;
        $param_types .= "s";
    }

    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $date_to = date('Y-m-d 23:59:59', strtotime($_GET['date_to']));
        $where_clauses[] = "o.order_date <= ?";
        $params[] = $date_to;
        $param_types .= "s";
    }

    // Lọc theo thành phố/quận/huyện
    if (isset($_GET['location']) && !empty($_GET['location'])) {
        $location_search = '%' . $_GET['location'] . '%';
        $where_clauses[] = "o.shipping_address LIKE ?";
        $params[] = $location_search;
        $param_types .= "s";
    }

    // Tạo mệnh đề WHERE nếu có điều kiện lọc
    $where_sql = "";
    if (!empty($where_clauses)) {
        $where_sql = " WHERE " . implode(" AND ", $where_clauses);
    }

    // Mảng trạng thái đơn hàng
    $statuses = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao hàng',
        'delivered' => 'Đã giao hàng',
        'cancelled' => 'Đã hủy'
    ];

    // Mảng classes CSS cho từng trạng thái
    $status_classes = [
        'pending' => 'warning',
        'confirmed' => 'primary',
        'processing' => 'info',
        'shipping' => 'info',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];

    // Lấy danh sách đơn hàng
    $sql = "SELECT o.*, u.fullname as customer_name 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id " . $where_sql . "
            ORDER BY o.order_date DESC";
    
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    $orders = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }
        body {
            background-color: #f8f9fa;
            color: #212529;
            line-height: 1.6;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #3c2f2f;
            color: white;
            padding: 20px 0;
            height: 100vh;
            position: fixed;
        }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .menu-item {
            padding: 10px 20px;
            display: block;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .page-title {
            font-family: 'Playfair Display', serif;
            color: #3c2f2f;
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
            margin-bottom: 30px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        .pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ff9800;
        }
        .processing {
            background-color: rgba(13, 110, 253, 0.2);
            color: #0d6efd;
        }
        .shipped {
            background-color: rgba(23, 162, 184, 0.2);
            color: #17a2b8;
        }
        .delivered, .completed {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        .cancelled {
            background-color: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-right: 5px;
            text-align: center;
        }
        .view-btn {
            background-color: #17a2b8;
            color: white;
        }
        .edit-btn {
            background-color: #ffc107;
            color: #212529;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .status-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">Cà Phê Đậm Đà</div>
                <div>Quản trị viên</div>
            </div>
            <div class="sidebar-menu">
                <a href="../index.php" class="menu-item">Tổng quan</a>
                <a href="../products/index.php" class="menu-item">Sản phẩm</a>
                <a href="index.php" class="menu-item active">Đơn hàng</a>
                <a href="../users/index.php" class="menu-item">Người dùng</a>
                <a href="../statistics/top-customers.php" class="menu-item">Thống kê</a>
                <a href="../logout.php" class="menu-item">Đăng xuất</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Quản lý đơn hàng</h1>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Form lọc đơn hàng -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0"><i class="fas fa-filter mr-2"></i>Lọc đơn hàng</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="status"><i class="fas fa-tag mr-1"></i>Trạng thái</label>
                                <select name="status" id="status" class="form-control form-control-sm">
                                    <option value="">-- Tất cả trạng thái --</option>
                                    <?php foreach ($statuses as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo isset($_GET['status']) && $_GET['status'] === $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="date_from"><i class="far fa-calendar-alt mr-1"></i>Từ ngày</label>
                                <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" 
                                       value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="date_to"><i class="far fa-calendar-alt mr-1"></i>Đến ngày</label>
                                <input type="date" name="date_to" id="date_to" class="form-control form-control-sm"
                                       value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-group">
                                <label for="location"><i class="fas fa-map-marker-alt mr-1"></i>Địa điểm</label>
                                <input type="text" name="location" id="location" class="form-control form-control-sm" 
                                       placeholder="Quận/Huyện/Thành phố"
                                       value="<?php echo isset($_GET['location']) ? $_GET['location'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <i class="fas fa-search mr-1"></i>Tìm kiếm
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm ml-2">
                                <i class="fas fa-redo-alt mr-1"></i>Đặt lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th>Địa chỉ</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $row): ?>
                            <tr>
                                <td><?php echo isset($row['order_number']) ? $row['order_number'] : 'ĐH-' . $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Khách vãng lai'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?></td>
                                <td><?php echo number_format($row['total_amount'], 0, ',', '.'); ?> VNĐ</td>
                                <td>
                                    <?php 
                                    $payment_methods = [
                                        'cod' => 'Thanh toán khi nhận hàng',
                                        'banking' => 'Chuyển khoản ngân hàng',
                                        'momo' => 'Ví MoMo',
                                        'vnpay' => 'VN Pay'
                                    ];
                                    echo isset($payment_methods[$row['payment_method']]) ? $payment_methods[$row['payment_method']] : $row['payment_method'];
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    switch($row['status'] ?? 'pending') {
                                        case 'pending':
                                            $status_class = 'pending';
                                            $status_text = 'Chờ xác nhận';
                                            break;
                                        case 'processing':
                                            $status_class = 'processing';
                                            $status_text = 'Đang xử lý';
                                            break;
                                        case 'shipped':
                                            $status_class = 'shipped';
                                            $status_text = 'Đang giao hàng';
                                            break;
                                        case 'delivered':
                                        case 'completed':
                                            $status_class = 'delivered';
                                            $status_text = 'Đã giao hàng';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'cancelled';
                                            $status_text = 'Đã hủy';
                                            break;
                                        default:
                                            $status_class = 'pending';
                                            $status_text = $row['status'] ?? 'Chờ xác nhận';
                                    }
                                    ?>
                                    <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['shipping_address'] ?? ''); ?></td>
                                <td>
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="action-btn view-btn">Xem</a>
                                    <form method="get" action="" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <select name="status" class="status-select" onchange="this.form.submit()">
                                            <option value="">Cập nhật trạng thái</option>
                                            <option value="pending" <?php if (($row['status'] ?? '') === 'pending') echo 'selected'; ?>>Chờ xác nhận</option>
                                            <option value="processing" <?php if (($row['status'] ?? '') === 'processing') echo 'selected'; ?>>Đang xử lý</option>
                                            <option value="shipped" <?php if (($row['status'] ?? '') === 'shipped') echo 'selected'; ?>>Đang giao hàng</option>
                                            <option value="delivered" <?php if (($row['status'] ?? '') === 'delivered') echo 'selected'; ?>>Đã giao hàng</option>
                                            <option value="cancelled" <?php if (($row['status'] ?? '') === 'cancelled') echo 'selected'; ?>>Đã hủy</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">Không có đơn hàng nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
