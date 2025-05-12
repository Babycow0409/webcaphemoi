<?php // File header đã được tạo mới

// Đảm bảo đã tải config.php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cà Phê Đậm Đà</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/font-fix.css">
    <style>
    /* Reset CSS */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        padding-top: 100px;
        line-height: 1.6;
        color: #333;
        background-color: #f9f9f9;
    }

    header {
        background-color: #3c2f2f;
        color: white;
        padding: 1rem;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
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
        font-weight: 700;
        letter-spacing: 1px;
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
        margin: 10px 15px;
        font-weight: 600;
        font-size: 0.95rem;
        letter-spacing: 0.5px;
        transition: all 0.3s;
        position: relative;
        padding: 5px 0;
    }

    nav a:hover {
        color: #d4a373;
    }

    nav a:after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        background: #d4a373;
        bottom: 0;
        left: 0;
        transition: width 0.3s;
    }

    nav a:hover:after {
        width: 100%;
    }

    h1,
    h2 {
        font-family: 'Playfair Display', serif;
        color: #3c2f2f;
        text-align: center;
        margin: 40px 0 20px;
        font-weight: 700;
    }

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
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-size: 0.9rem;
    }

    .btn:hover {
        background-color: #8b4513;
        transform: scale(1.05);
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
        min-width: 180px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
        border-radius: 5px;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.3s, transform 0.3s;
    }

    .dropdown-content a {
        color: white;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        font-size: 0.9rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .dropdown-content a:last-child {
        border-bottom: none;
    }

    .dropdown-content a:hover {
        background-color: #d4a373;
        color: white;
    }

    .dropdown:hover .dropdown-content {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    /* Login status */
    .login-status {
        background-color: #f5f5f5;
        padding: 12px 0;
        text-align: center;
        font-size: 0.9rem;
        border-bottom: 1px solid #eee;
    }

    .login-status .container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: flex-end;
    }

    .login-status a {
        color: #3c2f2f;
        text-decoration: none;
        margin-left: 15px;
        font-weight: 600;
        transition: color 0.3s;
    }

    .login-status a:hover {
        color: #d4a373;
    }

    /* Container styling */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Alerts */
    .alert {
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-weight: 500;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #721c24;
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
                        <a href="products.php?category=1">Arabica</a>
                        <a href="products.php?category=2">Robusta</a>
                        <a href="products.php?category=3">Chồn</a>
                        <a href="products.php?category=4">Khác</a>
                    </div>
                </div>
                <a href="about.php">Giới thiệu</a>
                <a href="cart.php" style="position: relative;">
                    Giỏ hàng
                    <span id="cartCount"
                        style="position: absolute; top: -8px; right: -8px; background-color: #d4a373; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">0</span>
                </a>
                <?php
                if(isset($_SESSION['user_id'])) {
                    echo '<div class="dropdown">
                        <a href="profile.php" class="nav-user-icon"><i class="fas fa-user nav-icon"></i> ' . htmlspecialchars($_SESSION['fullname']) . '</a>
                        <div class="dropdown-content">
                            <a href="profile.php"><i class="fas fa-user-circle"></i> Thông tin tài khoản</a>
                            <a href="address-book.php"><i class="fas fa-address-book"></i> Sổ địa chỉ</a>
                            <a href="my-orders.php"><i class="fas fa-shopping-bag"></i> Lịch sử đơn hàng</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                        </div>
                    </div>';
                } else {
                    echo '<div class="dropdown">
                        <a href="profile.php" class="nav-user-icon"><i class="fas fa-user nav-icon"></i></a>
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

    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="login-status">
        <div class="container">
            <p>
                Xin chào <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong> |
                <a href="profile.php">Tài khoản</a> |
                <a href="my-orders.php">Đơn hàng</a> |
                <a href="logout.php">Đăng xuất</a>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Kiểm tra và cập nhật số lượng giỏ hàng
        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const cartCountElement = document.getElementById('cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = cart.length;
                console.log("Đã cập nhật số lượng giỏ hàng:", cart.length);
            }
        }

        // Kiểm tra session
        fetch('check_cart_session.php')
            .then(response => response.json())
            .then(data => {
                if (data.hasSession) {
                    console.log("Đã có giỏ hàng trong session:", data.count, "sản phẩm");
                } else {
                    console.log("Không có giỏ hàng trong session, kiểm tra localStorage");
                    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                    if (cart.length > 0) {
                        console.log("Có", cart.length, "sản phẩm trong localStorage, đồng bộ vào session");
                        fetch('sync_cart.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'cart_data=' + encodeURIComponent(JSON.stringify(cart))
                            })
                            .then(response => response.json())
                            .then(result => {
                                console.log("Kết quả đồng bộ:", result.message);
                                if (result.success) {
                                    // Nếu đang ở trang giỏ hàng, tải lại trang
                                    if (window.location.pathname.includes('cart.php')) {
                                        window.location.reload();
                                    } else {
                                        updateCartCount();
                                    }
                                }
                            })
                            .catch(error => console.error("Lỗi đồng bộ giỏ hàng:", error));
                    }
                }
            })
            .catch(error => console.error("Lỗi kiểm tra session:", error));

        // Cập nhật số lượng giỏ hàng ban đầu
        updateCartCount();
    });
    </script>