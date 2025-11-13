<?php
// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

$dbChecks = __DIR__ . '/../../includes/db_checks.php';
if (file_exists($dbChecks)) {
    require_once $dbChecks;
}

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
if (function_exists('ensureColumnExists')) {
    ensureColumnExists($conn, 'orders', 'payment_status', "VARCHAR(20) NOT NULL DEFAULT 'pending'", 'payment_method');
}

if (isset($_POST['action']) && $_POST['action'] == 'update_status' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $status_note = isset($_POST['status_note']) ? trim($_POST['status_note']) : '';
    $new_payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : null;
    
    $allowed_statuses = ['pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled'];
    $allowed_payment_statuses = ['pending', 'paid', 'failed', 'refunded'];
    
    $status_valid = in_array($new_status, $allowed_statuses);
    $payment_status_valid = $new_payment_status === null || in_array($new_payment_status, $allowed_payment_statuses, true);
    
    if ($status_valid && $payment_status_valid) {
        // Kiểm tra xem cột status_note có tồn tại trong bảng orders không
        $check_column = "SHOW COLUMNS FROM orders LIKE 'status_note'";
        $column_result = $conn->query($check_column);
        $has_status_note = $column_result->num_rows > 0;
        
        // Kiểm tra cột updated_at
        $check_updated_column = "SHOW COLUMNS FROM orders LIKE 'updated_at'";
        $updated_column_result = $conn->query($check_updated_column);
        $has_updated_at = $updated_column_result->num_rows > 0;
        
        // Xây dựng câu SQL động
        $update_fields = ['status = ?'];
        $params = [$new_status];
        $param_types = 's';
        
        if ($new_payment_status !== null) {
            $update_fields[] = 'payment_status = ?';
            $params[] = $new_payment_status;
            $param_types .= 's';
        }
        
        if ($has_status_note) {
            $update_fields[] = 'status_note = ?';
            $params[] = $status_note;
            $param_types .= 's';
        }
        
        if ($has_updated_at) {
            $update_fields[] = 'updated_at = NOW()';
        }
        
        $params[] = $order_id;
        $param_types .= 'i';
        
        $sql = "UPDATE orders SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param($param_types, ...$params);
            if ($stmt->execute()) {
                $success_message = "Cập nhật trạng thái đơn hàng và thanh toán thành công!";
            } else {
                $error_message = "Không thể cập nhật trạng thái: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error_message = "Không thể chuẩn bị câu lệnh cập nhật.";
        }
    } else {
        if (!$status_valid) {
            $error_message = "Trạng thái đơn hàng không hợp lệ!";
        } else {
            $error_message = "Trạng thái thanh toán không hợp lệ!";
        }
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
    $sql_items = "SELECT * FROM order_items WHERE order_id = ?";
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

$payment_statuses = [
    'pending' => 'Chờ thanh toán',
    'paid' => 'Đã thanh toán',
    'failed' => 'Thanh toán thất bại',
    'refunded' => 'Đã hoàn tiền'
];

$payment_status_classes = [
    'pending' => 'badge-warning',
    'paid' => 'badge-success',
    'failed' => 'badge-danger',
    'refunded' => 'badge-info'
];

// Set page title
$order_title = '';
if (!empty($order['custom_order_id'])) {
    $order_title = htmlspecialchars($order['custom_order_id']);
} elseif (isset($order['order_number'])) {
    $order_title = htmlspecialchars($order['order_number']);
} else {
    $order_title = '#' . $order_id;
}
$page_title = "Chi tiết đơn hàng " . $order_title;

// Include header
require_once __DIR__ . '/../includes/admin-header.php';
?>

<style>
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
        .payment-status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.6rem;
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

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại danh sách
    </a>
</div>
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
                                            <p><strong>Mã đơn hàng:</strong> 
                                                <?php 
                                                    if (!empty($order['custom_order_id'])) {
                                                        echo htmlspecialchars($order['custom_order_id']);
                                                    } elseif (isset($order['order_number'])) {
                                                        echo htmlspecialchars($order['order_number']);
                                                    } else {
                                                        echo '#' . $order_id;
                                                    } 
                                                ?>
                                            </p>
                                            <p><strong>Ngày đặt:</strong> <?php echo isset($order['order_date']) ? date('d/m/Y H:i', strtotime($order['order_date'])) : 'N/A'; ?></p>
                                            <p><strong>Cập nhật lần cuối:</strong> <?php echo isset($order['updated_at']) ? date('d/m/Y H:i', strtotime($order['updated_at'])) : 'N/A'; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</p>
                                            <p><strong>Phương thức thanh toán:</strong> <?php echo $order['payment_method'] ?? 'N/A'; ?></p>
                                            <p>
                                                <strong>Trạng thái thanh toán:</strong>
                                                <?php
                                                $currentPaymentStatus = $order['payment_status'] ?? 'pending';
                                                $paymentStatusLabel = $payment_statuses[$currentPaymentStatus] ?? $currentPaymentStatus;
                                                $paymentStatusClass = $payment_status_classes[$currentPaymentStatus] ?? 'badge-secondary';
                                                ?>
                                                <span class="badge <?php echo $paymentStatusClass; ?> payment-status-badge">
                                                    <?php echo $paymentStatusLabel; ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <form method="POST">
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
                                                <label for="payment_status">Cập nhật trạng thái thanh toán</label>
                                                <select class="form-control" id="payment_status" name="payment_status">
                                                    <?php foreach ($payment_statuses as $key => $label): ?>
                                                        <option value="<?php echo $key; ?>" <?php echo ($key == $currentPaymentStatus) ? 'selected' : ''; ?>>
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
                                                                    <img src="../../<?php echo $item['image']; ?>" alt="<?php echo $item['product_name']; ?>" class="product-img mr-2">
                                                                <?php else: ?>
                                                                    <div class="product-img mr-2 bg-light d-flex align-items-center justify-content-center">
                                                                        <i class="fas fa-image text-muted"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div>
                                                                    <div><?php echo $item['product_name']; ?></div>
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
<?php
// Include footer
require_once __DIR__ . '/../includes/admin-footer.php';
?>
