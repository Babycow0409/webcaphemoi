<?php
session_start();
require_once 'includes/db_connect.php'; // Kết nối database thay vì dùng file products.php
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/search-form.css">
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
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            padding: 15px;
        }
        .product-card { 
            background-color: #fffaf0; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
            transition: transform 0.3s; 
            display: flex; 
            flex-direction: column;
            height: 400px;
        }
        .product-card:hover { transform: scale(1.05); }
        .product-card img { 
            width: 100%; 
            height: 180px;
            object-fit: contain;
            border-radius: 5px; 
            cursor: pointer;
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
        }
        .product-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .product-card h3 { 
            margin: 10px 0; 
            color: #3c2f2f; 
            cursor: pointer; 
            font-size: 1.1em;
            height: 2.4em;
            overflow: hidden;
        }
        .product-card p { 
            color: #555; 
            margin-bottom: 15px;
            font-size: 0.9em;
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
        
        <?php include 'includes/search-form.php'; ?>
        
        <div class="product-grid" id="productGrid">
            <?php
            // Lấy các tham số tìm kiếm từ URL
            $search_term = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
            $category = isset($_GET['category']) ? $_GET['category'] : '';
            $price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';

            // Xử lý khoảng giá
            $min_price = null;
            $max_price = null;
            if (!empty($price_range)) {
                $price_parts = explode('-', $price_range);
                $min_price = (int)$price_parts[0];
                $max_price = (int)$price_parts[1];
            }

            // Xây dựng câu truy vấn SQL
            $sql = "SELECT * FROM products WHERE 1=1";
            $params = [];
            $param_types = "";
            
            // Tìm kiếm theo tên sản phẩm
            if (!empty($search_term)) {
                $sql .= " AND LOWER(name) LIKE ?";
                $params[] = "%{$search_term}%";
                $param_types .= "s";
            }
            
            // Lọc theo phân loại
            if (!empty($category)) {
                $sql .= " AND category = ?";
                $params[] = $category;
                $param_types .= "s";
            }
            
            // Lọc theo khoảng giá
            if ($min_price !== null && $max_price !== null) {
                // Trường hợp "trên X đồng" (min > 0, max = 0)
                if ($min_price > 0 && $max_price == 0) {
                    $sql .= " AND price >= ?";
                    $params[] = $min_price;
                    $param_types .= "i";
                }
                // Trường hợp "dưới X đồng" (min = 0, max > 0)
                else if ($min_price == 0 && $max_price > 0) {
                    $sql .= " AND price < ?";
                    $params[] = $max_price;
                    $param_types .= "i";
                }
                // Trường hợp khoảng giá từ min đến max
                else {
                    $sql .= " AND price >= ? AND price < ?";
                    $params[] = $min_price;
                    $params[] = $max_price;
                    $param_types .= "ii";
                }
            }
            
            // Chuẩn bị câu lệnh
            $stmt = $conn->prepare($sql);
            
            // Bind params nếu có
            if (!empty($params)) {
                $stmt->bind_param($param_types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $filtered_products = [];
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $filtered_products[] = $row;
                }
            }
            
            // Phân trang
            $productsPerPage = 8; // Số sản phẩm mỗi trang
            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $totalProducts = count($filtered_products);
            $totalPages = ceil($totalProducts / $productsPerPage);

            // Giới hạn sản phẩm hiển thị theo trang
            $paginatedProducts = array_slice(
                $filtered_products, 
                ($currentPage - 1) * $productsPerPage, 
                $productsPerPage
            );

            if (count($filtered_products) > 0) {
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
                echo "<div class='not-found'>Không tìm thấy sản phẩm nào phù hợp với từ khóa '$search_term'</div>";
            }

            // Code debug tạm thời - xóa sau khi đã kiểm tra xong
            echo "<pre style='display:none;'>";
            echo "search_term: " . $search_term . "\n";
            echo "category: " . $category . "\n";
            echo "price_range: " . $price_range . "\n";
            echo "min_price: " . $min_price . "\n";
            echo "max_price: " . $max_price . "\n";
            echo "total products after filter: " . count($filtered_products) . "\n";
            echo "</pre>";
            ?>
        </div>

        <!-- Thêm nút phân trang ở cuối -->
        <?php if($totalProducts > 0): ?>
        <div class="pagination">
            <?php if($currentPage > 1): ?>
                <a href="?q=<?php echo urlencode($search_term); ?>&category=<?php echo $category; ?>&price_range=<?php echo $price_range; ?>&page=<?php echo $currentPage - 1; ?>" class="page-link">&laquo; Trang trước</a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?q=<?php echo urlencode($search_term); ?>&category=<?php echo $category; ?>&price_range=<?php echo $price_range; ?>&page=<?php echo $i; ?>" class="page-link <?php echo $i == $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if($currentPage < $totalPages): ?>
                <a href="?q=<?php echo urlencode($search_term); ?>&category=<?php echo $category; ?>&price_range=<?php echo $price_range; ?>&page=<?php echo $currentPage + 1; ?>" class="page-link">Trang sau &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>

    <script>
        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        function addToCart(id, name, price, image) {
            const existingItem = cart.find(item => item.name === name);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({ id, name, price, quantity: 1, image });
            }
            localStorage.setItem("cart", JSON.stringify(cart));
            alert(`${name} đã được thêm vào giỏ hàng!`);
        }
    </script>
</body>
</html> 