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
$database = "coffee_shop";

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

// Kiểm tra xem bảng users có cột name không
$sql_check_name = "SHOW COLUMNS FROM `users` LIKE 'name'";
$result_check_name = $conn->query($sql_check_name);
$has_name_column = ($result_check_name->num_rows > 0);

// Lấy danh sách đơn hàng
if ($has_name_column) {
    $sql = "SELECT o.*, u.name as customer_name 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC";
} else {
    // Nếu không có cột name, sử dụng email hoặc id làm customer_name
    $sql = "SELECT o.*, u.email as customer_name 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC";
}

$result = $conn->query($sql);
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

            <table>
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo isset($row['order_number']) ? $row['order_number'] : 'ĐH-' . $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Khách vãng lai'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
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
                        <?php endwhile; ?>
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