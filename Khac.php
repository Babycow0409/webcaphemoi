<?php
session_start();
require_once 'includes/db_connect.php';

// Lấy sản phẩm thuộc loại "other" từ database
$category = 'other';
$sql = "SELECT * FROM products WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();
$products = [];

// Kiểm tra và lọc theo tên sản phẩm
$search_term = '';
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = strtolower(trim($_GET['q']));
}

// Kiểm tra và lọc theo khoảng giá
$price_range = '';
$min_price = null;
$max_price = null;
if (isset($_GET['price_range']) && !empty($_GET['price_range'])) {
    $price_range = $_GET['price_range'];
    $price_parts = explode('-', $price_range);
    $min_price = (int)$price_parts[0];
    $max_price = (int)$price_parts[1];
}

// Lọc sản phẩm
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Lọc theo tên sản phẩm
        if (!empty($search_term) && strpos(strtolower($row['name']), $search_term) === false) {
            continue;
        }
        
        // Lọc theo khoảng giá
        if ($min_price !== null && $max_price !== null) {
            // Trường hợp "trên X đồng" (min > 0, max = 0)
            if ($min_price > 0 && $max_price == 0) {
                if ($row['price'] < $min_price) {
                    continue;
                }
            }
            // Trường hợp "dưới X đồng" (min = 0, max > 0)
            else if ($min_price == 0 && $max_price > 0) {
                if ($row['price'] >= $max_price) {
                    continue;
                }
            }
            // Trường hợp khoảng giá từ min đến max
            else {
                if ($row['price'] < $min_price || $row['price'] >= $max_price) {
                    continue;
                }
            }
        }
        
        $products[] = $row;
    }
}

// Phân trang
$productsPerPage = 8;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalProducts = count($products);
$totalPages = ceil($totalProducts / $productsPerPage);

// Giới hạn sản phẩm hiển thị theo trang
$paginatedProducts = array_slice(
    $products, 
    ($currentPage - 1) * $productsPerPage, 
    $productsPerPage
);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Các loại cà phê khác - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        .product-card img:hover { transform: scale(1.05); }
        
        .product-card h3 { 
            margin: 15px 0; 
            color: #3c2f2f; 
            cursor: pointer; 
            height: 2.4em;
            overflow: hidden;
        }
        .product-card h3:hover { color: #d4a373; }
        
        .product-card p { 
            color: #555; 
            margin-bottom: 15px;
        }
        
        .filter-container {
            background-color: #3c2f2f;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .filter-form {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .search-section, .price-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin: 0 10px;
        }
        
        .filter-form label {
            color: white;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .filter-form input, .filter-form select {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            width: 220px;
            font-size: 0.9em;
        }
        
        .search-btn {
            background-color: #d4a373;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 25px;
            transition: background-color 0.3s;
        }
        
        .search-btn:hover {
            background-color: #c69c6d;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
        }
        
        .page-link {
            padding: 8px 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
            color: #3c2f2f;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .page-link:hover, .page-link.active {
            background-color: #d4a373;
            color: white;
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
        
        @media (max-width: 768px) {
            nav { flex-direction: column; padding: 10px; }
            .nav-links { flex-direction: column; margin-top: 15px; }
            nav a { margin: 8px 0; }
            .product-grid { grid-template-columns: 1fr; }
            .filter-form {
                flex-direction: column;
            }
            .search-section, .price-section {
                width: 100%;
                margin: 5px 0;
            }
            .filter-form input, .filter-form select {
                width: 100%;
            }
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
        <p style="text-align: center; max-width: 800px; margin: 0 auto 30px;">
            Ngoài các dòng cà phê chính, chúng tôi còn cung cấp nhiều loại cà phê đặc biệt khác như Mocha, Culi, 
            Espresso, Latte và nhiều loại thức uống đặc trưng khác. Khám phá thêm hương vị đặc sắc với các sản phẩm bên dưới.
        </p>
        
        <!-- Bộ lọc -->
        <div class="filter-container">
            <form action="" method="GET" class="filter-form">
                <div class="search-section">
                    <label>🔍 Tìm kiếm:</label>
                    <input type="text" name="q" placeholder="Nhập tên sản phẩm..." 
                           value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                </div>
                
                <div class="price-section">
                    <label>💰 Khoảng giá:</label>
                    <select name="price_range">
                        <option value="">Tất cả giá</option>
                        <option value="0-100000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '0-100000') ? 'selected' : ''; ?>>Dưới 100.000đ</option>
                        <option value="100000-200000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '100000-200000') ? 'selected' : ''; ?>>100.000đ - 200.000đ</option>
                        <option value="200000-300000" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '200000-300000') ? 'selected' : ''; ?>>200.000đ - 300.000đ</option>
                        <option value="300000-0" <?php echo (isset($_GET['price_range']) && $_GET['price_range'] == '300000-0') ? 'selected' : ''; ?>>Trên 300.000đ</option>
                    </select>
                </div>
                
                <button type="submit" class="search-btn">Tìm Kiếm</button>
            </form>
        </div>
        
        <!-- Hiển thị sản phẩm -->
        <div class="product-grid">
            <?php
            if (count($paginatedProducts) > 0) {
                foreach ($paginatedProducts as $product) {
                    echo "
                    <div class='product-card'>
                        <a href='product-detail.php?id={$product['id']}'>
                            <img src='{$product['image']}' alt='{$product['name']}'>
                        </a>
                        <div class='product-info'>
                            <a href='product-detail.php?id={$product['id']}'><h3>{$product['name']}</h3></a>
                            <p>" . number_format($product['price'], 0, ',', '.') . " VNĐ / {$product['weight']}</p>
                            <a href='#' class='btn' onclick=\"addToCart('{$product['id']}', '{$product['name']}', {$product['price']}, '{$product['image']}')\">Thêm vào giỏ</a>
                        </div>
                    </div>";
                }
            } else {
                echo "<p style='text-align: center; grid-column: 1 / -1;'>Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm.</p>";
            }
            ?>
        </div>
        
        <!-- Phân trang -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($currentPage > 1): ?>
                <a href="?q=<?php echo isset($_GET['q']) ? urlencode($_GET['q']) : ''; ?>&price_range=<?php echo isset($_GET['price_range']) ? $_GET['price_range'] : ''; ?>&page=<?php echo $currentPage - 1; ?>" class="page-link">&laquo; Trang trước</a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?q=<?php echo isset($_GET['q']) ? urlencode($_GET['q']) : ''; ?>&price_range=<?php echo isset($_GET['price_range']) ? $_GET['price_range'] : ''; ?>&page=<?php echo $i; ?>" class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if($currentPage < $totalPages): ?>
                <a href="?q=<?php echo isset($_GET['q']) ? urlencode($_GET['q']) : ''; ?>&price_range=<?php echo isset($_GET['price_range']) ? $_GET['price_range'] : ''; ?>&page=<?php echo $currentPage + 1; ?>" class="page-link">Trang sau &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>

    <footer id="contact" style="background-color: #3c2f2f; color: white; padding: 40px 0; margin-top: 50px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <h2 style="color: white;">Liên hệ</h2>
            <p style="margin: 20px 0;">
                Địa chỉ: 123 Đường Nguyễn Huệ, Quận 1, TP.HCM<br>
                Email: info@caphedamda.com<br>
                Điện thoại: 0909 123 456
            </p>
            <div style="margin: 20px 0;">
                <a href="#" style="color: #d4a373; margin: 0 10px;"><i class="fab fa-facebook-f"></i> Facebook</a>
                <a href="#" style="color: #d4a373; margin: 0 10px;"><i class="fab fa-instagram"></i> Instagram</a>
                <a href="#" style="color: #d4a373; margin: 0 10px;"><i class="fab fa-twitter"></i> Twitter</a>
            </div>
            <p style="margin-top: 20px; font-size: 0.9em;">
                © 2023 Cà Phê Đậm Đà. Tất cả các quyền được bảo lưu.
            </p>
        </div>
    </footer>

    <script>
        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        function addToCart(id, name, price, image) {
            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({ id, name, price, image, quantity: 1 });
            }
            localStorage.setItem("cart", JSON.stringify(cart));
            alert(`${name} đã được thêm vào giỏ hàng!`);
        }
    </script>
</body>
</html> 