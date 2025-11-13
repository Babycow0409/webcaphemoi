<?php
session_start();
include 'includes/db_connect.php';
require_once 'includes/cart_functions.php';
require_once 'includes/db_checks.php';

// Đảm bảo các cột cần thiết đã tồn tại trong database
checkOrderSystemDb($conn);

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

// Kiểm tra và lọc bỏ các sản phẩm đã bị xóa
$removedProducts = validateCartProducts($cart, $conn);

// Nếu có sản phẩm bị xóa
if(!empty($removedProducts)) {
    $_SESSION['cart'] = $cart;
    $_SESSION['message'] = "Một số sản phẩm đã bị xóa khỏi giỏ hàng vì không còn tồn tại: " . implode(", ", $removedProducts);
    
    // Nếu giỏ hàng trống sau khi lọc, chuyển hướng về trang giỏ hàng
    if(empty($cart)) {
        header("Location: cart.php");
        exit;
    }
    
    // Cập nhật localStorage
    echo "<script>localStorage.setItem('cart', JSON.stringify(" . json_encode($cart) . "));</script>";
}

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
    
    // Lấy địa chỉ mặc định từ sổ địa chỉ
    $stmt = $conn->prepare("SELECT address_detail, ward, district, province FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $default_address = $result->fetch_assoc();
        
        // Tạo địa chỉ đầy đủ từ các thành phần
        $full_address = $default_address['address_detail'];
        if (!empty($default_address['ward'])) $full_address .= ', ' . $default_address['ward'];
        if (!empty($default_address['district'])) $full_address .= ', ' . $default_address['district'];
        if (!empty($default_address['province'])) $full_address .= ', ' . $default_address['province'];
        
        $user['address'] = $full_address;
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
            padding: 22px 35px; 
            background-color: #d4a373; 
            color: white; 
            border: none; 
            border-radius: 16px; 
            cursor: pointer; 
            font-size: 24px; 
            font-weight: 700; 
            transition: all 0.3s; 
            display: block; 
            width: 100%; 
            text-align: center;
            margin-top: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .btn:hover {
            background-color: #c49666;
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.25);
        }
        
        /* Style cho checkout */
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: space-between;
        }
        
        .checkout-form {
            flex: 2;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            min-width: 300px;
            border-top: 5px solid #d4a373;
        }
        
        .order-summary {
            flex: 1;
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            min-width: 300px;
            max-width: 400px;
            align-self: flex-start;
            position: sticky;
            top: 120px;
            border-top: 5px solid #3c2f2f;
        }
        
        .order-summary h2 {
            font-size: 24px;
            margin-bottom: 25px;
            color: #3c2f2f;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 15px;
            text-align: left;
        }
        
        .form-group {
            margin-bottom: 30px;
        }
        
        .form-group label {
            display: block;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #3c2f2f;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            min-width: 0;
            padding: 20px 25px;
            border: 3px solid #d4a373;
            border-radius: 16px;
            font-size: 22px;
            background-color: #fff;
            color: #333;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-top: 8px;
            transition: border 0.3s, box-shadow 0.3s;
            overflow: auto;
            font-weight: 500;
        }
        
        .form-control:focus {
            border-color: #3c2f2f;
            box-shadow: 0 0 0 6px rgba(212, 163, 115, 0.2);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 120px;
            max-height: 300px;
            resize: vertical;
            line-height: 1.8;
            font-size: 22px;
        }
        
        #address.form-control {
            min-height: 80px;
            max-height: 200px;
        }
        
        .checkout-form h2 {
            font-size: 42px;
            margin-bottom: 40px;
            color: #3c2f2f;
            border-bottom: 4px solid #d4a373;
            padding-bottom: 20px;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        /* Nâng cấp select menu */
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%233c2f2f' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 24px;
            padding-right: 60px;
            font-weight: 600;
            cursor: pointer;
        }
        
        /* Thêm dấu * màu đỏ cho trường bắt buộc */
        .form-group label[for]:after {
            content: "*";
            color: #e74c3c;
            margin-left: 10px;
            font-size: 28px;
            position: relative;
            top: 3px;
        }
        
        /* Ngoại trừ trường ghi chú không bắt buộc */
        .form-group label[for="note"]:after {
            content: "";
        }
        
        .cart-item img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        
        .item-details {
            flex-grow: 1;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 8px;
            color: #3c2f2f;
            font-size: 16px;
        }
        
        .item-price, .item-total {
            color: #666;
            font-size: 14px;
            margin-top: 4px;
            line-height: 1.5;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 18px 0;
            font-size: 16px;
            color: #555;
        }
        
        .summary-row.total {
            font-size: 22px;
            font-weight: bold;
            color: #3c2f2f;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 2px solid #f1f1f1;
        }
        
        .summary-row span:first-child {
            font-weight: 500;
        }
        
        /* Nút quay lại giỏ hàng */
        .back-to-cart {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #5d4037;
            text-decoration: none;
            font-weight: 500;
            padding: 10px;
            transition: all 0.3s;
        }
        
        .back-to-cart:hover {
            color: #d4a373;
            text-decoration: underline;
        }
        
        .checkout-btn {
            background-color: #28a745;
            width: 100%;
            padding: 15px;
            font-size: 18px;
            border-radius: 8px;
            margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            color: white;
            transition: all 0.3s;
        }
        
        .checkout-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: #d4a373;
            background-color: #fdf8f3;
        }
        
        .payment-method.selected {
            border-color: #d4a373;
            background-color: #fdf8f3;
            box-shadow: 0 0 0 2px rgba(212, 163, 115, 0.2);
        }
        
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        
        .payment-method-info {
            flex-grow: 1;
        }
        
        .payment-method-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .payment-method-description {
            font-size: 14px;
            color: #666;
        }
        
        /* Responsive cho mobile */
        @media (max-width: 768px) {
            .checkout-container {
                flex-direction: column;
            }
            
            .order-summary {
                position: static;
                max-width: 100%;
                margin-top: 20px;
            }
            
            .checkout-form h2 {
                font-size: 32px;
                margin: 20px 0 15px;
            }

            .form-group label {
                font-size: 20px;
            }

            .form-control, textarea.form-control {
                font-size: 18px;
                padding: 16px 15px;
            }

            .btn {
                font-size: 20px;
                padding: 18px 30px;
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
        
        /* Thông tin sản phẩm trong checkout */
        .cart-item {
            display: flex;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .cart-item:last-child {
            margin-bottom: 25px;
        }
        
        .nav-user-icon {
            padding: 5px 10px;
            font-size: 22px;
            color: #fff;
            border-radius: 50%;
            background: #d4a373;
            transition: background 0.3s;
        }
        .nav-user-icon:hover {
            background: #c49666;
            color: #fff;
        }
        .dropdown-content {
            min-width: 170px;
        }
        .dropdown-content a {
            font-size: 16px;
            padding: 12px 18px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }
        
        /* Error message styling */
        .error-container {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 5px;
            color: #856404;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .error-container h4 {
            margin-top: 0;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }
        
        .error-container h4 i {
            margin-right: 10px;
            color: #ffc107;
        }
        
        .error-container p {
            margin: 0;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <header>
        <nav>
         <div class="logo">
    <a href="index.php" style="display: flex; align-items: center; text-decoration: none;">
        <img src="https://res.cloudinary.com/dczuwkvok/image/upload/v1747069387/Brown_Beige_Modern_Coffee_Logo_xeceb8.png" alt="LOGO" width="70" height="70" style="border-radius: 50%; margin-right: 10px;">
        <span style="font-family: 'Playfair Display', serif; font-size: 1em; color: white; font-weight: bold;">Cà Phê Đậm Đà</span>
    </a>
            </div>
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
                            <a href="my-orders.php"><i class="fas fa-shopping-bag"></i> Đơn hàng</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                        </div>
                    </div>';
                } else {
                    echo '<div class="dropdown">
                        <a href="#" class="nav-user-icon"><i class="fas fa-user nav-icon"></i></a>
                        <div class="dropdown-content">
                            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                            <a href="register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                        </div>
                    </div>';
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
            <div class="error-container">
                <h4><i class="fas fa-exclamation-triangle"></i> Đã xảy ra lỗi</h4>
                <p><?php echo $error; ?></p>
            </div>
            <?php endif; ?>
            
            <form action="place-order.php" method="post">
                <div class="form-group">
                    <label for="fullname">Họ tên</label>
                    <input type="text" id="fullname" name="fullname" class="form-control" readonly value="<?php echo htmlspecialchars($user['fullname']); ?>">
                    <p class="field-note" style="margin-top: 8px; color: #666; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> Thông tin này được lấy từ hồ sơ cá nhân của bạn. Để thay đổi, vui lòng cập nhật trong <a href="profile.php" style="color: #d4a373; text-decoration: underline;">thông tin cá nhân</a>.
                    </p>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" class="form-control" readonly value="<?php echo htmlspecialchars($user['phone']); ?>">
                    <p class="field-note" style="margin-top: 8px; color: #666; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> Thông tin này được lấy từ hồ sơ cá nhân của bạn. Để thay đổi, vui lòng cập nhật trong <a href="profile.php" style="color: #d4a373; text-decoration: underline;">thông tin cá nhân</a>.
                    </p>
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <textarea id="address" name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="payment">Phương thức thanh toán</label>
                    <select id="payment" name="payment" class="form-control" required>
                        <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                        <option value="bank">Chuyển khoản ngân hàng</option>
                        <option value="momo">Ví điện tử MoMo</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="note">Ghi chú</label>
                    <textarea id="note" name="note" class="form-control" rows="3"></textarea>
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
            
            <a href="cart.php" class="back-to-cart">« Quay lại giỏ hàng</a>
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