<?php // File header đã được tạo mới

// Đảm bảo session đã được bắt đầu
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <style>
        /* Sao chép CSS từ file khác vào đây */
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
        .login-status {
            background-color: #f5f5f5;
            padding: 10px 0;
            text-align: center;
        }
        .login-status .container {
            max-width: 1200px;
            margin: 0 auto;
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
                <a href="advanced-search.php">Tìm kiếm nâng cao</a>
                <a href="cart.php" style="position: relative;">
                    Giỏ hàng
                    <span id="cartCount" style="position: absolute; top: -8px; right: -8px; background-color: #d4a373; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">0</span>
                </a>
                <?php
                if(isset($_SESSION['user_id'])) {
                    echo '<div class="dropdown">
                        <a href="#">Tài khoản</a>
                        <div class="dropdown-content">
                            <a href="my-orders.php">Đơn hàng của tôi</a>
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
