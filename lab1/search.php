<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm - Cà Phê Đậm Đà</title>
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
        .product-card img { width: 100%; border-radius: 5px; height: 200px; object-fit: cover; cursor: pointer; }
        .product-card h3 { margin: 15px 0; color: #3c2f2f; cursor: pointer; }
        .product-card h3:hover { color: #d4a373; }
        .product-card p { color: #555; margin-bottom: 15px; }
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
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s, transform 0.3s;
        }
        .dropdown:hover .dropdown-content {
            display: block;
            opacity: 1;
            transform: translateY(0);
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
        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-input {
            padding: 10px;
            width: 300px;
            border: 1px solid #d4a373;
            border-radius: 5px;
        }
        .not-found {
            text-align: center;
            padding: 50px 0;
            color: #555;
        }
        
        @media (max-width: 768px) { 
            nav { flex-direction: column; padding: 10px; }
            .nav-links { flex-direction: column; margin-top: 15px; }
            nav a { margin: 8px 0; }
            .product-grid { grid-template-columns: 1fr; }
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
            </div>
        </nav>
    </header>

    <section class="products">
        <h1>Kết quả tìm kiếm</h1>
        
        <div class="search-container">
            <form action="search.php" method="get">
                <input type="text" id="searchInput" name="q" placeholder="Tìm kiếm sản phẩm..." 
                       class="search-input" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                <button type="submit" class="btn">Tìm kiếm</button>
            </form>
        </div>
        
        <div class="product-grid" id="productGrid">
            <?php
            // Danh sách tất cả sản phẩm từ tất cả các trang
            $all_products = [
                // Arabica
                [
                    'id' => 1,
                    'name' => 'Cà phê Arabica',
                    'price' => 150000,
                    'image' => 'https://lh6.googleusercontent.com/proxy/ULqvKQ2UCFsMhYAqAbJE1VXiCR4I6IDe6dtj5t5h7qBXzhy4bqhlzOC3FlzOXHrOcvWBb_oiCQRi0U4ZXBOK3vA',
                    'weight' => '500g',
                    'category' => 'arabica'
                ],
                [
                    'id' => 14,
                    'name' => 'Cà phê Arabica Đặc Biệt',
                    'price' => 200000,
                    'image' => 'https://images.unsplash.com/photo-1512568400610-62da28bc8a4b?auto=format&fit=crop&w=600',
                    'weight' => '500g',
                    'category' => 'arabica'
                ],
                [
                    'id' => 15,
                    'name' => 'Cà phê Arabica Colombia',
                    'price' => 185000,
                    'image' => 'https://caphedanong.net/wp-content/uploads/2022/06/ca-phe-hat-arabica-colombia.jpg',
                    'weight' => '500g',
                    'category' => 'arabica'
                ],
                [
                    'id' => 16,
                    'name' => 'Cà phê Arabica Brazil',
                    'price' => 175000,
                    'image' => 'https://vietblend.vn/wp-content/uploads/2021/02/brazil-arabica-1.jpg',
                    'weight' => '500g',
                    'category' => 'arabica'
                ],
                [
                    'id' => 17,
                    'name' => 'Cà phê Arabica Ethiopia',
                    'price' => 195000,
                    'image' => 'https://afamilycdn.com/150157425591193600/2020/11/17/2-16055777808681073218611.jpg',
                    'weight' => '500g',
                    'category' => 'arabica'
                ],
                
                // Robusta
                [
                    'id' => 2,
                    'name' => 'Cà phê Robusta',
                    'price' => 120000,
                    'image' => 'https://bizweb.dktcdn.net/thumb/1024x1024/100/512/697/products/r-bot-1719824345076.jpg?v=1719829974003',
                    'weight' => '500g',
                    'category' => 'robusta'
                ],
                [
                    'id' => 18,
                    'name' => 'Cà phê Robusta Đặc Biệt',
                    'price' => 160000,
                    'image' => 'https://coffee24h.vn/wp-content/uploads/2018/04/ca-phe-nguyen-chat-Robusta-Cau-Dat.jpg',
                    'weight' => '500g',
                    'category' => 'robusta'
                ],
                [
                    'id' => 19,
                    'name' => 'Cà phê Robusta Buôn Ma Thuột',
                    'price' => 140000,
                    'image' => 'https://salt.tikicdn.com/ts/product/39/d5/1c/aadb33bc7d9d44fc03d46f7a9113a333.jpg',
                    'weight' => '500g',
                    'category' => 'robusta'
                ],
                [
                    'id' => 20,
                    'name' => 'Cà phê Robusta Premium',
                    'price' => 150000,
                    'image' => 'https://kingcoffee.com.vn/wp-content/uploads/2022/12/robusta-premium-2.png',
                    'weight' => '500g',
                    'category' => 'robusta'
                ],
                
                // Chồn
                [
                    'id' => 3,
                    'name' => 'Cà phê Chồn',
                    'price' => 180000,
                    'image' => 'https://vn-live-01.slatic.net/p/cdf5f80d6feaa2e85e10968606ea4df6.jpg',
                    'weight' => '500g',
                    'category' => 'chon'
                ],
                [
                    'id' => 21,
                    'name' => 'Cà phê Chồn Đặc Biệt',
                    'price' => 250000,
                    'image' => 'https://product.hstatic.net/1000397797/product/ca-phe-chon-rang-xay-nguyen-chat-loai-dac-biet-1_c7acce8cd7ec4f509eacf86835e01c81_master.jpg',
                    'weight' => '250g',
                    'category' => 'chon'
                ],
                [
                    'id' => 22,
                    'name' => 'Cà phê Chồn Thượng Hạng',
                    'price' => 300000,
                    'image' => 'https://cafechtx.vn/wp-content/uploads/2019/06/Ca-phe-chon-1.jpg',
                    'weight' => '250g',
                    'category' => 'chon'
                ],
                [
                    'id' => 23,
                    'name' => 'Cà phê Chồn Robusta',
                    'price' => 220000,
                    'image' => 'https://trungnguyenecoffee.com/wp-content/uploads/2021/09/chu-chon2-min.jpg',
                    'weight' => '500g',
                    'category' => 'chon'
                ],
                [
                    'id' => 24,
                    'name' => 'Cà phê Chồn Arabica',
                    'price' => 280000,
                    'image' => 'https://vinacafe.com.vn/wp-content/uploads/2018/06/ca-phe-chon-arabica-dac-san.jpg',
                    'weight' => '250g',
                    'category' => 'chon'
                ],
                
                // Các loại khác
                [
                    'id' => 4,
                    'name' => 'Cà phê Mocha',
                    'price' => 145000,
                    'image' => 'https://product.hstatic.net/1000075078/product/mocha_nong_77f8777d72694d7099b7edefd5fa8e9a_master.jpg',
                    'weight' => '500g',
                    'category' => 'other'
                ],
                [
                    'id' => 5,
                    'name' => 'Cà phê Culi',
                    'price' => 135000,
                    'image' => 'https://coloihoang.com/wp-content/uploads/2019/04/san-pham-ca-phe-culi.png',
                    'weight' => '500g',
                    'category' => 'other'
                ],
                [
                    'id' => 6,
                    'name' => 'Cà phê Espresso',
                    'price' => 160000,
                    'image' => 'https://product.hstatic.net/1000075078/product/espresso_b62af56c27e14e41bbbd161181defd23_master.jpg',
                    'weight' => '500g',
                    'category' => 'other'
                ],
                [
                    'id' => 7,
                    'name' => 'Cà phê Latte',
                    'price' => 155000,
                    'image' => 'https://product.hstatic.net/1000075078/product/latte_851541_master.jpg',
                    'weight' => '500g',
                    'category' => 'other'
                ],
                [
                    'id' => 8,
                    'name' => 'Cà phê Bourbon',
                    'price' => 170000,
                    'image' => 'https://bizweb.dktcdn.net/100/346/613/products/ca-phe-bourbon.jpg?v=1554953413793',
                    'weight' => '500g',
                    'category' => 'other'
                ],
                [
                    'id' => 9,
                    'name' => 'Cà phê Cappuccino',
                    'price' => 165000,
                    'image' => 'https://product.hstatic.net/1000075078/product/cappuccino_621532_master.jpg',
                    'weight' => '500g',
                    'category' => 'other'
                ],
                [
                    'id' => 10,
                    'name' => 'Cà phê Americano',
                    'price' => 155000,
                    'image' => 'https://product.hstatic.net/1000075078/product/americano_968067_master.jpg',
                    'weight' => '500g',
                    'category' => 'other'
                ],
                [
                    'id' => 11,
                    'name' => 'Cà phê Macchiato',
                    'price' => 160000,
                    'image' => 'https://product.hstatic.net/1000075078/product/caramel-macchiato_143623_master.jpg',
                    'weight' => '500g',
                    'category' => 'other'
                ],
                [
                    'id' => 12,
                    'name' => 'Cà phê Vanilla Latte',
                    'price' => 170000,
                    'image' => 'https://product.hstatic.net/1000075078/product/vanilla-latte_618293_master.jpg',
                    'weight' => '500g',
                    'category' => 'other'
                ],
                [
                    'id' => 13,
                    'name' => 'Cà phê Caramel',
                    'price' => 175000,
                    'image' => 'https://product.hstatic.net/1000075078/product/caramel-phin-freeze_791036_master.jpg',
                    'weight' => '500g',
                    'category' => 'other'
                ]
            ];

            // Lấy từ khóa tìm kiếm từ URL
            $search_term = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
            
            // Nếu không có từ khóa, hiển thị tất cả sản phẩm
            if (empty($search_term)) {
                $filtered_products = $all_products;
            } else {
                // Lọc sản phẩm theo từ khóa
                $filtered_products = array_filter($all_products, function($product) use ($search_term) {
                    return stripos(strtolower($product['name']), $search_term) !== false;
                });
            }
            
            // Hiển thị kết quả tìm kiếm
            if (count($filtered_products) > 0) {
                foreach ($filtered_products as $product) {
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
            } else {
                echo "<div class='not-found'>Không tìm thấy sản phẩm nào phù hợp với từ khóa '$search_term'</div>";
            }
            ?>
        </div>
    </section>

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