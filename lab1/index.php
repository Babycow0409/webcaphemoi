<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Cà Phê Đậm Đà</title>
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
            display: inline-block;
            margin: 10px;
        }
        .btn:hover { background-color: #8b4513; transform: scale(1.05); }
        .hero { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 50px 20px; 
            text-align: center; 
            background-image: url('https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=1500&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            border-radius: 10px;
            margin-bottom: 50px;
            position: relative;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            padding: 100px 20px;
        }
        .hero h1 {
            font-size: 3em;
            color: white;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 1.2em;
            margin-bottom: 30px;
        }
        .featured {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .featured-products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .product-card { 
            background-color: #fffaf0; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
            transition: transform 0.3s; 
            display: flex; 
            flex-direction: column;
        }
        .product-card:hover { transform: scale(1.05); }
        .product-card img { width: 100%; border-radius: 5px; height: 200px; object-fit: cover; cursor: pointer; }
        .product-card h3 { margin: 15px 0; color: #3c2f2f; cursor: pointer; }
        .product-card h3:hover { color: #d4a373; }
        .product-card p { color: #555; margin-bottom: 15px; }
        footer {
            background-color: #3c2f2f;
            color: white;
            text-align: center;
            padding: 30px 0;
            margin-top: 50px;
        }
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
        .category-section {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .category-card {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            height: 250px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .category-card:hover {
            transform: scale(1.05);
        }
        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .category-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .category-title {
            color: white;
            font-size: 1.5em;
            font-family: 'Playfair Display', serif;
        }
        
        @media (max-width: 768px) { 
            nav { flex-direction: column; padding: 10px; }
            .nav-links { flex-direction: column; margin-top: 15px; }
            nav a { margin: 8px 0; }
            .hero h1 { font-size: 2em; }
            .hero-content { padding: 50px 20px; }
            .featured-products, .category-grid { grid-template-columns: 1fr; }
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
                    // Kiểm tra xem có đơn hàng đang xử lý không
                    $has_pending_orders = false; // Biến này sẽ được set true nếu có đơn hàng đang xử lý
                    if(isset($_SESSION['orders'])) {
                        foreach($_SESSION['orders'] as $order) {
                            if($order['status'] != 'completed') {
                                $has_pending_orders = true;
                                break;
                            }
                        }
                    }

                    echo '<div class="dropdown">
                        <a href="#">Tài khoản</a>
                        <div class="dropdown-content">
                            <a href="profile.php">Thông tin cá nhân</a>
                            <a href="orders.php">Đơn hàng';
                    // Hiển thị badge nếu có đơn hàng đang xử lý
                    if($has_pending_orders) {
                        echo ' <span class="order-badge">!</span>';
                    }
                    echo '</a>
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

    <section class="hero">
        <div class="hero-content">
            <h1>Cà Phê Đậm Đà</h1>
            <p>Hương vị đặc trưng từ những hạt cà phê thượng hạng</p>
            <a href="products.php" class="btn">Khám phá sản phẩm</a>
        </div>
    </section>

    <section class="category-section">
        <h2>Danh mục sản phẩm</h2>
        <div class="category-grid">
            <a href="arabica.php" class="category-card">
                <img src="https://lh6.googleusercontent.com/proxy/ULqvKQ2UCFsMhYAqAbJE1VXiCR4I6IDe6dtj5t5h7qBXzhy4bqhlzOC3FlzOXHrOcvWBb_oiCQRi0U4ZXBOK3vA" alt="Arabica">
                <div class="category-overlay">
                    <div class="category-title">Cà phê Arabica</div>
                </div>
            </a>
            <a href="robusta.php" class="category-card">
                <img src="https://bizweb.dktcdn.net/thumb/1024x1024/100/512/697/products/r-bot-1719824345076.jpg?v=1719829974003" alt="Robusta">
                <div class="category-overlay">
                    <div class="category-title">Cà phê Robusta</div>
                </div>
            </a>
            <a href="chon.php" class="category-card">
                <img src="https://vn-live-01.slatic.net/p/cdf5f80d6feaa2e85e10968606ea4df6.jpg" alt="Chồn">
                <div class="category-overlay">
                    <div class="category-title">Cà phê Chồn</div>
                </div>
            </a>
        </div>
    </section>

    <section class="featured" id="featured">
        <h2>Sản phẩm nổi bật</h2>
        <div class="featured-products">
            <?php
            $featured_products = [
                [
                    'id' => 1,
                    'name' => 'Cà phê Arabica',
                    'price' => 150000,
                    'image' => 'https://lh6.googleusercontent.com/proxy/ULqvKQ2UCFsMhYAqAbJE1VXiCR4I6IDe6dtj5t5h7qBXzhy4bqhlzOC3FlzOXHrOcvWBb_oiCQRi0U4ZXBOK3vA',
                    'weight' => '500g'
                ],
                [
                    'id' => 2,
                    'name' => 'Cà phê Robusta',
                    'price' => 120000,
                    'image' => 'https://bizweb.dktcdn.net/thumb/1024x1024/100/512/697/products/r-bot-1719824345076.jpg?v=1719829974003',
                    'weight' => '500g'
                ],
                [
                    'id' => 3,
                    'name' => 'Cà phê Chồn',
                    'price' => 180000,
                    'image' => 'https://vn-live-01.slatic.net/p/cdf5f80d6feaa2e85e10968606ea4df6.jpg',
                    'weight' => '500g'
                ],
                [
                    'id' => 4,
                    'name' => 'Cà phê Mocha',
                    'price' => 145000,
                    'image' => 'https://product.hstatic.net/1000075078/product/mocha_nong_77f8777d72694d7099b7edefd5fa8e9a_master.jpg',
                    'weight' => '500g'
                ]
            ];

            foreach ($featured_products as $product) {
                echo "
                <div class='product-card'>
                    <a href='product-detail.php?id={$product['id']}'>
                        <img src='{$product['image']}' alt='{$product['name']}'>
                    </a>
                    <a href='product-detail.php?id={$product['id']}'><h3>{$product['name']}</h3></a>
                    <p>" . number_format($product['price'], 0, ',', '.') . " VNĐ / {$product['weight']}</p>
                    <a href='#' class='btn' onclick=\"addToCart('{$product['name']} - {$product['weight']}', {$product['price']})\">Thêm vào giỏ</a>
                </div>";
            }
            ?>
        </div>
        <a href="products.php" class="btn" style="margin-top: 30px;">Xem tất cả sản phẩm</a>
    </section>

    <section class="about" id="about" style="max-width: 1200px; margin: 50px auto; padding: 20px; text-align: center;">
        <h2>Về chúng tôi</h2>
        <p style="margin: 20px 0; text-align: justify; padding: 0 20px;">
            Cà Phê Đậm Đà là thương hiệu cà phê Việt Nam được thành lập từ năm 2010. Chúng tôi tự hào là đơn vị chuyên cung cấp các loại cà phê nguyên chất với chất lượng cao nhất. Từ những hạt cà phê được chọn lọc kỹ càng đến quy trình rang xay đặc biệt, chúng tôi cam kết mang đến cho khách hàng trải nghiệm thưởng thức cà phê tuyệt vời nhất.
        </p>
        <p style="margin: 20px 0; text-align: justify; padding: 0 20px;">
            Chúng tôi trực tiếp hợp tác với các nông trại cà phê tại Tây Nguyên, nơi sản sinh ra những hạt cà phê chất lượng nhất Việt Nam. Quy trình rang xay được kiểm soát nghiêm ngặt để đảm bảo giữ trọn hương vị đặc trưng của từng loại cà phê. Với phương châm "Từ nông trại đến tách cà phê", chúng tôi mang đến cho bạn những trải nghiệm cà phê đích thực và nguyên bản nhất.
        </p>
    </section>

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
        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        function addToCart(name, price) {
            const existingItem = cart.find(item => item.name === name);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({ name, price, quantity: 1 });
            }
            localStorage.setItem("cart", JSON.stringify(cart));
            alert(`${name} đã được thêm vào giỏ hàng!`);
        }
    </script>
</body>
</html>