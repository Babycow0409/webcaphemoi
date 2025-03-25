<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sản phẩm - Cà Phê Đậm Đà</title>
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
        .product-detail { max-width: 1200px; margin: 50px auto; padding: 20px; display: flex; flex-wrap: wrap; gap: 40px; }
        .product-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            border-radius: 10px;
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
        }
        .product-info { flex: 1; min-width: 300px; }
        h1 { font-family: 'Playfair Display', serif; color: #3c2f2f; margin-bottom: 20px; }
        .price { color: #d4a373; font-size: 1.5em; margin: 15px 0; }
        .description { color: #555; margin: 20px 0; line-height: 1.8; }
        .btn { 
            padding: 12px 30px; 
            background-color: #d4a373; 
            color: white; 
            text-decoration: none; 
            border: none; 
            border-radius: 50px; 
            cursor: pointer; 
            transition: all 0.3s; 
            display: inline-block;
        }
        .btn:hover { background-color: #8b4513; transform: scale(1.05); }
        .related-products {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
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
        .not-found {
            text-align: center;
            padding: 100px 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .not-found h2 {
            margin-bottom: 20px;
            color: #3c2f2f;
            font-family: 'Playfair Display', serif;
        }
        .not-found p {
            margin-bottom: 30px;
            color: #555;
            font-size: 1.1em;
        }
        
        @media (max-width: 768px) { 
            nav { flex-direction: column; padding: 10px; }
            .nav-links { flex-direction: column; margin-top: 15px; }
            nav a { margin: 8px 0; }
            .product-detail { flex-direction: column; }
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

    <?php
    // Mảng sản phẩm từ tất cả các trang (products.php, arabica.php, robusta.php, chon.php)
    $all_products = [
        // Products.php - Arabica
        1 => [
            'name' => 'Cà phê Arabica',
            'price' => 150000,
            'image' => 'https://lh6.googleusercontent.com/proxy/ULqvKQ2UCFsMhYAqAbJE1VXiCR4I6IDe6dtj5t5h7qBXzhy4bqhlzOC3FlzOXHrOcvWBb_oiCQRi0U4ZXBOK3vA',
            'weight' => '500g',
            'description' => 'Cà phê Arabica được biết đến là loại cà phê cao cấp nhất thế giới, chiếm khoảng 60% sản lượng cà phê toàn cầu. Có hương vị nhẹ nhàng, thanh tao với hậu vị chua nhẹ đặc trưng, phù hợp cho những ai yêu thích sự tinh tế. Sản phẩm được chế biến từ những hạt cà phê Arabica nguyên chất được chọn lọc kỹ càng từ vùng Tây Nguyên.',
            'category' => 'arabica'
        ],
        // Products.php - Robusta
        2 => [
            'name' => 'Cà phê Robusta',
            'price' => 120000,
            'image' => 'https://bizweb.dktcdn.net/thumb/1024x1024/100/512/697/products/r-bot-1719824345076.jpg?v=1719829974003',
            'weight' => '500g',
            'description' => 'Cà phê Robusta là loại cà phê phổ biến thứ hai trên thế giới sau Arabica. Đặc trưng với vị đắng mạnh, đậm đà và dư vị kéo dài, rất phù hợp để pha phin hoặc espresso. Hàm lượng caffeine trong Robusta cao hơn gấp đôi so với Arabica, mang đến cảm giác tỉnh táo mạnh mẽ. Việt Nam là quốc gia sản xuất cà phê Robusta lớn nhất thế giới.',
            'category' => 'robusta'
        ],
        // Products.php - Chồn
        3 => [
            'name' => 'Cà phê Chồn',
            'price' => 180000,
            'image' => 'https://vn-live-01.slatic.net/p/cdf5f80d6feaa2e85e10968606ea4df6.jpg',
            'weight' => '500g',
            'description' => 'Cà phê chồn là loại cà phê đặc biệt được sản xuất từ hạt cà phê đã qua đường tiêu hóa của loài cầy vòi hương (còn gọi là chồn). Quá trình tiêu hóa đặc biệt này làm thay đổi cấu trúc protein của hạt cà phê, tạo nên hương vị độc đáo với độ chua nhẹ, vị đắng dịu và hương thơm nồng nàn.',
            'category' => 'chon'
        ],
        // Products.php - Thêm
        4 => [
            'name' => 'Cà phê Mocha',
            'price' => 145000,
            'image' => 'https://product.hstatic.net/1000075078/product/mocha_nong_77f8777d72694d7099b7edefd5fa8e9a_master.jpg',
            'weight' => '500g',
            'description' => 'Cà phê Mocha là sự kết hợp hoàn hảo giữa cà phê espresso đậm đà và socola ngọt ngào. Sản phẩm mang đến hương vị đặc trưng với vị đắng của cà phê, vị ngọt của socola và vị béo của sữa, tạo nên một trải nghiệm thưởng thức tuyệt vời.',
            'category' => 'other'
        ],
        5 => [
            'name' => 'Cà phê Culi',
            'price' => 135000,
            'image' => 'https://coloihoang.com/wp-content/uploads/2019/04/san-pham-ca-phe-culi.png',
            'weight' => '500g',
            'description' => 'Cà phê Culi được chế biến từ những hạt cà phê đặc biệt có hình dạng tròn và to hơn thông thường. Đây là loại cà phê có hương vị đậm đà, đắng mạnh và thơm nồng hơn so với các loại cà phê khác, rất được ưa chuộng tại Việt Nam.',
            'category' => 'other'
        ],
        6 => [
            'name' => 'Cà phê Espresso',
            'price' => 160000,
            'image' => 'https://product.hstatic.net/1000075078/product/espresso_b62af56c27e14e41bbbd161181defd23_master.jpg',
            'weight' => '500g',
            'description' => 'Cà phê Espresso được chế biến từ hạt cà phê rang đậm và xay nhuyễn, tạo nên một loại cà phê cô đặc, đậm đà với lớp crema vàng óng trên bề mặt. Đây là nền tảng cho nhiều loại đồ uống cà phê phổ biến trên thế giới.',
            'category' => 'other'
        ],
        
        // Arabica.php - Thêm
        14 => [
            'name' => 'Cà phê Arabica Đặc Biệt',
            'price' => 200000,
            'image' => 'https://centurycoffee.vn/uploads/images/2024/12/545x545-1733215387-single_product1-gol.jpg',
            'weight' => '500g',
            'description' => 'Cà phê Arabica Đặc Biệt là dòng sản phẩm cao cấp được chọn lọc từ những hạt cà phê Arabica tốt nhất, trồng ở độ cao trên 1600m. Cà phê mang hương vị đặc trưng với vị chua thanh xen lẫn hương hoa và trái cây, tạo nên một trải nghiệm thưởng thức tinh tế.',
            'category' => 'arabica'
        ],
        15 => [
            'name' => 'Cà phê Arabica Colombia',
            'price' => 400000,
            'image' => 'https://covi.vn/wp-content/uploads/2021/06/ca-phe-arabica-colombia-3.jpg',
            'weight' => '500g',
            'description' => 'Cà phê Arabica Colombia được trồng trọt tại vùng cao nguyên Colombia, mang đặc trưng của vùng đất nổi tiếng này với hương vị cân bằng, vị chua nhẹ và hương thơm trái cây đặc trưng. Đây là loại cà phê được yêu thích trên toàn thế giới.',
            'category' => 'arabica'
        ],
        16 => [
            'name' => 'Cà phê Arabica Brazil',
            'price' => 700000,
            'image' => 'https://khoinghiepcafe.com/wp-content/uploads/khoi-nghiep-cafe-hat-arabica-bourbon-brazil-chau-my-cao-cap-nguyen-chat-sach-100-pha-may-espresso-chuan-y-qua-tang-viet-nam.jpg.webp',
            'weight' => '500g',
            'description' => 'Cà phê Arabica Brazil mang đến hương vị đầy đặn với vị chua nhẹ và vị ngọt caramel. Hạt cà phê được thu hoạch và chế biến tại Brazil - quốc gia sản xuất cà phê hàng đầu thế giới, đảm bảo chất lượng vượt trội.',
            'category' => 'arabica'
        ],
        17 => [
            'name' => 'Cà phê Arabica Ethiopia',
            'price' => 540000,
            'image' => 'https://thecoffeeholic.vn/storage/photos/2/Tr%C3%A0%20CF/4.jpg',
            'weight' => '250g',
            'description' => 'Cà phê Arabica Ethiopia - đến từ quê hương của cà phê, mang đến hương vị phức tạp với nốt hương hoa, trái cây và vị chua sáng. Đây là loại cà phê có lịch sử lâu đời nhất thế giới và được đánh giá cao về chất lượng.',
            'category' => 'arabica'
        ],
        
        // Robusta.php - Thêm
        18 => [
            'name' => 'Cà phê Robusta Đặc Biệt',
            'price' => 160000,
            'image' => 'https://salt.tikicdn.com/cache/750x750/ts/product/c6/a2/32/9c841efc66a4b07b2914418102b49186.jpg.webp',
            'weight' => '500g',
            'description' => 'Cà phê Robusta Đặc Biệt được chọn lọc từ những hạt cà phê chất lượng tốt nhất. Sản phẩm mang đến vị đắng đậm đà, thơm nồng và dư vị kéo dài. Đây là lựa chọn lý tưởng cho những người yêu thích cà phê đen đúng điệu.',
            'category' => 'robusta'
        ],
        19 => [
            'name' => 'Cà phê Robusta Buôn Ma Thuột',
            'price' => 140000,
            'image' => 'https://salt.tikicdn.com/cache/750x750/ts/product/f0/9a/60/2f49a4f93b1262747001968b686eb4fb.jpg.webp',
            'weight' => '500g',
            'description' => 'Cà phê Robusta Buôn Ma Thuột đến từ vùng đất nổi tiếng với cà phê ngon nhất Việt Nam. Hương vị đặc trưng với vị đắng mạnh mẽ, thơm nồng và đậm đà, mang đến cảm giác tỉnh táo, sảng khoái.',
            'category' => 'robusta'
        ],
        20 => [
            'name' => 'Cà phê Robusta Premium',
            'price' => 150000,
            'image' => 'https://devafood.vn/wp-content/uploads/2022/10/Ca-phe-Robusta-PREMIUM-Robusta-1Kg-edited_2.png.webp',
            'weight' => '500g',
            'description' => 'Cà phê Robusta Premium được chế biến từ những hạt cà phê Robusta chất lượng cao, rang vừa để giữ lại đầy đủ hương vị và dưỡng chất. Mang đến vị đắng thanh, thơm nồng và hậu vị kéo dài.',
            'category' => 'robusta'
        ],
        
        // Chon.php - Thêm
        21 => [
            'name' => 'Cà phê Chồn Đặc Biệt',
            'price' => 250000,
            'image' => 'https://huonghau.com/wp-content/uploads/2020/02/caphe-chon-dac-biet-600x428.jpg',
            'weight' => '250g',
            'description' => 'Cà phê Chồn Đặc Biệt được chọn lọc từ những hạt cà phê chất lượng nhất sau khi qua quá trình tiêu hóa của chồn. Sản phẩm mang đến hương vị tinh tế, hài hòa giữa vị chua nhẹ, đắng dịu và ngọt hậu đặc trưng.',
            'category' => 'chon'
        ],
        22 => [
            'name' => 'Cà phê Chồn Thượng Hạng',
            'price' => 3760000,
            'image' => 'https://salt.tikicdn.com/cache/750x750/ts/product/49/95/13/c1bba3c45f8353abb00c96f714c8720e.jpg.webp',
            'weight' => '150g',
            'description' => 'Cà phê Chồn Thượng Hạng là dòng sản phẩm cao cấp nhất, được chế biến với quy trình nghiêm ngặt và tiêu chuẩn khắt khe. Hương vị đặc biệt với độ chua nhẹ, vị đắng tinh tế và hương thơm phong phú, tạo nên trải nghiệm thưởng thức tuyệt vời.',
            'category' => 'chon'
        ],
        23 => [
            'name' => 'Cà phê Chồn Robusta',
            'price' => 1500000,
            'image' => 'https://sieuthicafe.com/wp-content/uploads/2020/03/robusta.png',
            'weight' => '150g',
            'description' => 'Cà phê Chồn Robusta là sự kết hợp giữa hạt cà phê Robusta và quy trình chế biến đặc biệt qua đường tiêu hóa của chồn. Sản phẩm mang đến vị đắng mạnh mẽ của Robusta nhưng được cân bằng bởi quá trình chế biến đặc biệt.',
            'category' => 'chon'
        ],
        24 => [
            'name' => 'Cà phê Chồn Arabica',
            'price' => 1360000,
            'image' => 'https://sieuthicafe.com/wp-content/uploads/2019/02/C%C3%A0-Ph%C3%AA-Ch%E1%BB%93n-Cao-C%E1%BA%A5p-CIVET-Coffee-Arabica--768x768.jpg',
            'weight' => '100g',
            'description' => 'Cà phê Chồn Arabica kết hợp giữa hạt Arabica cao cấp và quy trình chế biến chồn, mang đến hương vị tinh tế nhất. Vị chua nhẹ của Arabica hòa quyện với hương thơm đặc biệt sau quá trình chế biến tạo nên sản phẩm đặc biệt.',
            'category' => 'chon'
        ]
    ];

    // Lấy ID từ URL
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Kiểm tra sản phẩm có tồn tại không
    if (isset($all_products[$product_id])) {
        $product = $all_products[$product_id];
        $category = $product['category'];
        
        // Hiển thị chi tiết sản phẩm
        echo "
        <section class='product-detail'>
            <img src='{$product['image']}' alt='{$product['name']}' class='product-image'>
            <div class='product-info'>
                <h1>{$product['name']}</h1>
                <p class='price'>" . number_format($product['price'], 0, ',', '.') . " VNĐ / {$product['weight']}</p>
                <p class='description'>{$product['description']}</p>
                <a href='#' class='btn' onclick=\"addToCart('{$product['name']} - {$product['weight']}', {$product['price']})\">Thêm vào giỏ</a>
            </div>
        </section>";
        
        // Hiển thị sản phẩm liên quan (cùng danh mục)
        echo "<section class='related-products'>
            <h2 style='text-align: center; margin-bottom: 30px; font-family: \"Playfair Display\", serif; color: #3c2f2f;'>Sản phẩm tương tự</h2>
            <div class='product-grid'>";
            
        $count = 0;
        foreach ($all_products as $id => $related) {
            if ($related['category'] == $category && $id != $product_id && $count < 3) {
                echo "
                <div class='product-card'>
                    <a href='product-detail.php?id={$id}'>
                        <img src='{$related['image']}' alt='{$related['name']}'>
                    </a>
                    <a href='product-detail.php?id={$id}'><h3>{$related['name']}</h3></a>
                    <p>" . number_format($related['price'], 0, ',', '.') . " VNĐ / {$related['weight']}</p>
                    <a href='#' class='btn' onclick=\"addToCart('{$related['name']} - {$related['weight']}', {$related['price']})\">Thêm vào giỏ</a>
                </div>";
                $count++;
            }
        }
        
        echo "</div></section>";
        
    } else {
        // Hiển thị thông báo lỗi
        echo "
        <div class='not-found'>
            <h2>Sản phẩm không tồn tại</h2>
            <p>Rất tiếc, sản phẩm bạn đang tìm kiếm không tồn tại hoặc đã bị xóa khỏi hệ thống.</p>
            <p>Vui lòng quay lại trang sản phẩm để xem các sản phẩm khác.</p>
            <a href='products.php' class='btn'>Xem tất cả sản phẩm</a>
        </div>";
    }
    ?>

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