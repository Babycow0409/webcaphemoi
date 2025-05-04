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

// Tìm category_id cho Arabica
$category_name = 'Arabica';
$cat_query = "SELECT id FROM categories WHERE name LIKE ?";
$cat_stmt = $conn->prepare($cat_query);
$search_param = "%$category_name%";
$cat_stmt->bind_param("s", $search_param);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$categoryId = 1; // Mặc định ID cho Arabica

if ($cat_result && $cat_result->num_rows > 0) {
    $categoryId = $cat_result->fetch_assoc()['id'];
}

// Lấy sản phẩm Arabica từ database
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.category_id = ? AND p.active = 1
        ORDER BY p.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();
$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arabica - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/search-form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Roboto', sans-serif; 
        }
        body { 
            padding-top: 100px; 
            line-height: 1.6; 
        }
        header { 
            background-color: #3c2f2f; 
            color: white; 
            padding: 1rem; 
            position: fixed; 
            width: 100%; 
            top: 0; 
            z-index: 1000; 
        }
        nav { 
            display: flex; 
            flex-wrap: wrap; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .logo { 
            font-family: 'Playfair Display', serif; 
            font-size: 1.8em; 
            padding: 10px; 
        }
        .nav-links { 
            display: flex; 
            flex-wrap: wrap; 
            align-items: center; 
            padding: 10px; 
        }
        nav a { 
            color: white; 
            text-decoration: none; 
            margin: 10px 15px; 
            font-weight: bold; 
        }
        nav a:hover { 
            color: #d4a373; 
        }
        h1, h2 { 
            font-family: 'Playfair Display', serif; 
            color: #3c2f2f; 
            text-align: center; 
            margin: 40px 0 20px; 
        }
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
            nav { 
                flex-direction: column; 
                padding: 10px; }
            .nav-links { 
                flex-direction: column; 
                margin-top: 15px; 
            }
            nav a { 
                margin: 8px 0; }
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
        .cart-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            max-width: 300px;
        }
        .cart-icon {
            position: relative;
            display: inline-block;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #d4a373;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
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
                <a href="cart.php" class="cart-icon">
                    Giỏ hàng
                    <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </nav>
    </header>

    <div id="cartNotification" class="cart-notification"></div>

    <?php if(isset($_SESSION['cart_message'])): ?>
    <div class="cart-notification" style="display: block;" id="sessionMessage">
        <?php echo $_SESSION['cart_message']; ?>
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('sessionMessage').style.display = 'none';
        }, 3000);
    </script>
    <?php unset($_SESSION['cart_message']); ?>
    <?php endif; ?>

    <section class="products">
        <h1>Cà phê Arabica</h1>
        
        <div class="info-section">
            <h2>Giới thiệu về cà phê Arabica</h2>
            <p>
                Cà phê Arabica được biết đến là loại cà phê cao cấp nhất thế giới, chiếm khoảng 60% sản lượng cà phê toàn cầu. Hạt cà phê Arabica có hình dáng ovan, phẳng và dài hơn so với các loại cà phê khác, với một đường cong đặc trưng.
            </p>
            <p>
                Cà phê Arabica nổi tiếng với hương vị tinh tế, cân bằng và phức tạp. Đặc trưng của loại cà phê này là vị chua nhẹ dễ chịu, hương thơm phong phú có thể bao gồm các nốt hương hoa, trái cây, hạt, socola hoặc caramel tùy thuộc vào nguồn gốc trồng trọt. Arabica thường có hàm lượng caffeine thấp hơn so với các loại cà phê khác, nhưng lại mang đến trải nghiệm thưởng thức tinh tế hơn.
            </p>
        </div>
        
        <?php
        $hideCategory = true;
        $currentCategory = 'arabica';
        include 'includes/search-form.php';
        ?>
        
        <div class="product-grid">
            <?php
            if (count($products) > 0) {
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
                            <button type='button' onclick='addToCart(" . $product['id'] . ", \"" . addslashes($product['name']) . "\", " . $product['price'] . ", \"" . addslashes($imagePath) . "\")' class='btn'>Thêm vào giỏ hàng</button>
                        </div>
                    </div>";
                }
            } else {
                echo "<p class='text-center'>Không có sản phẩm nào trong danh mục này.</p>";
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

    <script>
        // Tạo hàm hiển thị thông báo
        function showNotification(message) {
            const notification = document.getElementById('cartNotification');
            notification.textContent = message;
            notification.style.display = 'block';
            
            // Tự động ẩn thông báo sau 3 giây
            setTimeout(function() {
                notification.style.display = 'none';
            }, 3000);
        }
        
        // Hàm thêm sản phẩm vào giỏ hàng
        function addToCart(id, name, price, image) {
            // Gửi yêu cầu Ajax để thêm sản phẩm vào giỏ hàng
            fetch('add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&name=${encodeURIComponent(name)}&price=${price}&image=${encodeURIComponent(image)}&quantity=1&ajax=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hiển thị thông báo
                    showNotification(`Đã thêm "${name}" vào giỏ hàng!`);
                    
                    // Cập nhật số lượng sản phẩm trong giỏ hàng trên giao diện
                    updateCartCount(data.count);
                } else {
                    showNotification('Có lỗi xảy ra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng');
            });
        }
        
        // Hàm cập nhật số lượng sản phẩm trong giỏ hàng
        function updateCartCount(count) {
            const cartLinks = document.querySelectorAll('.cart-icon');
            
            cartLinks.forEach(link => {
                // Xóa số đếm cũ nếu có
                const oldCount = link.querySelector('.cart-count');
                if (oldCount) {
                    oldCount.remove();
                }
                
                // Thêm số đếm mới nếu có sản phẩm trong giỏ hàng
                if (count > 0) {
                    const countSpan = document.createElement('span');
                    countSpan.className = 'cart-count';
                    countSpan.textContent = count;
                    link.appendChild(countSpan);
                }
            });
        }
    </script>
</body>
</html> 