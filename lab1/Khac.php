<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Các loại cà phê khác - Cà Phê Đậm Đà</title>
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
            transition: background-color 0.3s;
        }
        .dropdown-content a:hover {
            background-color: #d4a373;
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
        <h1>Các loại cà phê khác</h1>
        
      
        
        <div style="text-align: center; margin-bottom: 20px;">
            <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm..." style="padding: 10px; width: 300px; border: 1px solid #d4a373; border-radius: 5px;">
        </div>
        
        <div class="product-grid" id="productGrid">
            <?php
            $products = [
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