<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

// Kiểm tra ID đơn hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
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

$order_id = intval($_GET['id']);
$order = null;
$order_items = [];

// Lấy thông tin đơn hàng
$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$order = $result->fetch_assoc();

// Lấy chi tiết đơn hàng
$sql_items = "SELECT oi.*, p.name AS product_name, p.image 
              FROM order_items oi 
              LEFT JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

if ($result_items->num_rows > 0) {
    while ($row = $result_items->fetch_assoc()) {
        $order_items[] = $row;
    }
}

// Cập nhật trạng thái đơn hàng nếu có yêu cầu
if (isset($_POST['update_status']) && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $sql_update = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_status, $order_id);
        
        if ($stmt_update->execute() && $stmt_update->affected_rows > 0) {
            $success_message = "Cập nhật trạng thái đơn hàng thành công!";
            // Cập nhật lại thông tin đơn hàng sau khi cập nhật
            $order['status'] = $new_status;
        } else {
            $error_message = "Không thể cập nhật trạng thái đơn hàng.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng - Cà Phê Đậm Đà</title>
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
        .btn {
            padding: 10px 20px;
            background-color: #d4a373;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #c18f5c;
        }
        .order-details {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            color: #3c2f2f;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #d4a373;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-box {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .info-label {
            width: 40%;
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            width: 60%;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        .product-info {
            display: flex;
            align-items: center;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        .order-totals {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-top: 30px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .total-row:last-child {
            font-weight: bold;
            font-size: 1.2em;
            color: #3c2f2f;
            border-top: 2px solid #d4a373;
            border-bottom: none;
            padding-top: 10px;
            margin-top: 10px;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .status-select {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            margin-left: 10px;
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
                <h1 class="page-title">Chi tiết đơn hàng #<?php echo $order['order_number']; ?></h1>
                <a href="index.php" class="btn">Quay lại danh sách</a>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="order-details">
                <h2 class="section-title">Thông tin đơn hàng</h2>
                
                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-row">
                            <div class="info-label">Mã đơn hàng:</div>
                            <div class="info-value"><?php echo $order['order_number']; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Ngày đặt hàng:</div>
                            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'] ?? date('Y-m-d H:i:s'))); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Khách hàng:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_name'] ?? 'Khách vãng lai'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_email'] ?? $order['email']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Trạng thái:</div>
                            <div class="info-value">
                                <?php
                                $status_class = '';
                                $status_text = '';
                                
                                switch($order['status']) {
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
                                        $status_text = $order['status'];
                                }
                                ?>
                                <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Phương thức thanh toán:</div>
                            <div class="info-value">
                                <?php 
                                $payment_methods = [
                                    'cod' => 'Thanh toán khi nhận hàng',
                                    'banking' => 'Chuyển khoản ngân hàng',
                                    'momo' => 'Ví MoMo',
                                    'vnpay' => 'VN Pay'
                                ];
                                echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-box">
                        <div class="info-row">
                            <div class="info-label">Người nhận:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['full_name']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Số điện thoại:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['phone']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Địa chỉ:</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($order['address']); ?>, 
                                <?php echo htmlspecialchars($order['district']); ?>, 
                                <?php echo htmlspecialchars($order['city']); ?>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Ghi chú:</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['notes'] ?? 'Không có ghi chú'); ?></div>
                        </div>
                    </div>
                </div>
                
                <h2 class="section-title">Chi tiết sản phẩm</h2>
                
                <?php if (empty($order_items)): ?>
                    <p>Không có thông tin về sản phẩm.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <img src="../../<?php echo $item['image'] ?? 'uploads/products/default.jpg'; ?>" class="product-image" alt="<?php echo $item['product_name']; ?>">
                                            <div><?php echo $item['product_name'] ?? 'Sản phẩm #'.$item['product_id']; ?></div>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <div>Tạm tính:</div>
                            <div><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</div>
                        </div>
                        <div class="total-row">
                            <div>Phí vận chuyển:</div>
                            <div>Miễn phí</div>
                        </div>
                        <div class="total-row">
                            <div>Tổng cộng:</div>
                            <div><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="index.php" class="btn">Quay lại danh sách</a>
                    
                    <form method="post" action="">
                        <label for="status">Cập nhật trạng thái:</label>
                        <select name="status" id="status" class="status-select">
                            <option value="pending" <?php if ($order['status'] === 'pending') echo 'selected'; ?>>Chờ xác nhận</option>
                            <option value="processing" <?php if ($order['status'] === 'processing') echo 'selected'; ?>>Đang xử lý</option>
                            <option value="shipped" <?php if ($order['status'] === 'shipped') echo 'selected'; ?>>Đang giao hàng</option>
                            <option value="delivered" <?php if ($order['status'] === 'delivered') echo 'selected'; ?>>Đã giao hàng</option>
                            <option value="cancelled" <?php if ($order['status'] === 'cancelled') echo 'selected'; ?>>Đã hủy</option>
                        </select>
                        <button type="submit" name="update_status" class="btn">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 