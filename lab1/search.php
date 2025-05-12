<?php
session_start();
require_once 'includes/db_connect.php'; // Kết nối database

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
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.active = 1";
$params = [];
$param_types = "";

// Tìm kiếm theo tên sản phẩm
if (!empty($search_term)) {
    $sql .= " AND (LOWER(p.name) LIKE ? OR LOWER(p.description) LIKE ?)";
    $search_param = "%{$search_term}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

// Lọc theo phân loại
if (!empty($category)) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
    $param_types .= "i";
}

// Lọc theo khoảng giá
if ($min_price !== null && $max_price !== null) {
    // Trường hợp "trên X đồng" (min > 0, max = 0)
    if ($min_price > 0 && $max_price == 0) {
        $sql .= " AND p.price >= ?";
        $params[] = $min_price;
        $param_types .= "i";
    }
    // Trường hợp "dưới X đồng" (min = 0, max > 0)
    else if ($min_price == 0 && $max_price > 0) {
        $sql .= " AND p.price <= ?";
        $params[] = $max_price;
        $param_types .= "i";
    }
    // Trường hợp khoảng giá từ min đến max
    else if ($min_price > 0 && $max_price > 0) {
        $sql .= " AND p.price >= ? AND p.price <= ?";
        $params[] = $min_price;
        $params[] = $max_price;
        $param_types .= "ii";
    }
}

// Sắp xếp kết quả
$sql .= " ORDER BY p.id DESC";

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
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
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
        }
        .product-card:hover { transform: scale(1.05); }
        .product-card img { 
            width: 100%; 
            height: 180px;
            object-fit: cover;
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
            min-width: 170px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
            margin-top: -2px;
            padding-top: 10px;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .dropdown-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .search-result-summary {
            background-color: #f8f3eb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .not-found {
            text-align: center;
            padding: 50px 0;
            color: #555;
        }
        .pagination {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        .pagination a {
            color: #3c2f2f;
            text-decoration: none;
            padding: 8px 16px;
            margin: 0 5px;
            border: 1px solid #d4a373;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .pagination a.active {
            background-color: #d4a373;
            color: white;
        }
        .pagination a:hover:not(.active) {
            background-color: #f8f3eb;
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
        <h1>Kết quả tìm kiếm</h1>
        
        <?php include 'includes/search-form.php'; ?>
        
        <div class="search-result-summary">
            <?php
            $searchDescription = [];
            
            if (!empty($search_term)) {
                $searchDescription[] = "từ khóa '<strong>" . htmlspecialchars($search_term) . "</strong>'";
            }
            
            if (!empty($category)) {
                $cat_query = "SELECT name FROM categories WHERE id = ?";
                $cat_stmt = $conn->prepare($cat_query);
                $cat_stmt->bind_param("i", $category);
                $cat_stmt->execute();
                $cat_result = $cat_stmt->get_result();
                if ($cat_result && $cat_result->num_rows > 0) {
                    $cat_name = $cat_result->fetch_assoc()['name'];
                    $searchDescription[] = "danh mục '<strong>" . htmlspecialchars($cat_name) . "</strong>'";
                }
            }
            
            if (!empty($price_range)) {
                $price_text = "";
                if ($min_price > 0 && $max_price == 0) {
                    $price_text = "giá trên " . number_format($min_price, 0, ',', '.') . "đ";
                } else if ($min_price == 0 && $max_price > 0) {
                    $price_text = "giá dưới " . number_format($max_price, 0, ',', '.') . "đ";
                } else if ($min_price > 0 && $max_price > 0) {
                    $price_text = "giá từ " . number_format($min_price, 0, ',', '.') . "đ đến " . number_format($max_price, 0, ',', '.') . "đ";
                }
                if (!empty($price_text)) {
                    $searchDescription[] = $price_text;
                }
            }
            
            $description = !empty($searchDescription) ? "Tìm kiếm với " . implode(", ", $searchDescription) : "Tất cả sản phẩm";
            echo "<p>{$description}</p>";
            echo "<p>Tìm thấy <strong>" . count($filtered_products) . "</strong> sản phẩm phù hợp</p>";
            ?>
        </div>
        
        <div class="product-grid">
            <?php
            if (count($paginatedProducts) > 0) {
                foreach ($paginatedProducts as $product) {
                    // Xử lý đường dẫn hình ảnh
                    $imagePath = $product['image'];
                    if (!empty($imagePath) && strpos($imagePath, 'uploads/') === false) {
                        $imagePath = 'uploads/products/' . $imagePath;
                    }
                    
                    echo "<div class='product-card'>
                        <img src='" . htmlspecialchars($imagePath) . "' alt='" . htmlspecialchars($product['name']) . "' onerror=\"this.src='images/default-product.jpg'\">
                        <div class='product-info'>
                            <h3>" . htmlspecialchars($product['name']) . "</h3>";
                    
                    if (isset($product['category_name']) && !empty($product['category_name'])) {
                        echo "<p class='category'>" . htmlspecialchars($product['category_name']) . "</p>";
                    }
                    
                    echo "<p class='price'>" . number_format($product['price'], 0, ',', '.') . " VNĐ</p>
                        </div>
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
            } else {
                echo "<div class='not-found'>
                    <i class='fas fa-search' style='font-size: 3em; color: #d4a373; margin-bottom: 20px;'></i>
                    <h2>Không tìm thấy sản phẩm nào phù hợp</h2>
                    <p>Hãy thử tìm kiếm với từ khóa khác hoặc điều chỉnh bộ lọc của bạn.</p>
                    <a href='products.php' class='btn'>Xem tất cả sản phẩm</a>
                </div>";
            }
            ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
            <a href="?q=<?php echo urlencode($search_term); ?>&category=<?php echo $category; ?>&price_range=<?php echo $price_range; ?>&page=<?php echo $currentPage - 1; ?>">Trước</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?q=<?php echo urlencode($search_term); ?>&category=<?php echo $category; ?>&price_range=<?php echo $price_range; ?>&page=<?php echo $i; ?>" class="<?php echo ($i == $currentPage) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
            <a href="?q=<?php echo urlencode($search_term); ?>&category=<?php echo $category; ?>&price_range=<?php echo $price_range; ?>&page=<?php echo $currentPage + 1; ?>">Tiếp</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
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
</body>
</html> 