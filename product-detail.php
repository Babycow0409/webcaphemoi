<?php
include 'includes/db_connect.php';

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Chuyển hướng nếu không có ID
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Lấy thông tin sản phẩm
$sql = "SELECT p.*, c.name as category_name FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra sản phẩm có tồn tại không
if ($result->num_rows === 0) {
    // Nếu sản phẩm không tồn tại, hiển thị thông báo thân thiện
    $product_not_found = true;
} else {
    $product = $result->fetch_assoc();
    
    // Lấy thêm sản phẩm liên quan dựa trên category_id
    if (isset($product['category_id']) && !empty($product['category_id'])) {
        $category_id = $product['category_id'];
        $sql_related = "SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4";
        $stmt_related = $conn->prepare($sql_related);
        $stmt_related->bind_param("ii", $category_id, $product_id);
        $stmt_related->execute();
        $result_related = $stmt_related->get_result();
        $related_products = [];
        
        if ($result_related->num_rows > 0) {
            while ($row = $result_related->fetch_assoc()) {
                $related_products[] = $row;
            }
        }
    } else {
        $related_products = [];
    }
}

// Kiểm tra và hiển thị an toàn
$product_name = isset($product['name']) ? htmlspecialchars($product['name']) : 'Sản phẩm không xác định';
$product_price = isset($product['price']) ? number_format($product['price'], 0, ',', '.') : '0';
$product_weight = isset($product['weight']) ? htmlspecialchars($product['weight']) : '';
$product_description = isset($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'Không có mô tả';
$product_image = isset($product['image']) && !empty($product['image']) ? $product['image'] : 'path/to/default-image.jpg';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($product) ? $product['name'] . ' - Cà Phê Đậm Đà' : 'Sản phẩm không tồn tại - Cà Phê Đậm Đà'; ?></title>
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
        .product-detail { 
            max-width: 1200px; 
            margin: 50px auto; 
            padding: 20px; 
            display: flex; 
            flex-wrap: wrap; 
            gap: 40px; 
        }
        
        .product-detail .product-image {
            flex: 1;
            min-width: 300px;
            max-width: 500px;
            height: auto;
            text-align: center;
        }
        
        .product-detail .product-image img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .product-info { 
            flex: 1; 
            min-width: 300px; 
        }
        h1 { font-family: 'Playfair Display', serif; color: #3c2f2f; margin-bottom: 20px; }
        .product-price { 
            color: #d4a373; 
            font-size: 1.5em; 
            margin: 15px 0; 
            font-weight: bold;
        }
        .product-weight {
            font-size: 1.1em;
            color: #666;
            margin: 10px 0;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        .quantity-controls button {
            width: 30px;
            height: 30px;
            background: #d4a373;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
        }
        .quantity-controls input {
            width: 50px;
            height: 30px;
            text-align: center;
            margin: 0 10px;
            border: 1px solid #ccc;
        }
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
        
        .product-not-found {
            text-align: center;
            padding: 50px 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .product-not-found h1 {
            font-size: 28px;
            color: #d9534f;
            margin-bottom: 20px;
        }
        
        .product-not-found p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .product-not-found .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .product-not-found .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .product-not-found .btn-primary {
            background-color: #5a3921;
            color: white;
            border: none;
        }
        
        .product-not-found .btn-primary:hover {
            background-color: #3d2715;
        }
        
        .product-not-found .btn-secondary {
            background-color: #e9ecef;
            color: #333;
            border: 1px solid #ced4da;
        }
        
        .product-not-found .btn-secondary:hover {
            background-color: #ced4da;
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
    
    <!-- Thông báo thêm vào giỏ hàng -->
    <div id="cart-message" style="display: none; background-color: #4CAF50; color: white; text-align: center; padding: 10px; position: fixed; top: 80px; left: 50%; transform: translateX(-50%); border-radius: 5px; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2); width: 300px;"></div>

    <div class="container">
        <?php if (isset($product_not_found) && $product_not_found): ?>
            <!-- Thông báo sản phẩm không tồn tại -->
            <div class="product-not-found">
                <h1>Sản phẩm không tồn tại</h1>
                <p>Rất tiếc, sản phẩm bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
                <div class="actions">
                    <a href="products.php" class="btn btn-primary">Xem tất cả sản phẩm</a>
                    <a href="index.php" class="btn btn-secondary">Về trang chủ</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Hiển thị chi tiết sản phẩm -->
            <div class="product-detail">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product_image); ?>" alt="<?php echo htmlspecialchars($product_name); ?>">
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product_name); ?></h1>
                    <p class="price"><?php echo $product_price; ?>đ</p>
                    
                    <?php if (!empty($product_weight)): ?>
                    <p class="product-weight">Trọng lượng: <?php echo htmlspecialchars($product_weight); ?></p>
                    <?php endif; ?>
                    
                    <div class="product-description">
                        <h3>Mô tả sản phẩm</h3>
                        <p class="description"><?php echo $product_description; ?></p>
                    </div>
                    
                    <div class="product-actions">
                        <div class="quantity-controls">
                            <button onclick="decreaseQuantity()" class="quantity-btn">-</button>
                            <input type="number" id="quantity" value="1" min="1" max="99" class="quantity-input">
                            <button onclick="increaseQuantity()" class="quantity-btn">+</button>
                        </div>
                        <a href="javascript:void(0);" onclick="addToCart()" class="btn">Thêm vào giỏ hàng</a>
                    </div>
                </div>
            </div>
            
            <!-- Sản phẩm liên quan -->
            <?php if (!empty($related_products)): ?>
            <div class="related-products">
                <h2>Sản phẩm liên quan</h2>
                <div class="product-grid">
                    <?php foreach ($related_products as $related): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo $related['image']; ?>" alt="<?php echo $related['name']; ?>">
                        </div>
                        <div class="product-details">
                            <h3><?php echo $related['name']; ?></h3>
                            <p class="product-price"><?php echo number_format($related['price'], 0, ',', '.'); ?>đ</p>
                            <?php if (isset($related['weight']) && !empty($related['weight'])): ?>
                            <p class="product-weight"><?php echo $related['weight']; ?></p>
                            <?php endif; ?>
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-details">Chi tiết</a>
                                <button class="btn btn-cart" onclick="addToCart('<?php echo $related['id']; ?>', '<?php echo addslashes($related['name']); ?>', <?php echo (float)$related['price']; ?>, '<?php echo addslashes($related['image']); ?>')">
                                    Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script>
    function decreaseQuantity() {
        var input = document.getElementById('quantity');
        var value = parseInt(input.value);
        if(value > 1) {
            input.value = value - 1;
        }
    }

    function increaseQuantity() {
        var input = document.getElementById('quantity');
        var value = parseInt(input.value);
        if(value < 99) {
            input.value = value + 1;
        }
    }

    function addToCart() {
        var quantity = document.getElementById('quantity').value;
        var id = "<?php echo $product['id']; ?>";
        var name = "<?php echo addslashes($product['name']); ?>";
        var price = <?php echo $product['price']; ?>;
        var image = "<?php echo isset($product['image']) ? $product['image'] : 'images/default-product.jpg'; ?>";
        
        // Điều hướng đến trang add-to-cart.php với tham số GET
        window.location.href = "add-to-cart.php?id=" + encodeURIComponent(id) + 
                               "&name=" + encodeURIComponent(name) + 
                               "&price=" + encodeURIComponent(price) + 
                               "&image=" + encodeURIComponent(image) + 
                               "&quantity=" + encodeURIComponent(quantity);
    }
    </script>
</body>
</html>