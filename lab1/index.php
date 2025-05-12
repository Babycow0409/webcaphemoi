<?php
session_start();
require_once 'includes/db_connect.php';

// Đảm bảo Content-Type header được thiết lập đúng
header('Content-Type: text/html; charset=utf-8');

// Lấy danh mục sản phẩm từ database
try {
    $sql_categories = "SELECT * FROM categories ORDER BY id ASC";
    $result_categories = $conn->query($sql_categories);
    $categories = [];
    
    if ($result_categories && $result_categories->num_rows > 0) {
        while ($row = $result_categories->fetch_assoc()) {
            $categories[] = $row;
        }
    }
} catch (Exception $e) {
    // Nếu có lỗi khi truy vấn bảng categories, dùng danh mục mặc định
    $categories = [
        ['id' => 1, 'name' => 'Arabica'],
        ['id' => 2, 'name' => 'Robusta'],
        ['id' => 3, 'name' => 'Chồn'],
        ['id' => 4, 'name' => 'Khác']
    ];
}

// Lấy sản phẩm nổi bật từ database
try {
    // Kiểm tra xem cột featured có tồn tại trong bảng products hay không
    $checkFeaturedColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'featured'");
    
    if ($checkFeaturedColumn && $checkFeaturedColumn->num_rows > 0) {
        // Nếu có cột featured, ưu tiên lấy sản phẩm nổi bật
        $sql = "SELECT * FROM products WHERE featured = 1 AND active = 1 ORDER BY id ASC LIMIT 3";
        $result = $conn->query($sql);
        
        // Nếu không có sản phẩm nổi bật, lấy tất cả sản phẩm
        if ($result && $result->num_rows == 0) {
            $sql = "SELECT * FROM products WHERE active = 1 ORDER BY id ASC LIMIT 3";
            $result = $conn->query($sql);
        }
    } else {
        // Nếu không có cột featured, lấy tất cả sản phẩm
        $sql = "SELECT * FROM products WHERE active = 1 ORDER BY id ASC LIMIT 3";
        $result = $conn->query($sql);
    }
    
    $featured_products = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $featured_products[] = $row;
        }
    }
} catch (Exception $e) {
    // Nếu có lỗi khi truy vấn bảng products
    $featured_products = [];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap"
        rel="stylesheet">
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

    h1,
    h2 {
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
        display: inline-block;
        margin: 10px;
    }

    .btn:hover {
        background-color: #8b4513;
        transform: scale(1.05);
    }

    .hero {
        max-width: 1200px;
        margin: 0 auto;
        padding: 50px 20px;
        text-align: center;
        background-image: url('https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=1500&q=80');
        background-size: cover;
        background-position: center;
        color: white;
        border-radius: 10px;
        margin-bottom: 50px;
        position: relative;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 10px;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        padding: 100px 20px;
    }

    .hero h1 {
        font-size: 3em;
        color: white;
        margin-bottom: 20px;
    }

    .hero p {
        font-size: 1.2em;
        margin-bottom: 30px;
    }

    .featured {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        text-align: center;
    }

    .featured-products {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .featured-products.three-products {
        grid-template-columns: repeat(3, 1fr);
        max-width: 1000px;
        margin: 30px auto;
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

    .product-card:hover {
        transform: scale(1.05);
    }

    .product-card img {
        width: 100%;
        border-radius: 5px;
        height: 200px;
        object-fit: cover;
        cursor: pointer;
    }

    .product-card h3 {
        margin: 15px 0;
        color: #3c2f2f;
        cursor: pointer;
    }

    .product-card h3:hover {
        color: #d4a373;
    }

    .product-card p {
        color: #555;
        margin-bottom: 15px;
    }

    .product-category {
        display: inline-block;
        background-color: #3c2f2f;
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8em;
        margin-bottom: 10px;
    }

    footer {
        background-color: #3c2f2f;
        color: #fff;
        padding: 40px 0 20px;
        margin-top: 60px;
    }

    .footer-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-section {
        flex: 1;
        min-width: 250px;
        margin-bottom: 25px;
        padding: 0 15px;
    }

    .footer-section h3 {
        color: #d4a373;
        margin-bottom: 20px;
        font-size: 18px;
        position: relative;
        padding-bottom: 10px;
    }

    .footer-section h3:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 2px;
        background-color: #d4a373;
    }

    .footer-section p {
        margin-bottom: 10px;
        font-size: 14px;
        line-height: 1.6;
    }

    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 15px;
    }

    .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: #fff;
        transition: all 0.3s;
    }

    .social-links a:hover {
        background-color: #d4a373;
        transform: translateY(-3px);
    }

    .newsletter-form {
        display: flex;
        margin-top: 15px;
    }

    .newsletter-form input {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 4px 0 0 4px;
    }

    .newsletter-form button {
        padding: 10px 15px;
        background-color: #d4a373;
        color: white;
        border: none;
        border-radius: 0 4px 4px 0;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .newsletter-form button:hover {
        background-color: #c1864a;
    }

    .footer-bottom {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 30px;
        font-size: 13px;
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
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
        border-radius: 4px;
        margin-top: -2px;
        padding-top: 10px;
    }

    .dropdown-content a {
        color: white;
        font-size: 16px;
        padding: 12px 18px;
        text-decoration: none;
        display: block;
    }
    
    .dropdown-content a:hover {
        background-color: #5a4b4b;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .category-section {
        max-width: 1200px;
        margin: 50px auto;
        padding: 20px;
        text-align: center;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .category-card {
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        height: 250px;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .category-card:hover {
        transform: scale(1.05);
    }

    .category-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .category-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .category-title {
        color: white;
        font-size: 1.5em;
        font-family: 'Playfair Display', serif;
    }

    @media (max-width: 768px) {
        nav {
            flex-direction: column;
            padding: 10px;
        }

        .nav-links {
            flex-direction: column;
            margin-top: 15px;
        }

        nav a {
            margin: 8px 0;
        }

        .hero h1 {
            font-size: 2em;
        }

        .hero-content {
            padding: 50px 20px;
        }

        .featured-products,
        .category-grid {
            grid-template-columns: 1fr;
        }

        .featured-products.three-products {
            grid-template-columns: 1fr;
        }

        .footer-container {
            flex-direction: column;
        }

        .footer-section {
            margin-bottom: 30px;
        }
    }

    .nav-user-icon {
        padding: 5px 10px;
        font-size: 22px;
        color: #fff;
        border-radius: 50%;
        background: #d4a373;
        transition: background 0.3s;
    }

    .nav-user-icon:hover {
        background: #c49666;
        color: #fff;
    }
    </style>
</head>

<body>
    <header>
        <nav>
            <div class="logo">
    <a href="index.php" style="display: flex; align-items: center; text-decoration: none;">
        <img src="https://res.cloudinary.com/dczuwkvok/image/upload/v1747069387/Brown_Beige_Modern_Coffee_Logo_xeceb8.png" alt="LOGO" width="70" height="70" style="border-radius: 50%; margin-right: 10px;">
        <span style="font-family: 'Playfair Display', serif; font-size: 1em; color: white; font-weight: bold;">Cà Phê Đậm Đà</span>
    </a>
            </div>
            <div class="nav-links">
                <a href="index.php">Trang chủ</a>
                <div class="dropdown">
                    <a href="products.php">Sản phẩm</a>
                    <div class="dropdown-content">
                        <a href="products.php">Tất cả</a>
                        <?php foreach($categories as $category): ?>
                        <a
                            href="products.php?category=<?php echo $category['id']; ?>"><?php echo $category['name']; ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="about.php">Giới thiệu</a>
                <a href="cart.php">Giỏ hàng</a>
                <?php
                if(isset($_SESSION['user_id'])) {
                    echo '<div class="dropdown">
                        <a href="profile.php" class="nav-user-icon"><i class="fas fa-user nav-icon"></i> ' . htmlspecialchars($_SESSION['fullname']) . '</a>
                        <div class="dropdown-content">
                            <a href="profile.php"><i class="fas fa-user-circle"></i> Thông tin tài khoản</a>
                            <a href="address-book.php"><i class="fas fa-address-book"></i> Sổ địa chỉ</a>
                            <a href="my-orders.php"><i class="fas fa-shopping-bag"></i> Lịch sử đơn hàng</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                        </div>
                    </div>';
                } else {
                    echo '<div class="dropdown">
                        <a href="profile.php" class="nav-user-icon"><i class="fas fa-user nav-icon"></i></a>
                        <div class="dropdown-content">
                            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                            <a href="register.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                        </div>
                    </div>';
                }
                ?>
            </div>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Cà Phê Đậm Đà</h1>
            <p>Hương vị đặc trưng từ những hạt cà phê thượng hạng</p>
            <a href="products.php" class="btn">Khám phá sản phẩm</a>
        </div>
    </section>

    <section class="category-section">
        <h2>Danh mục sản phẩm</h2>
        <div class="category-grid">
            <?php 
            // Hình ảnh mặc định cho các danh mục
            $default_images = [
                1 => 'https://lh6.googleusercontent.com/proxy/ULqvKQ2UCFsMhYAqAbJE1VXiCR4I6IDe6dtj5t5h7qBXzhy4bqhlzOC3FlzOXHrOcvWBb_oiCQRi0U4ZXBOK3vA',
                2 => 'https://bizweb.dktcdn.net/thumb/1024x1024/100/512/697/products/r-bot-1719824345076.jpg?v=1719829974003',
                3 => 'https://vn-live-01.slatic.net/p/cdf5f80d6feaa2e85e10968606ea4df6.jpg',
                4 => 'https://suckhoedoisong.qltns.mediacdn.vn/324455921873985536/2024/12/29/ca-phe-2-17354890538271341325466.jpg'
            ];
            
            foreach($categories as $category): 
                $cat_id = $category['id'];
                $image = isset($category['image']) && !empty($category['image']) ? $category['image'] : (isset($default_images[$cat_id]) ? $default_images[$cat_id] : 'images/category-default.jpg');
            ?>
            <a href="products.php?category=<?php echo $cat_id; ?>" class="category-card">
                <img src="<?php echo $image; ?>" alt="<?php echo $category['name']; ?>"
                    onerror="this.src='images/category-default.jpg'">
                <div class="category-overlay">
                    <div class="category-title"><?php echo $category['name']; ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="featured" id="featured">
        <h2>Sản phẩm nổi bật</h2>
        <div class="featured-products three-products">
            <?php if (count($featured_products) > 0): ?>
            <?php foreach ($featured_products as $product): ?>
            <?php
                    // Lấy tên danh mục từ category_id nếu có
                    $category_name = "";
                    if (isset($product['category_id'])) {
                        foreach ($categories as $cat) {
                            if (isset($cat['id']) && $cat['id'] == $product['category_id']) {
                                $category_name = $cat['name'];
                                break;
                            }
                        }
                    }
                    ?>
            <div class='product-card'>
                <?php if (!empty($category_name)): ?>
                <span class='product-category'><?php echo $category_name; ?></span>
                <?php endif; ?>
                <a href='product-detail.php?id=<?php echo $product['id']; ?>'>
                    <img src='<?php echo htmlspecialchars($product['image']); ?>'
                        alt='<?php echo htmlspecialchars($product['name']); ?>'
                        onerror="this.src='images/default-product.jpg'">
                </a>
                <a href='product-detail.php?id=<?php echo $product['id']; ?>'>
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                </a>
                <p><?php echo number_format($product['price'], 0, ',', '.'); ?>
                    VNĐ<?php echo isset($product['weight']) ? ' / ' . $product['weight'] : ''; ?></p>
                <a href='add-to-cart.php?id=<?php echo $product['id']; ?>&name=<?php echo urlencode($product['name']); ?>&price=<?php echo urlencode($product['price']); ?>&image=<?php echo urlencode($product['image']); ?>&quantity=1'
                    class='btn'>Thêm vào giỏ hàng</a>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p style='text-align: center; grid-column: 1/-1;'>Chưa có sản phẩm nào.</p>
            <?php endif; ?>
        </div>
        <a href="products.php" class="btn" style="margin-top: 30px;">Xem tất cả sản phẩm</a>
    </section>

    <section class="about" id="about" style="max-width: 1200px; margin: 50px auto; padding: 20px; text-align: center;">
        <h2>Về chúng tôi</h2>
        <p style="margin: 20px 0; text-align: justify; padding: 0 20px;">
            Cà Phê Đậm Đà là thương hiệu cà phê Việt Nam được thành lập từ năm 2010. Chúng tôi tự hào là đơn vị chuyên
            cung cấp các loại cà phê nguyên chất với chất lượng cao nhất. Từ những hạt cà phê được chọn lọc kỹ càng đến
            quy trình rang xay đặc biệt, chúng tôi cam kết mang đến cho khách hàng trải nghiệm thưởng thức cà phê tuyệt
            vời nhất.
        </p>
        <p style="margin: 20px 0; text-align: justify; padding: 0 20px;">
            Chúng tôi trực tiếp hợp tác với các nông trại cà phê tại Tây Nguyên, nơi sản sinh ra những hạt cà phê chất
            lượng nhất Việt Nam. Quy trình rang xay được kiểm soát nghiêm ngặt để đảm bảo giữ trọn hương vị đặc trưng
            của từng loại cà phê. Với phương châm "Từ nông trại đến tách cà phê", chúng tôi mang đến cho bạn những trải
            nghiệm cà phê đích thực và nguyên bản nhất.
        </p>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>

</html>