<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Robusta - Cà Phê Đậm Đà</title>
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
        
        @media (max-width: 768px) { 
            nav { flex-direction: column; padding: 10px; }
            .nav-links { flex-direction: column; margin-top: 15px; }
            nav a { margin: 8px 0; }
            .product-grid { grid-template-columns: 1fr; }
        }
        .info-section {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f8f3eb;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .info-section h2 {
            color: #3c2f2f;
            margin-bottom: 20px;
        }
        .info-section p {
            margin-bottom: 15px;
            text-align: justify;
            padding: 0 15px;
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
            </div>
        </nav>
    </header>

    <section class="products">
        <h1>Cà phê Robusta</h1>
        
        <div class="info-section">
            <h2>Giới thiệu về cà phê Robusta</h2>
            <p>
                Cà phê Robusta là loại cà phê phổ biến thứ hai trên thế giới sau Arabica, chiếm khoảng 40% sản lượng cà phê toàn cầu. Hạt Robusta có hình tròn nhỏ và màu nâu nhạt hơn so với Arabica, với đường cong thẳng hơn.
            </p>
            <p>
                Đúng như tên gọi, cà phê Robusta nổi bật với sức chống chịu mạnh mẽ trước các điều kiện khí hậu khắc nghiệt và sâu bệnh. Đặc trưng của Robusta là vị đắng mạnh, đậm đà và dư vị kéo dài. Hàm lượng caffeine trong Robusta cao hơn gấp đôi so với Arabica, mang đến cảm giác tỉnh táo mạnh mẽ. Việt Nam là quốc gia sản xuất cà phê Robusta lớn nhất thế giới, với những vùng trồng nổi tiếng như Tây Nguyên.
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
                    'id' => 2,
                    'name' => 'Cà phê Robusta',
                    'price' => 120000,
                    'image' => 'https://bizweb.dktcdn.net/thumb/1024x1024/100/512/697/products/r-bot-1719824345076.jpg?v=1719829974003',
                    'weight' => '500g'
                ],
                [
                    'id' => 18,
                    'name' => 'Cà phê Robusta Đặc Biệt',
                    'price' => 160000,
                    'image' => 'https://salt.tikicdn.com/cache/750x750/ts/product/c6/a2/32/9c841efc66a4b07b2914418102b49186.jpg.webp',
                    'weight' => '500g'
                ],
                [
                    'id' => 19,
                    'name' => 'Cà phê Robusta Buôn Ma Thuột',
                    'price' => 140000,
                    'image' => 'https://salt.tikicdn.com/cache/750x750/ts/product/f0/9a/60/2f49a4f93b1262747001968b686eb4fb.jpg.webp',
                    'weight' => '500g'
                ],
                [
                    'id' => 20,
                    'name' => 'Cà phê Robusta Premium',
                    'price' => 150000,
                    'image' => 'https://devafood.vn/wp-content/uploads/2022/10/Ca-phe-Robusta-PREMIUM-Robusta-1Kg-edited_2.png.webp',
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

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filteredProducts = products.filter(product => 
                product.name.toLowerCase().includes(searchTerm)
            );
            displayProducts(filteredProducts);
        });

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