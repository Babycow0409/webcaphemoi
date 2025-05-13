<?php
session_start();
// Kiểm tra có thông tin đơn hàng không
if(!isset($_SESSION['order_id']) || !isset($_SESSION['order_total'])) {
    header("Location: index.php");
    exit;
}

$order_id = $_SESSION['order_id'];
$order_total = $_SESSION['order_total'];

// Lấy custom_order_id nếu có
$display_order_id = isset($_SESSION['custom_order_id']) ? $_SESSION['custom_order_id'] : $order_id;

// Xóa thông tin đơn hàng khỏi session sau khi hiển thị
unset($_SESSION['order_id']);
unset($_SESSION['order_total']);
unset($_SESSION['custom_order_id']);

// Đảm bảo giỏ hàng đã được xóa hoàn toàn
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Xóa giỏ hàng trong localStorage -->
    <script>
        // Xóa giỏ hàng trong localStorage khi trang tải
        window.onload = function() {
            // Xóa giỏ hàng trong localStorage
            localStorage.removeItem('cart');
            console.log('Giỏ hàng đã được xóa sau khi đặt hàng thành công');
            
            // Cập nhật hiển thị số lượng giỏ hàng
            const cartCountElements = document.querySelectorAll('.cart-count');
            if (cartCountElements.length > 0) {
                cartCountElements.forEach(function(element) {
                    element.style.display = 'none';
                });
            }
        };
    </script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        body { padding-top: 80px; line-height: 1.6; background-color: #f8f9fa; }
        
        /* Header styles */
        header { 
            background-color: #3c2f2f; 
            color: white; 
            padding: 0; 
            position: fixed; 
            width: 100%; 
            top: 0; 
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        nav { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 1200px; 
            margin: 0 auto;
            padding: 0 20px;
            height: 80px;
        }
        
        .logo { 
            font-family: 'Playfair Display', serif; 
            font-size: 1.8em; 
            font-weight: 700;
            color: #d4a373;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        .nav-links { 
            display: flex; 
            align-items: center; 
        }
        
        nav a { 
            color: white; 
            text-decoration: none; 
            margin: 0 15px; 
            font-weight: 500;
            font-size: 0.95em;
            letter-spacing: 0.5px;
            position: relative;
            padding: 8px 0;
            transition: color 0.3s;
        }
        
        nav a:hover { 
            color: #d4a373; 
        }
        
        /* Underline animation for nav links */
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
        
        /* Dropdown styles */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #ffffff;
            min-width: 180px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 10px 0;
            z-index: 1;
            transition: all 0.3s ease;
            transform: translateY(10px);
            opacity: 0;
            visibility: hidden;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        .dropdown-content a {
            color: #5d4037;
            padding: 10px 20px;
            display: block;
            font-weight: 400;
            margin: 0;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        
        .dropdown-content a:hover {
            background-color: #f5f5f5;
            color: #d4a373;
            border-left: 3px solid #d4a373;
        }
        
        .dropdown-content a::after {
            display: none;
        }
        
        .cart-icon {
            position: relative;
            margin-left: 15px;
        }
        
        .cart-icon i {
            font-size: 1.2em;
            vertical-align: middle;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #d4a373;
            color: white;
            font-size: 0.7em;
            font-weight: bold;
            height: 18px;
            width: 18px;
            text-align: center;
            line-height: 18px;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        h1, h2 { font-family: 'Playfair Display', serif; color: #3c2f2f; text-align: center; margin: 40px 0 20px; }
        
        .success-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #d4a373, #c68b59);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
            font-size: 50px;
            box-shadow: 0 10px 20px rgba(212, 163, 115, 0.3);
            transition: transform 0.5s;
        }
        
        .success-icon:hover {
            transform: scale(1.05) rotate(5deg);
        }
        
        .order-details {
            margin: 30px auto;
            max-width: 400px;
            background-color: #f8f6f2;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.03);
        }
        
        .order-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e5e0d8;
        }
        
        .order-detail-row:last-child {
            border-bottom: none;
            font-weight: bold;
            color: #5d4037;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 30px;
            background: #d4a373;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            margin-top: 25px;
            transition: all 0.3s;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(212, 163, 115, 0.3);
        }
        
        .btn:hover {
            background: #b6894c;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(212, 163, 115, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(212, 163, 115, 0.3);
        }
        
        .steps-container {
            display: flex;
            justify-content: space-between;
            max-width: 600px;
            margin: 50px auto 30px;
            position: relative;
        }
        
        .steps-container::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: #e5e0d8;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #f8f6f2;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4a373;
            font-size: 20px;
            border: 3px solid #d4a373;
            margin-bottom: 15px;
        }
        
        .step-text {
            font-size: 14px;
            color: #5d4037;
            font-weight: 500;
        }
        
        .step.active .step-icon {
            background: #d4a373;
            color: white;
        }
        
        .step.active .step-text {
            color: #d4a373;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">Cà Phê Đậm Đà</a>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                <div class="dropdown">
                    <a href="products.php"><i class="fas fa-coffee"></i> Sản phẩm</a>
                    <div class="dropdown-content">
                        <a href="products.php">Tất cả sản phẩm</a>
                        <a href="arabica.php">Arabica</a>
                        <a href="robusta.php">Robusta</a>
                        <a href="chon.php">Cà phê Chồn</a>
                        <a href="Khac.php">Sản phẩm khác</a>
                    </div>
                </div>
                <a href="#about"><i class="fas fa-info-circle"></i> Giới thiệu</a>
                <a href="#contact"><i class="fas fa-envelope"></i> Liên hệ</a>
                <a href="cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                </a>
                <?php
                if(isset($_SESSION['user_id'])) {
                    // Hiển thị tên người dùng nếu đã đăng nhập
                    echo '<span style="color: #d4a373; margin-right: 15px;">Xin chào, ' . (isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Khách hàng') . '</span>';
                    
                    echo '<div class="dropdown">
                        <a href="#"><i class="fas fa-user-circle"></i> Tài khoản</a>
                        <div class="dropdown-content">
                            <a href="profile.php"><i class="fas fa-user"></i> Thông tin cá nhân</a>
                            <a href="my-orders.php"><i class="fas fa-shopping-bag"></i> Đơn hàng</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                        </div>
                    </div>';
                } else {
                    echo '<a href="login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>';
                    echo '<a href="register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>';
                }
                ?>
            </div>
        </nav>
    </header>

    <div class="success-container">
        <div class="success-icon"><i class="fas fa-check"></i></div>
        <h1>Đặt hàng thành công!</h1>
        <p>Cảm ơn bạn đã đặt hàng tại Cà Phê Đậm Đà. Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.</p>
        
        <div class="steps-container">
            <div class="step active">
                <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="step-text">Đặt hàng</div>
            </div>
            <div class="step">
                <div class="step-icon"><i class="fas fa-clipboard-check"></i></div>
                <div class="step-text">Xác nhận</div>
            </div>
            <div class="step">
                <div class="step-icon"><i class="fas fa-box-open"></i></div>
                <div class="step-text">Đóng gói</div>
            </div>
            <div class="step">
                <div class="step-icon"><i class="fas fa-shipping-fast"></i></div>
                <div class="step-text">Giao hàng</div>
            </div>
        </div>
        
        <div class="order-details">
            <h2>Thông tin đơn hàng</h2>
            <div class="order-detail-row">
                <span><i class="fas fa-hashtag"></i> Mã đơn hàng:</span>
                <span><?php echo htmlspecialchars($display_order_id); ?></span>
            </div>
            <div class="order-detail-row">
                <span><i class="fas fa-money-bill-wave"></i> Tổng tiền:</span>
                <span><?php echo number_format($order_total, 0, ',', '.'); ?> VNĐ</span>
            </div>
        </div>
        
        <a href="my-orders.php" class="btn" style="margin-right: 10px;"><i class="fas fa-clipboard-list"></i> Xem đơn hàng</a>
        <a href="products.php" class="btn"><i class="fas fa-store"></i> Tiếp tục mua sắm</a>
    </div>

    <footer style="background-color: #3c2f2f; color: white; padding: 40px 0; margin-top: 50px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <p>© 2023 Cà Phê Đậm Đà. Tất cả các quyền được bảo lưu.</p>
        </div>
    </footer>

    <script>
        // Sửa lỗi hiển thị icon trong Safari
        document.addEventListener('DOMContentLoaded', function() {
            // Hiệu ứng hiển thị dropdown mượt mà hơn
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('mouseenter', function() {
                    const content = this.querySelector('.dropdown-content');
                    content.style.display = 'block';
                    setTimeout(function() {
                        content.style.opacity = '1';
                        content.style.transform = 'translateY(0)';
                        content.style.visibility = 'visible';
                    }, 10);
                });
                
                dropdown.addEventListener('mouseleave', function() {
                    const content = this.querySelector('.dropdown-content');
                    content.style.opacity = '0';
                    content.style.transform = 'translateY(10px)';
                    content.style.visibility = 'hidden';
                    setTimeout(function() {
                        if (content.style.visibility === 'hidden') {
                            content.style.display = 'none';
                        }
                    }, 300);
                });
            });
        });
    </script>
</body>
</html> 