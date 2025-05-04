<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION["admin"])) {
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

// Kiểm tra ID đơn hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?error=Không tìm thấy ID đơn hàng");
    exit();
}

$order_id = intval($_GET['id']);

// Cập nhật trạng thái đơn hàng nếu có yêu cầu
if (isset($_POST['action']) && $_POST['action'] == 'update_status' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $status_note = isset($_POST['status_note']) ? trim($_POST['status_note']) : '';
    
    $allowed_statuses = ['pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $sql = "UPDATE orders SET status = ?, status_note = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $new_status, $status_note, $order_id);
        
        if ($stmt->execute()) {
            $success_message = "Cập nhật trạng thái đơn hàng thành công!";
        } else {
            $error_message = "Không thể cập nhật trạng thái đơn hàng: " . $conn->error;
        }
    } else {
        $error_message = "Trạng thái không hợp lệ!";
    }
}

// Lấy thông tin đơn hàng
$sql = "SELECT o.*, u.fullname, u.email, u.phone 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php?error=Không tìm thấy đơn hàng");
    exit();
}

$order = $result->fetch_assoc();

// Lấy chi tiết đơn hàng
$order_items = [];

// Kiểm tra xem bảng order_items đã tồn tại chưa
$check_table = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($check_table->num_rows > 0) {
    // Truy vấn lấy danh sách sản phẩm trong đơn hàng
    $sql_items = "SELECT oi.*, p.name, p.image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $order_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();

    if ($result_items->num_rows > 0) {
        while ($item = $result_items->fetch_assoc()) {
            $order_items[] = $item;
        }
    }
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?> - Admin</title>
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
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .order-status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 3px;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .timeline {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }
        .timeline::after {
            content: '';
            position: absolute;
            width: 2px;
            background-color: #dee2e6;
            top: 0;
            bottom: 0;
            left: 50px;
            margin-left: -1px;
        }
        .timeline-item {
            padding: 10px 40px 10px 70px;
            position: relative;
            margin-bottom: 15px;
        }
        .timeline-badge {
            position: absolute;
            left: 50px;
            transform: translateX(-50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            z-index: 1;
        }
        .timeline-content {
            padding: 15px;
            background-color: white;
            position: relative;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
        .alert {
            margin-bottom: 20px;
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
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../products/index.php">
                            <i class="fas fa-coffee mr-2"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-shopping-cart mr-2"></i> Đơn hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../users/index.php">
                            <i class="fas fa-users mr-2"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../statistics/top-customers.php">
                            <i class="fas fa-chart-bar mr-2"></i> Thống kê
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <div class="col-md-10">
                <div class="header d-flex justify-content-between align-items-center">
                    <h2>Chi tiết đơn hàng #<?php echo $order_id; ?></h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
                
                <div class="content">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="m-0">Thông tin đơn hàng</h5>
                                        <span class="order-status bg-<?php echo $status_classes[$order['status']] ?? 'secondary'; ?>">
                                            <?php echo $statuses[$order['status']] ?? $order['status']; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p><strong>Mã đơn hàng:</strong> #<?php echo $order_id; ?></p>
                                            <p><strong>Ngày đặt:</strong> <?php echo isset($order['order_date']) ? date('d/m/Y H:i', strtotime($order['order_date'])) : 'N/A'; ?></p>
                                            <p><strong>Cập nhật lần cuối:</strong> <?php echo isset($order['updated_at']) ? date('d/m/Y H:i', strtotime($order['updated_at'])) : 'N/A'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</p>
                                            <p><strong>Phương thức thanh toán:</strong> <?php echo $order['payment_method'] ?? 'N/A'; ?></p>
                                            <p><strong>Trạng thái thanh toán:</strong> <?php echo $order['payment_status'] ?? 'Chưa thanh toán'; ?></p>
                                        </div>
                                    </div>
                                    
                                    <form method="POST" class="mt-3">
                                        <div class="form-group">
                                            <label for="status">Cập nhật trạng thái đơn hàng</label>
                                            <select class="form-control" id="status" name="status">
                                                <?php foreach ($statuses as $value => $label): ?>
                                                    <option value="<?php echo $value; ?>" <?php echo ($value == $order['status']) ? 'selected' : ''; ?>>
                                                        <?php echo $label; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="status_note">Ghi chú trạng thái (tùy chọn)</label>
                                            <textarea class="form-control" id="status_note" name="status_note" rows="2"><?php echo htmlspecialchars($order['status_note'] ?? ''); ?></textarea>
                                        </div>
                                        <input type="hidden" name="action" value="update_status">
                                        <button type="submit" class="btn btn-primary">Cập nhật trạng thái</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="m-0">Sản phẩm trong đơn hàng</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Sản phẩm</th>
                                                <th>Giá</th>
                                                <th>Số lượng</th>
                                                <th class="text-right">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($order_items)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Không có thông tin sản phẩm</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($order_items as $item): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php if (!empty($item['image'])): ?>
                                                                    <img src="../../<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="product-img mr-2">
                                                                <?php else: ?>
                                                                    <div class="product-img mr-2 bg-light d-flex align-items-center justify-content-center">
                                                                        <i class="fas fa-image text-muted"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div>
                                                                    <div><?php echo $item['name']; ?></div>
                                                                    <?php if (isset($item['options']) && !empty($item['options'])): ?>
                                                                        <small class="text-muted"><?php echo $item['options']; ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                                        <td><?php echo $item['quantity']; ?></td>
                                                        <td class="text-right"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <tr>
                                                    <td colspan="3" class="text-right"><strong>Tổng cộng:</strong></td>
                                                    <td class="text-right"><strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</strong></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="m-0">Thông tin khách hàng</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Họ tên:</strong> <?php echo $order['fullname'] ?? 'N/A'; ?></p>
                                    <p><strong>Email:</strong> <?php echo $order['email'] ?? 'N/A'; ?></p>
                                    <p><strong>Số điện thoại:</strong> <?php echo $order['phone'] ?? 'N/A'; ?></p>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="m-0">Địa chỉ giao hàng</h5>
                                </div>
                                <div class="card-body">
                                    <p><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'Không có thông tin')); ?></p>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="m-0">Lịch sử trạng thái</h5>
                                </div>
                                <div class="card-body p-3">
                                    <div class="timeline">
                                        <?php
                                        // Truy vấn lịch sử trạng thái nếu có bảng order_history
                                        $check_history_table = $conn->query("SHOW TABLES LIKE 'order_history'");
                                        $history_items = [];
                                        
                                        if ($check_history_table->num_rows > 0) {
                                            $sql_history = "SELECT * FROM order_history WHERE order_id = ? ORDER BY created_at DESC";
                                            $stmt_history = $conn->prepare($sql_history);
                                            $stmt_history->bind_param("i", $order_id);
                                            $stmt_history->execute();
                                            $result_history = $stmt_history->get_result();
                                            
                                            if ($result_history->num_rows > 0) {
                                                while ($history = $result_history->fetch_assoc()) {
                                                    $history_items[] = $history;
                                                }
                                            }
                                        }
                                        
                                        // Nếu không có lịch sử hoặc bảng chưa tồn tại, hiển thị trạng thái hiện tại
                                        if (empty($history_items)) {
                                            $history_items[] = [
                                                'status' => $order['status'],
                                                'note' => $order['status_note'] ?? '',
                                                'created_at' => $order['updated_at'] ?? $order['order_date'] ?? date('Y-m-d H:i:s')
                                            ];
                                        }
                                        
                                        foreach ($history_items as $history):
                                            $status_class = $status_classes[$history['status']] ?? 'secondary';
                                        ?>
                                            <div class="timeline-item">
                                                <div class="timeline-badge bg-<?php echo $status_class; ?>">
                                                    <i class="fas fa-check text-white"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="d-flex justify-content-between">
                                                        <h6 class="mb-1">
                                                            <span class="badge badge-<?php echo $status_class; ?> status-badge">
                                                                <?php echo $statuses[$history['status']] ?? $history['status']; ?>
                                                            </span>
                                                        </h6>
                                                        <small class="text-muted">
                                                            <?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                    <?php if (!empty($history['note'])): ?>
                                                        <p class="mb-0 mt-2 small"><?php echo nl2br(htmlspecialchars($history['note'])); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
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