<?php
session_start();

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý phân trang
$productsPerPage = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

// Xử lý filter theo category nếu có
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;
$whereClause = $category ? "WHERE p.category_id = $category" : "";

// Đếm tổng số sản phẩm
$countQuery = "SELECT COUNT(*) as total FROM products p $whereClause";
$countResult = $conn->query($countQuery);
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $productsPerPage);

// Lấy danh mục nếu có filter
$category_name = "";
if ($category) {
    $cat_query = "SELECT name FROM categories WHERE id = $category";
    $cat_result = $conn->query($cat_query);
    if ($cat_result && $cat_result->num_rows > 0) {
        $category_name = $cat_result->fetch_assoc()['name'];
    }
}

// Lấy sản phẩm theo filter và phân trang
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          $whereClause 
          ORDER BY p.id DESC LIMIT $offset, $productsPerPage";
$result = $conn->query($query);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Lấy danh sách danh mục cho dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <script src="js/cart.js"></script>
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
        .filter-container {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-weight: bold;
            color: #3c2f2f;
            font-size: 0.9em;
        }
        .filter-input, .filter-select {
            padding: 8px 12px;
            border: 1px solid #d4a373;
            border-radius: 5px;
            min-width: 150px;
        }
        .filter-btn {
            padding: 8px 15px;
            background-color: #d4a373;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .filter-btn:hover {
            background-color: #8b4513;
        }
        .advanced-search {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: center;
            gap: 15px;
        }
        .filter-section {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-section label {
            font-weight: bold;
            color: #3c2f2f;
            font-size: 0.9em;
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
        .page-link:hover {
            background-color: #d4a373;
            color: white;
        }
        .page-link.active {
            background-color: #d4a373;
            color: white;
            font-weight: bold;
        }
        .product-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }
        .view-detail-btn, .add-to-cart-btn {
            padding: 10px 20px;
            background-color: #d4a373;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-block;
            text-align: center;
        }
        .view-detail-btn:hover, .add-to-cart-btn:hover {
            background-color: #8b4513;
            transform: scale(1.05);
        }
        .view-detail-btn {
            background-color: #f3e3d3;
            color: #3c2f2f;
        }
        .add-to-cart-btn {
            background-color: #d4a373;
            color: white;
        }
        .category-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .category-filter a {
            padding: 8px 15px;
            background-color: #f8f8f8;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .category-filter a.active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a {
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            transition: background-color .3s;
            border: 1px solid #ddd;
            margin: 0 4px;
        }
        
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
        
        .pagination a:hover:not(.active) {
            background-color: #ddd;
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
                <a href="#about">Giới thiệu</a>
                <a href="#contact">Liên hệ</a>
                <a href="cart.php" style="position: relative;">
                    Giỏ hàng
                    <span id="cartCount" style="position: absolute; top: -8px; right: -8px; background-color: #d4a373; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">0</span>
                </a>
                <?php
                if(isset($_SESSION['user_id'])) {
                    // Hiển thị tên người dùng nếu đã đăng nhập
                    echo '<span style="color: #d4a373; margin-right: 15px;">Xin chào, ' . (isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Khách hàng') . '</span>';
                    
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

    <!-- Thông báo thêm vào giỏ hàng -->
    <div id="cart-message" style="display: none; background-color: #4CAF50; color: white; text-align: center; padding: 10px; position: fixed; top: 80px; left: 50%; transform: translateX(-50%); border-radius: 5px; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2); width: 300px;"></div>

    <section class="products">
        <h1><?php echo !empty($category_name) ? "Cà phê " . htmlspecialchars($category_name) : "Sản phẩm của chúng tôi"; ?></h1>
        
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
        
        <div class="filter-container">
            <form action="search.php" method="get" class="filter-form">
                <div class="filter-group">
                    <label>Tìm kiếm:</label>
                    <input type="text" name="q" placeholder="Tên sản phẩm..." class="filter-input">
                </div>
                
                <div class="filter-group">
                    <label>Phân loại:</label>
                    <select name="category" class="filter-select">
                        <option value="">Tất cả</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Khoảng giá:</label>
                    <select name="price_range" class="filter-select">
                        <option value="">Tất cả giá</option>
                        <option value="0-100000">Dưới 100.000đ</option>
                        <option value="100000-300000">100.000đ - 300.000đ</option>
                        <option value="300000-500000">300.000đ - 500.000đ</option>
                        <option value="500000-1000000">500.000đ - 1.000.000đ</option>
                        <option value="1000000-0">Trên 1.000.000đ</option>
                    </select>
                </div>
                
                <button type="submit" class="filter-btn">Lọc</button>
            </form>
        </div>
        <div class="product-grid" id="productGrid">
            <?php
            foreach ($products as $product) {
                // Xử lý đường dẫn hình ảnh, thêm upload nếu cần thiết
                $imagePath = $product['image'];
                if (!empty($imagePath) && strpos($imagePath, 'uploads/') === false) {
                    $imagePath = 'uploads/products/' . $imagePath;
                }
                
                echo "
                <div class='product-card'>
                    <img src='" . htmlspecialchars($imagePath) . "' alt='" . htmlspecialchars($product['name']) . "' onerror=\"this.src='images/default-product.jpg'\">
                    <h3>" . htmlspecialchars($product['name']) . "</h3>";
                    
                if (isset($product['category_name']) && !empty($product['category_name'])) {
                    echo "<span style='font-size:0.8em; color:#666; display:block;margin-bottom:5px;'>" . htmlspecialchars($product['category_name']) . "</span>";
                }
                
                echo "<p class='price'>" . number_format($product['price'], 0, ',', '.') . " VNĐ</p>
                    <div class='product-actions'>
                        <a href='product-detail.php?id=" . $product['id'] . "' class='btn'>Xem chi tiết</a>
                        <a href='add-to-cart.php?id=" . urlencode($product['id']) . 
                           "&name=" . urlencode($product['name']) . 
                           "&price=" . urlencode($product['price']) . 
                           "&image=" . urlencode($imagePath) . 
                           "&quantity=1' class='btn'>Thêm vào giỏ hàng</a>
                    </div>
                </div>";
            }
            ?>
        </div>
    </section>
    <footer id="contact" style="background-color: #3c2f2f; color: white; padding: 30px 0; margin-top: 50px;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <h2 style="color: white; text-align: center; margin-bottom: 20px; font-family: 'Playfair Display', serif;">Liên hệ</h2>
            <p style="margin: 20px 0; text-align: center;">
                Địa chỉ: 123 Đường Nguyễn Huệ, Quận 1, TP.HCM<br>
                Email: info@caphedamda.com<br>
                Điện thoại: 0909 123 456
            </p>
            <div style="margin: 20px 0; text-align: center;">
                <a href="#" style="color: #d4a373; margin: 0 10px; text-decoration: none;"><i class="fab fa-facebook"></i> Facebook</a>
                <a href="#" style="color: #d4a373; margin: 0 10px; text-decoration: none;"><i class="fab fa-instagram"></i> Instagram</a>
                <a href="#" style="color: #d4a373; margin: 0 10px; text-decoration: none;"><i class="fab fa-twitter"></i> Twitter</a>
            </div>
            <p style="margin-top: 20px; font-size: 0.9em; text-align: center; color: #aaa;">
                © 2023 Cà Phê Đậm Đà. Tất cả các quyền được bảo lưu.
            </p>
        </div>
    </footer>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        const products = <?php echo json_encode($products); ?>;
        const productGrid = document.getElementById('productGrid');
        const searchInput = document.getElementById('searchInput');

        function displayProducts(filteredProducts) {
            productGrid.innerHTML = filteredProducts.map(product => {
                // Xử lý đường dẫn hình ảnh
                let imagePath = product.image;
                if (!imagePath.includes('uploads/') && imagePath) {
                    imagePath = 'uploads/products/' + imagePath;
                }
                
                let categoryDisplay = product.category_name ? 
                    `<span style='font-size:0.8em; color:#666; display:block;margin-bottom:5px;'>${product.category_name}</span>` : '';
                
                return `
                <div class='product-card'>
                    <img src='${imagePath}' alt='${product.name}' onerror="this.src='images/default-product.jpg'">
                    <h3>${product.name}</h3>
                    ${categoryDisplay}
                    <p class='price'>${new Intl.NumberFormat('vi-VN').format(product.price)} VNĐ</p>
                    <div class='product-actions'>
                        <a href='product-detail.php?id=${product.id}' class='btn'>Xem chi tiết</a>
                        <button onclick='addToCart(${product.id}, "${product.name.replace(/"/g, '\\"')}", ${product.price}, "${imagePath.replace(/"/g, '\\"')}")' class='btn'>Thêm vào giỏ hàng</button>
                    </div>
                </div>
            `}).join('');
        }
        
        // Hiển thị tất cả sản phẩm khi trang được tải
        displayProducts(products);

        // Cập nhật số lượng giỏ hàng khi tải trang
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>