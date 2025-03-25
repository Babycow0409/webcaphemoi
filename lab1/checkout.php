<?php
session_start();
require_once 'config/database.php';

$error_message = '';
$success_message = '';

if(isset($_POST['placeOrder'])) {
    try {
        // Lấy dữ liệu từ form
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $payment_method = $_POST['payment_method'];
        
        // Lấy dữ liệu giỏ hàng từ form (hiện tại được gửi qua JavaScript)
        $cartItems = isset($_POST['cartItems']) ? json_decode($_POST['cartItems'], true) : [];
        
        // Kiểm tra nếu giỏ hàng trống
        if(empty($cartItems)) {
            $error_message = "Giỏ hàng của bạn đang trống!";
        } else {
            // Tính tổng tiền
            $total = 0;
            foreach($cartItems as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            
            $conn->beginTransaction();
            
            // Tạo đơn hàng mới
            $stmt = $conn->prepare("INSERT INTO orders (user_id, order_code, total_amount, status, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, payment_method) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)");
            
            $order_code = time() . rand(1000, 9999);
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            $stmt->execute([
                $user_id,
                $order_code,
                $total,
                $name,
                $email,
                $phone,
                $address,
                $city,
                $payment_method
            ]);
            
            $order_id = $conn->lastInsertId();
            
            // Thêm các sản phẩm trong đơn hàng
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)");
            
            foreach($cartItems as $item) {
                $stmt->execute([
                    $order_id,
                    $item['name'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
            
            $conn->commit();
            $success_message = "Đặt hàng thành công! Mã đơn hàng của bạn là: $order_code";
        }
    } catch(PDOException $e) {
        if(isset($conn)) $conn->rollBack();
        $error_message = "Có lỗi xảy ra: " . $e->getMessage();
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
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { padding-top: 100px; line-height: 1.6; }
        header { background-color: #3c2f2f; color: white; padding: 1rem; position: fixed; width: 100%; top: 0; z-index: 1000; }
        nav { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.8em; padding: 10px; }
        .nav-links { display: flex; flex-wrap: wrap; align-items: center; padding: 10px; }
        nav a { color: white; text-decoration: none; margin: 10px 15px; font-weight: bold; }
        nav a:hover { color: #d4a373; }
        
        .checkout-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        
        h1, h2 { font-family: 'Playfair Display', serif; color: #3c2f2f; margin-bottom: 20px; }
        
        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
        }
        
        .order-summary {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #d4a373;
            font-size: 1.2em;
            font-weight: bold;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #d4a373;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #8b4513;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 30px;
            color: #666;
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
        <h1>Thanh toán</h1>
        
        <?php if($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if($success_message): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
                <p>Cảm ơn bạn đã mua hàng tại Cà Phê Đậm Đà!</p>
                <a href="products.php" class="btn" style="margin-top: 15px;">Tiếp tục mua sắm</a>
            </div>
            <script>
                // Xóa giỏ hàng sau khi đặt hàng thành công
                localStorage.removeItem('cart');
            </script>
        <?php else: ?>
        
        <div id="emptyCartMessage" class="empty-cart" style="display: none;">
            <h3>Giỏ hàng của bạn đang trống</h3>
            <p>Vui lòng thêm sản phẩm vào giỏ hàng trước khi thanh toán</p>
            <a href="products.php" class="btn">Xem sản phẩm</a>
        </div>
        
        <div id="checkoutContent" class="checkout-content">
            <div class="order-summary">
                <h2>Thông tin đơn hàng</h2>
                <div id="cart-items"></div>
                <div id="cart-total"></div>
            </div>

            <form method="POST" action="" id="checkout-form">
                <h2>Thông tin giao hàng</h2>
                
                <input type="hidden" name="cartItems" id="cartItemsInput">
                
                <div class="form-group">
                    <label for="name">Họ và tên:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Địa chỉ:</label>
                    <input type="text" id="address" name="address" required>
                </div>
                
                <div class="form-group">
                    <label for="city">Thành phố:</label>
                    <input type="text" id="city" name="city" required>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Phương thức thanh toán:</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                        <option value="bank">Chuyển khoản ngân hàng</option>
                        <option value="momo">Ví MoMo</option>
                        <option value="vnpay">VNPay</option>
                    </select>
                </div>
                
                <button type="submit" name="placeOrder" class="btn" id="placeOrderBtn">Đặt hàng</button>
            </form>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Lấy giỏ hàng từ localStorage
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const cartItemsDiv = document.getElementById('cart-items');
                const cartTotalDiv = document.getElementById('cart-total');
                const cartItemsInput = document.getElementById('cartItemsInput');
                const checkoutContent = document.getElementById('checkoutContent');
                const emptyCartMessage = document.getElementById('emptyCartMessage');
                
                // Kiểm tra giỏ hàng trống
                if (cart.length === 0) {
                    checkoutContent.style.display = 'none';
                    emptyCartMessage.style.display = 'block';
                    return;
                }
                
                // Hiển thị sản phẩm trong giỏ hàng
                let html = '';
                let total = 0;
                
                cart.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    html += `
                        <div class="cart-item">
                            <span>${item.name} x ${item.quantity}</span>
                            <span>${new Intl.NumberFormat('vi-VN').format(itemTotal)} VNĐ</span>
                        </div>
                    `;
                });
                
                cartItemsDiv.innerHTML = html;
                cartTotalDiv.innerHTML = `
                    <div class="cart-total">
                        <span>Tổng cộng:</span>
                        <span>${new Intl.NumberFormat('vi-VN').format(total)} VNĐ</span>
                    </div>
                `;
                
                // Lưu thông tin giỏ hàng vào input hidden
                cartItemsInput.value = JSON.stringify(cart);
                
                // Validate form trước khi submit
                document.getElementById('checkout-form').addEventListener('submit', function(e) {
                    const name = document.getElementById('name').value;
                    const email = document.getElementById('email').value;
                    const phone = document.getElementById('phone').value;
                    const address = document.getElementById('address').value;
                    const city = document.getElementById('city').value;
                    
                    if (!name || !email || !phone || !address || !city) {
                        e.preventDefault();
                        alert('Vui lòng điền đầy đủ thông tin giao hàng');
                        return false;
                    }
                    
                    // Kiểm tra định dạng email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        e.preventDefault();
                        alert('Email không hợp lệ');
                        return false;
                    }
                    
                    // Kiểm tra định dạng số điện thoại
                    const phoneRegex = /(84|0[3|5|7|8|9])+([0-9]{8})\b/;
                    if (!phoneRegex.test(phone)) {
                        e.preventDefault();
                        alert('Số điện thoại không hợp lệ');
                        return false;
                    }
                    
                    return true;
                });
            });
        </script>
        <?php endif; ?>
    </div>
</body>
</html> 