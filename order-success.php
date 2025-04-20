<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra xem có ID đơn hàng không
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = intval($_GET['order_id']);
$order = null;

// Lấy thông tin đơn hàng
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
} else {
    header("Location: index.php");
    exit();
}

// Xóa giỏ hàng sau khi đặt hàng thành công
echo "<script>localStorage.removeItem('cart');</script>";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - Cà Phê Đậm Đà</title>
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
            font-weight: bold;
        }
        .btn:hover { background-color: #8b4513; transform: scale(1.05); }
        
        /* Style cho trang success */
        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            position: relative;
        }
        
        .step.completed {
            color: #28a745;
            font-weight: bold;
        }
        
        .step.completed::after {
            content: '';
            position: absolute;
            bottom: -17px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background-color: #28a745;
        }
        
        .success-message {
            text-align: center;
            padding: 30px;
            background-color: rgba(40, 167, 69, 0.1);
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background-color: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 40px;
        }
        
        .success-title {
            font-family: 'Playfair Display', serif;
            color: #28a745;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .order-details {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .order-details h3 {
            font-family: 'Playfair Display', serif;
            color: #3c2f2f;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #d4a373;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
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
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
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

    <div class="success-container">
        <h1>Đặt hàng thành công</h1>
        
        <div class="checkout-steps">
            <div class="step completed">1. Giỏ hàng</div>
            <div class="step completed">2. Thanh toán</div>
            <div class="step completed">3. Hoàn tất</div>
        </div>
        
        <div class="success-message">
            <div class="success-icon">✓</div>
            <h2 class="success-title">Cảm ơn bạn đã đặt hàng!</h2>
            <p>Đơn hàng của bạn đã được xác nhận. Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.</p>
        </div>
        
        <div class="order-details">
            <h3>Thông tin đơn hàng</h3>
            
            <div class="detail-row">
                <div class="detail-label">Mã đơn hàng:</div>
                <div class="detail-value"><?php echo $order['order_number']; ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Họ tên người nhận:</div>
                <div class="detail-value"><?php echo htmlspecialchars($order['shipping_name']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Địa chỉ giao hàng:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($order['shipping_address']); ?>
                    <?php if(isset($order['shipping_city']) && !empty($order['shipping_city'])): ?>
                    , <?php echo htmlspecialchars($order['shipping_city']); ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Số điện thoại:</div>
                <div class="detail-value"><?php echo htmlspecialchars($order['shipping_phone']); ?></div>
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
            
            <div class="detail-row">
                <div class="detail-label">Tổng tiền:</div>
                <div class="detail-value"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</div>
            </div>
        </div>
        
        <?php if ($order['payment_method'] == 'banking'): ?>
        <div class="order-details">
            <h3>Thông tin chuyển khoản</h3>
            
            <div class="detail-row">
                <div class="detail-label">Ngân hàng:</div>
                <div class="detail-value">Vietcombank</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Số tài khoản:</div>
                <div class="detail-value">1234567890</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Chủ tài khoản:</div>
                <div class="detail-value">CÔNG TY TNHH CÀ PHÊ ĐẬM ĐÀ</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Nội dung chuyển khoản:</div>
                <div class="detail-value"><?php echo $order['order_number']; ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="index.php" class="btn">Tiếp tục mua sắm</a>
            <?php if(isset($_SESSION['user'])): ?>
            <a href="orders.php" class="btn btn-secondary">Xem đơn hàng của bạn</a>
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

    <script>
        // Xóa giỏ hàng sau khi đặt hàng thành công
        localStorage.removeItem('cart');
    </script>
</body>
</html> 