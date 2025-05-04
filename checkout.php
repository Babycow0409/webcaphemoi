<?php
session_start();
include 'includes/db_connect.php';
require_once 'includes/cart_functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Chuyển hướng về trang giỏ hàng nếu không có sản phẩm
if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['message'] = "Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.";
    header("Location: cart.php");
    exit;
}

// Lấy dữ liệu giỏ hàng
$cart = $_SESSION['cart'];
$totalAmount = calculateCartTotal($cart);

// Lấy thông tin người dùng nếu đã đăng nhập
$user = [
    'fullname' => '',
    'email' => '',
    'phone' => '',
    'address' => ''
];

if(isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT fullname, email, phone, address FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $user['fullname'] = $user_data['fullname'] ?? '';
        $user['email'] = $user_data['email'] ?? '';
        $user['phone'] = $user_data['phone'] ?? '';
        $user['address'] = $user_data['address'] ?? '';
    }
}

// Hiển thị thông báo lỗi nếu có
if(isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Sử dụng cùng style với trang chủ */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { padding-top: 100px; line-height: 1.6; background-color: #f9f9f9; color: #333; }
        
        /* Header cải tiến */
        header {
            background-color: #3c2f2f;
            color: white;
            padding: 0.8rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        /* Khi cuộn, header sẽ nhỏ hơn */
        header.scrolled {
            padding: 0.5rem;
        }
        
        nav {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8em;
            padding: 10px;
            letter-spacing: 1px;
            color: #f8f4e3;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .nav-links {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 10px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            position: relative;
            transition: all 0.3s;
            padding: 5px 0;
        }
        
        nav a:hover {
            color: #d4a373;
        }
        
        /* Đường gạch chân khi hover */
        nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: #d4a373;
            bottom: 0;
            left: 0;
            transition: width 0.3s;
        }
        
        nav a:hover::after {
            width: 100%;
        }
        
        /* Dropdown cải tiến */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            z-index: 1;
            border-radius: 8px;
            overflow: hidden;
            transform: translateY(10px);
            transition: all 0.3s;
            opacity: 0;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
            transform: translateY(0);
            opacity: 1;
        }
        
        .dropdown-content a {
            color: #3c2f2f;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            margin: 0;
            border-bottom: 1px solid #f1f1f1;
            transition: background-color 0.3s;
        }
        
        .dropdown-content a:hover {
            background-color: #f8f4e3;
        }
        
        .dropdown-content a::after {
            display: none;
        }
        
        /* Icon cho menu */
        .nav-icon {
            margin-right: 8px;
            color: #d4a373;
        }
        
        .user-greeting {
            color: #d4a373;
            margin-right: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .user-greeting i {
            margin-right: 8px;
        }
        
        /* Cart icon với số lượng */
        .cart-icon {
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #d4a373;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
            font-weight: bold;
        }
        
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
        
        /* Style cho checkout */
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .checkout-form {
            flex: 1;
            min-width: 300px;
        }
        
        .order-summary {
            flex: 1;
            min-width: 300px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #5d4037;
        }
        
        .form-group input, 
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 15px;
            background-color: #f8f9fa;
        }
        
        .btn {
            background-color: #5d4037;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #3e2723;
        }
        
        .cart-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .cart-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        
        .item-details {
            flex-grow: 1;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .item-total {
            font-weight: bold;
            color: #3c2f2f;
            text-align: right;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-row.total {
            font-weight: bold;
            font-size: 1.2em;
            border-top: 2px solid #d4a373;
            border-bottom: none;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                flex-direction: column;
            }
        }
        
        /* Footer styles */
        footer {
            background-color: #3c2f2f;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
        }
        
        .empty-cart-message {
            text-align: center;
            padding: 50px 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            margin: 30px 0;
        }
        
        .empty-cart-message i {
            font-size: 50px;
            color: #d4a373;
            margin-bottom: 20px;
        }
        
        /* Style for error message */
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Cà Phê Đậm Đà</div>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home nav-icon"></i>Trang chủ</a>
                <div class="dropdown">
                    <a href="products.php"><i class="fas fa-coffee nav-icon"></i>Sản phẩm</a>
                    <div class="dropdown-content">
                        <a href="products.php">Tất cả sản phẩm</a>
                        <a href="products.php?category=arabica">Arabica</a>
                        <a href="products.php?category=robusta">Robusta</a>
                        <a href="products.php?category=chon">Chồn</a>
                        <a href="products.php?category=other">Khác</a>
                    </div>
                </div>
                <a href="about.php"><i class="fas fa-info-circle nav-icon"></i>Giới thiệu</a>
                <a href="contact.php"><i class="fas fa-envelope nav-icon"></i>Liên hệ</a>
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart nav-icon"></i>Giỏ hàng
                    <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
                <?php
                if(isset($_SESSION['user_id'])) {
                    // Hiển thị tên người dùng nếu đã đăng nhập
                    echo '<span class="user-greeting"><i class="fas fa-user"></i>Xin chào, ' . (isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Khách hàng') . '</span>';
                    
                    echo '<div class="dropdown">
                        <a href="#"><i class="fas fa-user-circle nav-icon"></i>Tài khoản</a>
                        <div class="dropdown-content">
                            <a href="profile.php"><i class="fas fa-id-card"></i> Thông tin cá nhân</a>
                            <a href="orders.php"><i class="fas fa-shopping-bag"></i> Đơn hàng</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                        </div>
                    </div>';
                } else {
                    echo '<a href="login.php"><i class="fas fa-sign-in-alt nav-icon"></i>Đăng nhập</a>';
                    echo '<a href="register.php"><i class="fas fa-user-plus nav-icon"></i>Đăng ký</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <!-- JavaScript cho header -->
    <script>
        // Header thu nhỏ khi cuộn
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>

    <div class="checkout-container">
        <div class="checkout-form">
            <h2>Thông tin thanh toán</h2>
            
            <?php if(isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form action="place-order.php" method="post">
                <div class="form-group">
                    <label for="fullname">Họ tên*</label>
                    <input type="text" id="fullname" name="fullname" required value="<?php echo $user['fullname']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" required value="<?php echo $user['email']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại*</label>
                    <input type="tel" id="phone" name="phone" required value="<?php echo $user['phone']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ*</label>
                    <textarea id="address" name="address" rows="3" required><?php echo $user['address']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="payment">Phương thức thanh toán*</label>
                    <select id="payment" name="payment" required>
                        <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                        <option value="bank">Chuyển khoản ngân hàng</option>
                        <option value="momo">Ví điện tử MoMo</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="note">Ghi chú</label>
                    <textarea id="note" name="note" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn">Đặt hàng</button>
            </form>
        </div>
        
        <div class="order-summary">
            <h2>Đơn hàng của bạn</h2>
            
            <?php foreach($cart as $item): ?>
            <div class="cart-item">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" onerror="this.src='images/default-product.jpg'">
                <div class="item-details">
                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ x <?php echo $item['quantity']; ?></div>
                    <div class="item-total"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> VNĐ</div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="summary-row">
                <span>Tạm tính:</span>
                <span><?php echo number_format($totalAmount, 0, ',', '.'); ?> VNĐ</span>
            </div>
            
            <div class="summary-row">
                <span>Phí vận chuyển:</span>
                <span>Miễn phí</span>
            </div>
            
            <div class="summary-row total">
                <span>Tổng cộng:</span>
                <span><?php echo number_format($totalAmount, 0, ',', '.'); ?> VNĐ</span>
            </div>
            
            <a href="cart.php" style="display: block; text-align: center; margin-top: 20px; color: #5d4037; text-decoration: none;">« Quay lại giỏ hàng</a>
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