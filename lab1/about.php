<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/cart.js"></script>
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
        display: block;
        text-align: center;
        margin: 10px auto;
    }

    .btn:hover {
        background-color: #8b4513;
        transform: scale(1.05);
    }

    .products {
        max-width: 1200px;
        margin: 50px auto;
        padding: 20px;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
        padding: 20px;
        margin-bottom: 40px;
    }

    .product-card {
        background-color: #fffaf0;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .product-card:hover {
        transform: scale(1.05);
    }

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

    @media (max-width: 992px) {
        .product-grid {
            grid-template-columns: repeat(3, 1fr);
        }
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

        .product-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .product-grid {
            grid-template-columns: 1fr;
        }
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
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .dropdown-content a {
        color: white;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .filter-container {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
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

    .filter-input,
    .filter-select {
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
        gap: 10px;
        margin: 30px 0;
        flex-wrap: wrap;
    }

    .pagination a,
    .pagination span {
        color: #3c2f2f;
        padding: 12px 18px;
        text-decoration: none;
        border: 1px solid #ddd;
        border-radius: 5px;
        transition: all 0.3s;
        font-size: 16px;
        font-weight: 500;
        display: inline-block;
        margin: 4px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .pagination a.active {
        background-color: #d4a373;
        color: white;
        border-color: #d4a373;
        box-shadow: 0 2px 8px rgba(212, 163, 115, 0.4);
        transform: scale(1.05);
    }

    .pagination a:hover:not(.active) {
        background-color: #f3e3d3;
        border-color: #d4a373;
        transform: scale(1.05);
    }

    .pagination span {
        background-color: #f8f8f8;
        color: #999;
    }

    .product-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
    }

    .view-detail-btn,
    .add-to-cart-btn {
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

    .view-detail-btn:hover,
    .add-to-cart-btn:hover {
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

    .nav-user-icon {
        padding: 5px 10px;
        font-size: 22px;
        color: #fff;
        border-radius: 50%;
        background: #d4a373;
        transition: background 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        margin-right: 5px;
    }

    .nav-user-icon:hover {
        background: #c49666;
        color: #fff;
    }

    .dropdown-content {
        min-width: 170px;
        z-index: 10;
    }

    .dropdown-content a {
        font-size: 16px;
        padding: 12px 18px;
    }

    .nav-icon {
        margin-right: 8px;
        color: #d4a373;
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
                <a href="about.php">Giới thiệu</a>
                <a href="cart.php" style="position: relative;">
                    Giỏ hàng
                    <span id="cartCount"
                        style="position: absolute; top: -8px; right: -8px; background-color: #d4a373; color: white; border-radius: 50%; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px;">0</span>
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
                        <a href="profile.php">Tài khoản</a>
                        <div class="dropdown-content">
                            <a href="profile.php">Thông tin cá nhân</a>
                            <a href="my-orders.php">Đơn hàng';
                    if($has_pending_orders) {
                        echo ' <span class="order-badge">!</span>';
                    }
                    echo '</a>
                            <a href="logout.php">Đăng xuất</a>
                        </div>
                    </div>';
                } else {
                    echo '<div class="dropdown">
                        <a href="profile.php" class="nav-user-icon"><i class="fas fa-user"></i></a>
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

<div class="about-container">
    <div class="about-header">
        <h1>Về Chúng Tôi</h1>
        <p class="subtitle">Khám phá câu chuyện và tầm nhìn của Coffee Shop</p>
    </div>

    <div class="about-content">
        <section class="about-section">
            <h2>Câu Chuyện Của Chúng Tôi</h2>
            <p>Được thành lập vào năm 2010, Coffee Shop đã phát triển từ một quán cà phê nhỏ thành một thương hiệu cà
                phê được yêu thích tại Việt Nam. Chúng tôi tự hào về việc mang đến những trải nghiệm cà phê chất lượng
                cao, kết hợp giữa truyền thống và hiện đại.</p>
        </section>

        <section class="about-section">
            <h2>Tầm Nhìn & Sứ Mệnh</h2>
            <p>Tầm nhìn của chúng tôi là trở thành địa điểm cà phê hàng đầu, nơi mọi người có thể tận hưởng những khoảnh
                khắc thư giãn tuyệt vời với tách cà phê chất lượng cao.</p>
            <p>Sứ mệnh của chúng tôi là:</p>
            <ul>
                <li>Cung cấp cà phê chất lượng cao từ nguồn nguyên liệu được chọn lọc kỹ lưỡng</li>
                <li>Tạo không gian thoải mái và thân thiện cho khách hàng</li>
                <li>Đóng góp vào sự phát triển bền vững của ngành cà phê Việt Nam</li>
            </ul>
        </section>

        <section class="about-section">
            <h2>Giá Trị Cốt Lõi</h2>
            <div class="values-grid">
                <div class="value-item">
                    <i class="fas fa-heart"></i>
                    <h3>Chất Lượng</h3>
                    <p>Cam kết mang đến những sản phẩm cà phê tốt nhất</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-leaf"></i>
                    <h3>Bền Vững</h3>
                    <p>Hỗ trợ nông dân và bảo vệ môi trường</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-users"></i>
                    <h3>Cộng Đồng</h3>
                    <p>Xây dựng cộng đồng yêu cà phê</p>
                </div>
                <div class="value-item">
                    <i class="fas fa-star"></i>
                    <h3>Đổi Mới</h3>
                    <p>Không ngừng cải tiến và phát triển</p>
                </div>
            </div>
        </section>

        <section class="about-section">
            <h2>Đội Ngũ Của Chúng Tôi</h2>
            <p>Đội ngũ của chúng tôi bao gồm những chuyên gia cà phê giàu kinh nghiệm, những barista tài năng và nhân
                viên nhiệt tình, luôn sẵn sàng phục vụ khách hàng với nụ cười thân thiện.</p>
        </section>
    </div>
</div>

<style>
.about-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.about-header {
    text-align: center;
    margin-bottom: 50px;
}

.about-header h1 {
    color: #3c2f2f;
    font-size: 2.5em;
    margin-bottom: 15px;
}

.subtitle {
    color: #666;
    font-size: 1.2em;
}

.about-section {
    margin-bottom: 50px;
}

.about-section h2 {
    color: #d4a373;
    margin-bottom: 20px;
    font-size: 1.8em;
}

.about-section p {
    line-height: 1.6;
    color: #333;
    margin-bottom: 15px;
}

.about-section ul {
    list-style-type: disc;
    margin-left: 20px;
    margin-bottom: 20px;
}

.about-section li {
    margin-bottom: 10px;
    color: #333;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.value-item {
    text-align: center;
    padding: 20px;
    background-color: #f9f5f0;
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.value-item:hover {
    transform: translateY(-5px);
}

.value-item i {
    font-size: 2em;
    color: #d4a373;
    margin-bottom: 15px;
}

.value-item h3 {
    color: #3c2f2f;
    margin-bottom: 10px;
}

.value-item p {
    color: #666;
    font-size: 0.9em;
}

@media (max-width: 768px) {
    .about-header h1 {
        font-size: 2em;
    }

    .values-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>