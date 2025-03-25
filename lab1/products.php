<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Cà Phê Đậm Đà</title>
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
            display: block; 
            text-align: center;
            margin: 10px auto;
        }
        .btn:hover { background-color: #8b4513; transform: scale(1.05); }
        .products { max-width: 1200px; margin: 50px auto; padding: 20px; }
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 30px; 
            padding: 20px;
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
        .product-card img { 
            width: 100%; 
            height: 200px; 
            object-fit: contain;
            border-radius: 5px; 
            cursor: pointer;
            background-color: #f9f9f9;
            padding: 10px;
            transition: transform 0.3s;
        }
        .product-card img:hover {
            transform: scale(1.05);
        }
        .product-card h3 { margin: 15px 0; color: #3c2f2f; cursor: pointer; }
        .product-card h3:hover { color: #d4a373; }
        .product-card p { color: #555; margin-bottom: 15px; }
        
        @media (max-width: 768px) { 
            nav { flex-direction: column; padding: 10px; }
            .nav-links { flex-direction: column; margin-top: 15px; }
            nav a { margin: 8px 0; }
            .product-grid { grid-template-columns: 1fr; }
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
                    $has_pending_orders = false;
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

    <section class="products">
        <h1>Sản phẩm của chúng tôi</h1>
        
        <div class="info-section">
            <h2>Giới thiệu về các dòng cà phê</h2>
            <p>
                Cà Phê Đậm Đà tự hào mang đến cho khách hàng đa dạng các loại cà phê chất lượng cao, được chọn lọc từ những vùng trồng nổi tiếng trên thế giới và Việt Nam. Chúng tôi cung cấp ba dòng cà phê chính: Arabica, Robusta và Cà phê Chồn.
            </p>
            <p>
                Mỗi loại cà phê đều có đặc tính và hương vị riêng biệt. Arabica mang đến vị chua thanh tao, hương thơm phong phú; Robusta với vị đắng mạnh và đậm đà; Cà phê Chồn là loại đặc sản quý hiếm với quy trình chế biến độc đáo, mang đến hương vị hài hòa giữa vị đắng, chua và ngọt.
            </p>
            <p>
                Tất cả sản phẩm cà phê của chúng tôi đều được rang xay theo công thức riêng biệt, đảm bảo giữ trọn vẹn hương vị đặc trưng của từng loại hạt cà phê. Dù bạn là người mới bắt đầu hay đã là chuyên gia cà phê, chúng tôi đều có những sản phẩm phù hợp với khẩu vị của bạn.
            </p>
        </div>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <form action="search.php" method="get">
                <input type="text" id="searchInput" name="q" placeholder="Tìm kiếm sản phẩm..." style="padding: 10px; width: 300px; border: 1px solid #d4a373; border-radius: 5px;">
                <button type="submit" class="btn">Tìm kiếm</button>
            </form>
        </div>
        <div class="product-grid" id="productGrid">
            <?php
            $products = [
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
                ],
                [
                    'id' => 5,
                    'name' => 'Cà phê Culi',
                    'price' => 135000,
                    'image' => 'https://coloihoang.com/wp-content/uploads/2019/04/san-pham-ca-phe-culi.png',
                    'weight' => '500g'
                ],
                [
                    'id' => 6,
                    'name' => 'Cà phê Espresso',
                    'price' => 160000,
                    'image' => 'https://product.hstatic.net/1000075078/product/espresso_b62af56c27e14e41bbbd161181defd23_master.jpg',
                    'weight' => '500g'
                ],
                [
                    'id' => 7,
                    'name' => 'Cà phê Latte',
                    'price' => 155000,
                    'image' => 'https://product.hstatic.net/1000075078/product/latte_851541_master.jpg',
                    'weight' => '500g'
                ],
                [
                    'id' => 8,
                    'name' => 'Cà phê Bourbon',
                    'price' => 170000,
                    'image' => 'https://bizweb.dktcdn.net/100/346/613/products/ca-phe-bourbon.jpg?v=1554953413793',
                    'weight' => '500g'
                ],
                [
                    'id' => 9,
                    'name' => 'Cà phê Cappuccino',
                    'price' => 165000,
                    'image' => 'https://product.hstatic.net/1000075078/product/cappuccino_621532_master.jpg',
                    'weight' => '500g'
                ],
                [
                    'id' => 10,
                    'name' => 'Cà phê Americano',
                    'price' => 155000,
                    'image' => 'https://product.hstatic.net/1000075078/product/americano_968067_master.jpg',
                    'weight' => '500g'
                ],
                [
                    'id' => 11,
                    'name' => 'Cà phê Macchiato',
                    'price' => 160000,
                    'image' => 'https://product.hstatic.net/1000075078/product/caramel-macchiato_143623_master.jpg',
                    'weight' => '500g'
                ],
                [
                    'id' => 12,
                    'name' => 'Cà phê Vanilla Latte',
                    'price' => 170000,
                    'image' => 'https://product.hstatic.net/1000075078/product/vanilla-latte_618293_master.jpg',
                    'weight' => '500g'
                ],
                [
                    'id' => 13,
                    'name' => 'Cà phê Caramel',
                    'price' => 175000,
                    'image' => 'https://product.hstatic.net/1000075078/product/caramel-phin-freeze_791036_master.jpg',
                    'weight' => '500g'
                ]
            ];

            foreach ($products as $product) {
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
        const products = <?php echo json_encode($products); ?>;
        const productGrid = document.getElementById('productGrid');
        const searchInput = document.getElementById('searchInput');

        function displayProducts(filteredProducts) {
            productGrid.innerHTML = filteredProducts.map(product => `
                <div class='product-card'>
                    <a href='product-detail.php?id=${product.id}'>
                        <img src='${product.image}' alt='${product.name}'>
                    </a>
                    <a href='product-detail.php?id=${product.id}'><h3>${product.name}</h3></a>
                    <p>${new Intl.NumberFormat('vi-VN').format(product.price)} VNĐ / ${product.weight}</p>
                    <a href='#' class='btn' onclick="addToCart('${product.name} - ${product.weight}', ${product.price})">Thêm vào giỏ</a>
                </div>
            `).join('');
        }

      
        // Hiển thị tất cả sản phẩm khi trang được tải
        displayProducts(products);

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