<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Kiểm tra và thêm cột order_number nếu chưa tồn tại
$check_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_number'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN order_number VARCHAR(30) UNIQUE AFTER id");
}

// Kiểm tra và thêm cột order_date nếu chưa tồn tại
$check_date_column = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_date'");
if ($check_date_column->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN order_date DATETIME DEFAULT CURRENT_TIMESTAMP AFTER payment_method");
    
    // Cập nhật order_date từ created_at nếu có
    $check_created_at = $conn->query("SHOW COLUMNS FROM orders LIKE 'created_at'");
    if ($check_created_at->num_rows > 0) {
        $conn->query("UPDATE orders SET order_date = created_at WHERE created_at IS NOT NULL");
    }
}

// Hiển thị thông tin đơn hàng của người dùng
$query = "SELECT * FROM orders WHERE user_id = {$_SESSION['user_id']} ORDER BY order_date DESC";
$result = $conn->query($query);
$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    // Cập nhật dữ liệu thiếu trong bảng
    foreach ($orders as $order) {
        $updates = [];
        
        // Kiểm tra và cập nhật các trường thiếu
        if (empty($order['order_date']) && !empty($order['created_at'])) {
            $updates[] = "order_date = '{$order['created_at']}'";
        } elseif (empty($order['order_date'])) {
            $now = date('Y-m-d H:i:s');
            $updates[] = "order_date = '$now'";
        }
        
        // Nếu không có total_amount, điền giá trị
        if (empty($order['total_amount'])) {
            // Truy vấn items để tính tổng
            $items_query = "SELECT SUM(price * quantity) as total FROM order_items WHERE order_id = {$order['id']}";
            $items_result = $conn->query($items_query);
            if ($items_result->num_rows > 0) {
                $total_row = $items_result->fetch_assoc();
                $total = $total_row['total'] ?: 0;
                $updates[] = "total_amount = $total";
            } else {
                $updates[] = "total_amount = 0";
            }
        }
        
        // Cập nhật dữ liệu nếu có thay đổi
        if (!empty($updates)) {
            $update_sql = "UPDATE orders SET " . implode(", ", $updates) . " WHERE id = {$order['id']}";
            $conn->query($update_sql);
        }
    }
}

// Cập nhật order_number cho các đơn hàng chưa có
foreach ($orders as &$order) {
    if (empty($order['order_number'])) {
        $order_id = $order['id'];
        // Sử dụng order_date nếu có, nếu không dùng thời gian hiện tại
        $date_str = !empty($order['order_date']) ? $order['order_date'] : date('YmdHis');
        $new_order_number = 'ORDER' . date('YmdHis', strtotime($date_str)) . rand(100, 999);
        
        $stmt = $conn->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
        $stmt->bind_param("si", $new_order_number, $order_id);
        $stmt->execute();
        
        $order['order_number'] = $new_order_number;
    }
    
    // Kiểm tra và thiết lập giá trị mặc định cho các trường thiếu
    if (empty($order['created_at']) && !empty($order['order_date'])) {
        $order['created_at'] = $order['order_date'];
    } elseif (empty($order['created_at'])) {
        $order['created_at'] = date('Y-m-d H:i:s');
    }
    
    // Đảm bảo order_total có giá trị
    if (empty($order['order_total']) && !empty($order['total_amount'])) {
        $order['order_total'] = $order['total_amount'];
    } elseif (empty($order['order_total'])) {
        $order['order_total'] = 0;
    }
}

// Xem chi tiết đơn hàng
$order_details = [];
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    // Kiểm tra quyền truy cập đơn hàng
    $stmt = $conn->prepare("SELECT o.* FROM orders o WHERE o.id = ? AND o.user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Lấy thông tin người dùng để hiển thị
        $user_stmt = $conn->prepare("SELECT fullname, email, phone FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_row = $user_result->fetch_assoc()) {
            $order['fullname'] = $user_row['fullname'];
            $order['email'] = $user_row['email'];
            $order['phone'] = $user_row['phone'];
            $order['address'] = 'Không có thông tin';
            $order['city'] = 'Không có thông tin';
        }
        
        // Lấy chi tiết đơn hàng với hình ảnh sản phẩm
        $stmt = $conn->prepare("
            SELECT oi.*, p.image 
            FROM order_items oi 
            LEFT JOIN products p ON oi.id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $order_items = [];
        while ($row = $result->fetch_assoc()) {
            $order_items[] = $row;
        }
        
        $order_details = [
            'order' => $order,
            'items' => $order_items
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi | Coffee Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Main container styles */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        /* Profile layout */
        .profile-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: 30px 0;
        }
        
        .profile-sidebar {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
        }
        
        .profile-content {
            flex: 3;
            min-width: 300px;
        }
        
        /* Sidebar styling */
        .profile-menu {
            background-color: #f8f6f2;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(60,47,47,0.04);
        }
        
        .profile-menu h3 {
            font-size: 1.2rem;
            color: #3c2f2f;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .profile-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .profile-menu li {
            margin-bottom: 12px;
        }
        
        .profile-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: #5d4037;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .profile-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .profile-menu a:hover {
            background-color: rgba(212, 163, 115, 0.1);
            color: #d4a373;
        }
        
        .profile-menu a.active {
            background-color: #d4a373;
            color: white;
        }
        
        /* Content area styling */
        .profile-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 24px rgba(60,47,47,0.08);
        }
        
        .profile-card h2 {
            color: #3c2f2f;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        /* Button styling */
        .btn-primary {
            background-color: #d4a373;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: background 0.2s;
            font-size: 0.95rem;
        }
        
        .btn-primary:hover {
            background-color: #b6894c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 163, 115, 0.2);
        }
        
        /* Order list table styling */
        .order-list {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        }
        
        .order-list th, .order-list td {
            padding: 15px 20px;
            text-align: left;
        }
        
        .order-list th {
            background-color: #f8f6f2;
            font-weight: 600;
            color: #3c2f2f;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .order-list td {
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-list tr:last-child td {
            border-bottom: none;
        }
        
        .order-list tr:hover td {
            background-color: rgba(248, 246, 242, 0.5);
        }
        
        /* Order status badge styling */
        .order-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            text-align: center;
            min-width: 100px;
        }
        
        .status-pending {
            background-color: #ffc107;
        }
        
        .status-confirmed {
            background-color: #17a2b8;
        }
        
        .status-processing {
            background-color: #007bff;
        }
        
        .status-shipping, .status-shipped {
            background-color: #6f42c1;
        }
        
        .status-delivered {
            background-color: #28a745;
        }
        
        .status-cancelled {
            background-color: #dc3545;
        }
        
        /* Order detail styling */
        .order-detail {
            background-color: #f8f6f2;
            padding: 25px;
            border-radius: 10px;
            margin-top: 25px;
        }
        
        .order-detail h3 {
            color: #3c2f2f;
            font-size: 1.2rem;
            margin: 20px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .order-detail h3:first-child {
            margin-top: 0;
        }
        
        .order-detail p {
            margin: 10px 0;
            line-height: 1.5;
            color: #5d4037;
        }
        
        .order-detail strong {
            color: #3c2f2f;
            font-weight: 600;
        }
        
        /* Order items table styling */
        .order-items {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            background-color: white;
        }
        
        .order-items th, .order-items td {
            padding: 15px;
            text-align: left;
        }
        
        .order-items th {
            background-color: #f0f0f0;
            color: #3c2f2f;
            font-weight: 600;
        }
        
        .order-items td {
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-items tr:last-child td {
            border-bottom: none;
        }
        
        .order-items tfoot td {
            background-color: #f8f6f2;
            font-weight: 600;
        }
        
        /* Back link styling */
        .back-link {
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            color: #d4a373;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        .back-link:hover {
            color: #b6894c;
            transform: translateX(-3px);
        }
        
        /* Product info styling */
        .product-info {
            display: flex;
            align-items: center;
        }
        
        .product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
            border: 1px solid #eee;
        }
        
        .product-name {
            font-weight: 500;
            color: #3c2f2f;
        }
        
        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 60px 0;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #d4a373;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: #3c2f2f;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-sidebar {
                max-width: 100%;
            }
            
            .order-list, .order-items {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .order-status {
                min-width: 80px;
                font-size: 0.8rem;
                padding: 5px 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-menu">
                    <h3>Tài khoản của tôi</h3>
                    <ul>
                        <li><a href="profile.php"><i class="fas fa-user"></i> Thông tin cá nhân</a></li>
                        <li><a href="address-book.php"><i class="fas fa-map-marker-alt"></i> Sổ địa chỉ</a></li>
                        <li><a href="my-orders.php" class="active"><i class="fas fa-shopping-bag"></i> Đơn hàng của tôi</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="profile-content">
                <div class="profile-card">
                    <?php if (!empty($order_details)): ?>
                        <a href="my-orders.php" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại danh sách đơn hàng</a>
                        <h2>Chi tiết đơn hàng #<?php echo $order_details['order']['order_number']; ?></h2>
                        
                        <div class="order-detail">
                            <h3><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h3>
                            <p><strong>Mã đơn hàng:</strong> <?php echo $order_details['order']['order_number']; ?></p>
                            <p><strong>Ngày đặt:</strong> <?php 
                                $created_date = isset($order_details['order']['created_at']) ? $order_details['order']['created_at'] : 
                                               (isset($order_details['order']['order_date']) ? $order_details['order']['order_date'] : date('Y-m-d H:i:s'));
                                echo date('d/m/Y H:i', strtotime($created_date)); 
                            ?></p>
                            <p><strong>Trạng thái:</strong> 
                                <span class="order-status status-<?php echo strtolower($order_details['order']['status']); ?>">
                                    <?php
                                    $status_text = '';
                                    switch ($order_details['order']['status']) {
                                        case 'pending':
                                            $status_text = 'Chờ xác nhận';
                                            break;
                                        case 'confirmed':
                                            $status_text = 'Đã xác nhận';
                                            break;
                                        case 'processing':
                                            $status_text = 'Đang xử lý';
                                            break;
                                        case 'shipping':
                                            $status_text = 'Đang giao hàng';
                                            break;
                                        case 'delivered':
                                            $status_text = 'Đã giao hàng';
                                            break;
                                        case 'cancelled':
                                            $status_text = 'Đã hủy';
                                            break;
                                        default:
                                            $status_text = ucfirst($order_details['order']['status']);
                                    }
                                    echo $status_text;
                                    ?>
                                </span>
                            </p>
                            <p><strong>Tổng tiền:</strong> <?php echo number_format($order_details['order']['total_amount'], 0, ',', '.'); ?>đ</p>
                            <p><strong>Phương thức thanh toán:</strong> 
                                <?php 
                                $payment_method = $order_details['order']['payment_method'];
                                switch(strtolower($payment_method)) {
                                    case 'cod':
                                        echo '<i class="fas fa-money-bill-wave"></i> Thanh toán khi nhận hàng';
                                        break;
                                    case 'bank_transfer':
                                        echo '<i class="fas fa-university"></i> Chuyển khoản ngân hàng';
                                        break;
                                    case 'credit_card':
                                        echo '<i class="far fa-credit-card"></i> Thẻ tín dụng';
                                        break;
                                    default:
                                        echo $payment_method;
                                }
                                ?>
                            </p>
                            
                            <h3><i class="fas fa-shipping-fast"></i> Thông tin giao hàng</h3>
                            <p><strong>Họ tên:</strong> <?php echo $order_details['order']['fullname']; ?></p>
                            <p><strong>Email:</strong> <?php echo $order_details['order']['email']; ?></p>
                            <p><strong>Số điện thoại:</strong> <?php echo $order_details['order']['phone']; ?></p>
                            <p><strong>Địa chỉ:</strong> <?php echo $order_details['order']['shipping_address']; ?></p>
                            
                            <h3><i class="fas fa-box-open"></i> Sản phẩm đã đặt</h3>
                            <table class="order-items">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_details['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <?php if (!empty($item['image'])): ?>
                                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['product_name']; ?>" class="product-image" onerror="this.src='images/default-product.jpg'">
                                                    <?php else: ?>
                                                        <img src="images/default-product.jpg" alt="<?php echo $item['product_name']; ?>" class="product-image">
                                                    <?php endif; ?>
                                                    <span class="product-name"><?php echo $item['product_name']; ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                                            <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align: right;"><strong>Tổng tiền:</strong></td>
                                        <td><?php echo number_format($order_details['order']['total_amount'], 0, ',', '.'); ?>đ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <h2><i class="fas fa-shopping-bag"></i> Đơn hàng của tôi</h2>
                        
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <h3>Bạn chưa có đơn hàng nào</h3>
                                <p>Hãy mua sắm và trải nghiệm dịch vụ của chúng tôi</p>
                                <a href="products.php" class="btn-primary">
                                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                                </a>
                            </div>
                        <?php else: ?>
                            <table class="order-list">
                                <thead>
                                    <tr>
                                        <th>Mã đơn hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                if (isset($order['order_number'])) {
                                                    echo $order['order_number'];
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                            <td><?php 
                                                $created_date = isset($order['created_at']) ? $order['created_at'] : 
                                                              (isset($order['order_date']) ? $order['order_date'] : date('Y-m-d H:i:s'));
                                                echo date('d/m/Y H:i', strtotime($created_date)); 
                                            ?></td>
                                            <td><?php 
                                                $total = isset($order['order_total']) ? $order['order_total'] : 
                                                       (isset($order['total_amount']) ? $order['total_amount'] : 0);
                                                echo number_format($total, 0, ',', '.'); 
                                            ?>đ</td>
                                            <td>
                                                <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                                                    <?php
                                                    $status_text = '';
                                                    switch ($order['status']) {
                                                        case 'pending':
                                                            $status_text = 'Chờ xác nhận';
                                                            break;
                                                        case 'confirmed':
                                                            $status_text = 'Đã xác nhận';
                                                            break;
                                                        case 'processing':
                                                            $status_text = 'Đang xử lý';
                                                            break;
                                                        case 'shipping':
                                                            $status_text = 'Đang giao hàng';
                                                            break;
                                                        case 'delivered':
                                                            $status_text = 'Đã giao hàng';
                                                            break;
                                                        case 'cancelled':
                                                            $status_text = 'Đã hủy';
                                                            break;
                                                        default:
                                                            $status_text = ucfirst($order['status']);
                                                    }
                                                    echo $status_text;
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="my-orders.php?order_id=<?php echo $order['id']; ?>" class="btn-primary">
                                                    <i class="fas fa-eye"></i> Xem chi tiết
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html> 