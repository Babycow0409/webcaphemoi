<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra xem giỏ hàng có trống không
$cart_empty = true;
$total = 0;
$cart_items = [];

// JavaScript sẽ đọc từ localStorage nên chỉ cần xử lý khi submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xử lý đặt hàng
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $district = $_POST['district'];
    $city = $_POST['city'];
    $payment_method = $_POST['payment_method'];
    $total_amount = $_POST['total_amount'];
    $user_id = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
    
    // Tạo mã đơn hàng
    $order_number = 'ORD-' . time() . '-' . rand(1000, 9999);
    
    // Thêm đơn hàng vào database
    $combined_address = $address . ', ' . $district . ', ' . $city;

    // Thêm đơn hàng vào database với cấu trúc bảng orders
    if ($user_id === null) {
        $sql = "INSERT INTO orders (order_number, total_amount, status, shipping_name, 
                shipping_email, shipping_phone, shipping_address, shipping_city, payment_method, created_at) 
                VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssssss", 
            $order_number, 
            $total_amount, 
            $full_name, 
            $email, 
            $phone, 
            $combined_address, 
            $city, 
            $payment_method
        );
    } else {
        $sql = "INSERT INTO orders (order_number, user_id, total_amount, status, shipping_name, 
                shipping_email, shipping_phone, shipping_address, shipping_city, payment_method, created_at) 
                VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sidsssss", 
            $order_number, 
            $user_id, 
            $total_amount, 
            $full_name, 
            $email, 
            $phone, 
            $combined_address, 
            $city, 
            $payment_method
        );
    }
    
    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        
        // Thêm chi tiết đơn hàng
        $cart_items = json_decode($_POST['cart_items'], true);
        foreach ($cart_items as $item) {
            $product_name = $item['name'];
            $price = $item['price'];
            $quantity = $item['quantity'];
            
            $sql_item = "INSERT INTO order_items (order_id, product_name, price, quantity) 
                        VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);
            $stmt_item->bind_param("isdi", $order_id, $product_name, $price, $quantity);
            $stmt_item->execute();
        }
        
        // Chuyển hướng đến trang xác nhận đơn hàng
        header("Location: order-success.php?order_id=" . $order_id);
        exit();
    } else {
        $error = "Có lỗi xảy ra khi đặt hàng: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Cà Phê Đậm Đà</title>
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
        
        /* Style cho checkout */
        .checkout-container {
            max-width: 1200px;
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
        
        .step.active {
            font-weight: bold;
            color: #d4a373;
        }
        
        .step.active::after {
            content: '';
            position: absolute;
            bottom: -17px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background-color: #d4a373;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .checkout-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .checkout-summary {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            color: #3c2f2f;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #d4a373;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #3c2f2f;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: #d4a373;
            outline: none;
            box-shadow: 0 0 0 3px rgba(212, 163, 115, 0.2);
        }
        
        .payment-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .payment-option {
            border: 2px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-option:hover {
            border-color: #d4a373;
        }
        
        .payment-option.selected {
            border-color: #d4a373;
            background-color: rgba(212, 163, 115, 0.1);
        }
        
        .payment-option img {
            height: 50px;
            margin-bottom: 10px;
            display: block;
            margin: 0 auto 10px;
        }
        
        .cart-items {
            margin-top: 20px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .cart-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        
        .cart-item-details {
            flex-grow: 1;
        }
        
        .cart-item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .cart-item-total {
            font-weight: bold;
            color: #3c2f2f;
            text-align: right;
        }
        
        .order-totals {
            margin-top: 30px;
            border-top: 2px dashed #e9ecef;
            padding-top: 20px;
        }
        
        .order-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-total-row.grand-total {
            font-weight: bold;
            font-size: 1.2em;
            color: #3c2f2f;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }
        
        .place-order-btn {
            background-color: #28a745;
            padding: 15px;
            font-size: 1.1em;
            border-radius: 50px;
            width: 100%;
            margin-top: 20px;
        }
        
        .place-order-btn:hover {
            background-color: #218838;
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

    <div class="checkout-container">
        <h1>Thanh toán đơn hàng</h1>
        
        <div class="checkout-steps">
            <div class="step">1. Giỏ hàng</div>
            <div class="step active">2. Thanh toán</div>
            <div class="step">3. Hoàn tất</div>
        </div>
        
        <div id="checkout-content">
            <!-- Nội dung thanh toán sẽ được thêm bằng JavaScript -->
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
        document.addEventListener('DOMContentLoaded', function() {
            initCheckout();
        });

        function initCheckout() {
            const checkoutContent = document.getElementById('checkout-content');
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            if (cart.length === 0) {
                checkoutContent.innerHTML = `
                    <div class="empty-cart-message">
                        <i>🛒</i>
                        <h3>Giỏ hàng của bạn đang trống</h3>
                        <p>Vui lòng thêm sản phẩm vào giỏ hàng trước khi thanh toán.</p>
                        <a href="products.php" class="btn">Mua sắm ngay</a>
                    </div>
                `;
                return;
            }
            
            // Tính tổng tiền
            let total = 0;
            cart.forEach(item => {
                total += item.price * item.quantity;
            });
            
            // Lấy thông tin người dùng nếu đã đăng nhập
            const userInfo = <?php echo isset($_SESSION['user']) ? json_encode($_SESSION['user']) : 'null'; ?>;
            
            checkoutContent.innerHTML = `
                <form id="checkout-form" method="post" action="checkout.php">
                    <div class="checkout-grid">
                        <div class="checkout-form">
                            <h3 class="section-title">Thông tin nhận hàng</h3>
                            
                            <div class="form-group">
                                <label for="full_name">Họ tên người nhận <span style="color: red">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required
                                    value="${userInfo ? userInfo.name || '' : ''}">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email <span style="color: red">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="${userInfo ? userInfo.email || '' : ''}">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Số điện thoại <span style="color: red">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required
                                    value="${userInfo ? userInfo.phone || '' : ''}">
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Địa chỉ <span style="color: red">*</span></label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="district">Quận/Huyện <span style="color: red">*</span></label>
                                <input type="text" class="form-control" id="district" name="district" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="city">Tỉnh/Thành phố <span style="color: red">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            
                            <h3 class="section-title">Phương thức thanh toán</h3>
                            
                            <input type="hidden" id="payment_method" name="payment_method" value="cod">
                            
                            <div class="payment-options">
                                <div class="payment-option selected" onclick="selectPayment('cod', this)">
                                    <img src="images/cod.png" alt="Thanh toán khi nhận hàng">
                                    <div>Thanh toán khi nhận hàng</div>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment('banking', this)">
                                    <img src="images/banking.png" alt="Chuyển khoản ngân hàng">
                                    <div>Chuyển khoản ngân hàng</div>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment('momo', this)">
                                    <img src="images/momo.png" alt="Ví MoMo">
                                    <div>Ví MoMo</div>
                                </div>
                                
                                <div class="payment-option" onclick="selectPayment('vnpay', this)">
                                    <img src="images/vnpay.png" alt="VN Pay">
                                    <div>VN Pay</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checkout-summary">
                            <h3 class="section-title">Thông tin đơn hàng</h3>
                            
                            <div class="cart-items">
                                ${cart.map(item => `
                                    <div class="cart-item">
                                        <img src="${item.image}" alt="${item.name}">
                                        <div class="cart-item-details">
                                            <div class="cart-item-name">${item.name}</div>
                                            <div class="cart-item-price">${new Intl.NumberFormat('vi-VN').format(item.price)} VNĐ x ${item.quantity}</div>
                                        </div>
                                        <div class="cart-item-total">${new Intl.NumberFormat('vi-VN').format(item.price * item.quantity)} VNĐ</div>
                                    </div>
                                `).join('')}
                            </div>
                            
                            <div class="order-totals">
                                <div class="order-total-row">
                                    <span>Tạm tính:</span>
                                    <span>${new Intl.NumberFormat('vi-VN').format(total)} VNĐ</span>
                                </div>
                                
                                <div class="order-total-row">
                                    <span>Phí vận chuyển:</span>
                                    <span>Miễn phí</span>
                                </div>
                                
                                <div class="order-total-row grand-total">
                                    <span>Tổng thanh toán:</span>
                                    <span>${new Intl.NumberFormat('vi-VN').format(total)} VNĐ</span>
                                </div>
                            </div>
                            
                            <input type="hidden" name="total_amount" value="${total}">
                            <input type="hidden" name="cart_items" value='${JSON.stringify(cart)}'>
                            
                            <button type="submit" class="btn place-order-btn">Đặt hàng</button>
                        </div>
                    </div>
                </form>
            `;
        }
        
        function selectPayment(method, element) {
            document.getElementById('payment_method').value = method;
            
            // Remove 'selected' class from all payment options
            const options = document.querySelectorAll('.payment-option');
            options.forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add 'selected' class to the clicked option
            element.classList.add('selected');
        }
    </script>
</body>
</html> 