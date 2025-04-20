<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Kiểm tra ID đơn hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user']['id'];
$order = null;
$order_items = [];

// Lấy thông tin đơn hàng
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: orders.php");
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <style>
        /* Sử dụng cùng style với trang chủ */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { padding-top: 100px; line-height: 1.6; }
        header { background-color: #3c2f2f; color: white; padding: 1rem; position: fixed; width: 100%; top: 0; z-index: 1000; }
        nav { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.8em; padding: 10px; }
        .nav-links { display: flex; flex-wrap: wrap; align-items: center; padding: 10px; }
        nav a { color: white; text-decoration: none; margin: 10px 15px; font-weight: bold; }
        nav a:hover { color: #d4a373; }
        h1, h2 { font-family: 'Playfair Display', serif; color: #3c2f2f; text-align: center; margin: 40px 0 20px; }
        .btn { 
            padding: 10px 20px; 
            background-color: #d4a373; 
            color: white; 
            text-decoration: none; 
            border: none; 
            border-radius: 50px; 
            cursor: pointer; 
            transition: all 0.3s; 
            display: block; 
            text-align: center;
            margin: 10px auto;
        }
        .btn:hover { background-color: #8b4513; transform: scale(1.05); }
        
        /* Style cho trang detail */
        .order-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .order-id {
            font-size: 18px;
            color: #6c757d;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }
        
        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ff9800;
        }
        
        .status-processing {
            background-color: rgba(13, 110, 253, 0.2);
            color: #0d6efd;
        }
        
        .status-shipped {
            background-color: rgba(23, 162, 184, 0.2);
            color: #17a2b8;
        }
        
        .status-delivered, .status-completed {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        
        .status-cancelled {
            background-color: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        
        .order-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .order-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .order-box {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .order-box h3 {
            font-family: 'Playfair Display', serif;
            color: #3c2f2f;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #d4a373;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            width: 40%;
            font-weight: bold;
            color: #3c2f2f;
        }
        
        .detail-value {
            width: 60%;
        }
        
        .order-items {
            margin-top: 30px;
        }
        
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .item-table th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #e9ecef;
            color: #3c2f2f;
        }
        
        .item-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .item-table tr:last-child td {
            border-bottom: none;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .item-name {
            font-weight: bold;
            color: #3c2f2f;
        }
        
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-row:last-child {
            font-weight: bold;
            font-size: 1.2em;
            color: #3c2f2f;
            border-top: 2px solid #d4a373;
            border-bottom: none;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .product-info {
            display: flex;
            align-items: center;
        }
        
        .product-text {
            margin-left: 15px;
        }
        
        /* Dropdown menu style */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #3c2f2f;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #d4a373;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        /* Footer styles */
        footer {
            background-color: #3c2f2f;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Cà Phê Đậm Đà</div>
            <div class="nav-links">
                <a href="index.php">Trang chủ</a>
                <div class="dropdown">
                    <a href="products.php">Sản phẩm</a>
                    <div class="dropdown-content">
                        <a href="products.php">Tất cả</a>
                        <a href="arabica.php">Arabica</a>
                        <a href="robusta.php">Robusta</a>
                        <a href="chon.php">Chồn</a>
                        <a href="Khac.php">Khác</a>
                    </div>
                </div>
                <a href="#about">Giới thiệu</a>
                <a href="#contact">Liên hệ</a>
                <a href="cart.php">Giỏ hàng</a>
                <?php
                if(isset($_SESSION['user'])) {
                    echo '<div class="dropdown">
                        <a href="#">Tài khoản</a>
                        <div class="dropdown-content">
                            <a href="profile.php">Thông tin cá nhân</a>
                            <a href="orders.php">Đơn hàng</a>
                            <a href="logout.php">Đăng xuất</a>
                        </div>
                    </div>';
                } else {
                    echo '<a href="login.php">Đăng nhập</a>';
                    echo '<a href="register.php">Đăng ký</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <div class="order-container">
        <div class="order-header">
            <h1>Chi tiết đơn hàng</h1>
            <div class="order-id">Mã đơn hàng: <?php echo $order['order_number']; ?></div>
        </div>
        
        <div class="order-grid">
            <div class="order-box">
                <h3>Thông tin đơn hàng</h3>
                
                <div class="detail-row">
                    <div class="detail-label">Ngày đặt hàng:</div>
                    <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'] ?? date('Y-m-d H:i:s'))); ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Trạng thái:</div>
                    <div class="detail-value">
                        <?php
                        $status_class = '';
                        $status_text = '';
                        
                        switch($order['status']) {
                            case 'pending':
                                $status_class = 'status-pending';
                                $status_text = 'Chờ xác nhận';
                                break;
                            case 'processing':
                                $status_class = 'status-processing';
                                $status_text = 'Đang xử lý';
                                break;
                            case 'shipped':
                                $status_class = 'status-shipped';
                                $status_text = 'Đang giao hàng';
                                break;
                            case 'delivered':
                            case 'completed':
                                $status_class = 'status-completed';
                                $status_text = 'Đã giao hàng';
                                break;
                            case 'cancelled':
                                $status_class = 'status-cancelled';
                                $status_text = 'Đã hủy';
                                break;
                            default:
                                $status_class = 'status-pending';
                                $status_text = $order['status'];
                        }
                        ?>
                        <span class="order-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Phương thức thanh toán:</div>
                    <div class="detail-value">
                        <?php 
                        $payment_methods = [
                            'cod' => 'Thanh toán khi nhận hàng',
                            'banking' => 'Chuyển khoản ngân hàng',
                            'momo' => 'Ví MoMo',
                            'vnpay' => 'VN Pay'
                        ];
                        echo isset($payment_methods[$order['payment_method']]) ? $payment_methods[$order['payment_method']] : $order['payment_method'];
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="order-box">
                <h3>Thông tin giao hàng</h3>
                
                <div class="detail-row">
                    <div class="detail-label">Họ tên:</div>
                    <div class="detail-value"><?php echo $order['full_name']; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value"><?php echo $order['email']; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Số điện thoại:</div>
                    <div class="detail-value"><?php echo $order['phone']; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Địa chỉ:</div>
                    <div class="detail-value">
                        <?php echo $order['address']; ?>, 
                        <?php echo $order['district']; ?>, 
                        <?php echo $order['city']; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="order-items">
            <h3>Sản phẩm đã đặt</h3>
            
            <?php if (empty($order_items)): ?>
                <p>Không có thông tin về sản phẩm.</p>
            <?php else: ?>
                <table class="item-table">
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
                                        <img src="<?php echo $item['image'] ?? 'uploads/products/default.jpg'; ?>" class="item-image" alt="<?php echo $item['product_name']; ?>">
                                        <div class="product-text">
                                            <div class="item-name"><?php echo $item['product_name'] ?? 'Sản phẩm #'.$item['product_id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div class="order-summary">
                <div class="summary-row">
                    <div>Tạm tính:</div>
                    <div><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</div>
                </div>
                
                <div class="summary-row">
                    <div>Phí vận chuyển:</div>
                    <div>Miễn phí</div>
                </div>
                
                <div class="summary-row">
                    <div>Tổng cộng:</div>
                    <div><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</div>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <a href="orders.php" class="btn">Quay lại danh sách đơn hàng</a>
            
            <?php if ($order['status'] == 'pending'): ?>
                <a href="cancel-order.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">Hủy đơn hàng</a>
            <?php endif; ?>
        </div>
    </div>

    <footer id="contact">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <h2 style="color: white;">Liên hệ</h2>
            <p style="margin: 20px 0;">
                Địa chỉ: 123 Đường Nguyễn Huệ, Quận 1, TP.HCM<br>
                Email: info@caphedamda.com<br>
                Điện thoại: 0909 123 456
            </p>
            <div style="margin: 20px 0;">
                <a href="#" style="color: #d4a373; margin: 0 10px;">Facebook</a>
                <a href="#" style="color: #d4a373; margin: 0 10px;">Instagram</a>
                <a href="#" style="color: #d4a373; margin: 0 10px;">Twitter</a>
            </div>
            <p style="margin-top: 20px; font-size: 0.9em;">
                © 2023 Cà Phê Đậm Đà. Tất cả các quyền được bảo lưu.
            </p>
        </div>
    </footer>
</body>
</html> 