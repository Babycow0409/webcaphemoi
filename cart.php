<?php
session_start();
include 'includes/db_connect.php';
require_once 'includes/cart_functions.php';
$page_title = "Giỏ hàng";

// Lấy giỏ hàng từ session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Kiểm tra và lọc bỏ các sản phẩm đã bị xóa
$removedProducts = validateCartProducts($cart, $conn);

// Kiểm tra và điều chỉnh số lượng dựa trên tồn kho
$adjustedProducts = validateCartStock($cart, $conn);

// Hiển thị thông báo nếu có sản phẩm bị xóa
if(!empty($removedProducts)) {
    $message = "Một số sản phẩm đã bị xóa khỏi giỏ hàng vì không còn tồn tại: " . implode(", ", $removedProducts);
    // Thay vì dùng alert, lưu thông báo này vào session để hiển thị đẹp hơn
    $_SESSION['removed_products_message'] = $message;
    
    // Cập nhật lại session và localStorage
    $_SESSION['cart'] = $cart;
    echo "<script>localStorage.setItem('cart', JSON.stringify(" . json_encode($cart) . "));</script>";
}

// Hiển thị thông báo nếu có sản phẩm bị điều chỉnh số lượng
if(!empty($adjustedProducts)) {
    $messages = [];
    foreach($adjustedProducts as $product) {
        if($product['available'] <= 0) {
            $messages[] = "Sản phẩm '{$product['name']}' đã hết hàng và đã được xóa khỏi giỏ hàng.";
        } else {
            $messages[] = "Số lượng sản phẩm '{$product['name']}' đã được điều chỉnh từ {$product['requested']} về {$product['available']} do tồn kho không đủ.";
        }
    }
    
    $message = implode("<br>", $messages);
    // Lưu thông báo vào session để hiển thị đẹp hơn
    $_SESSION['adjusted_products_message'] = $message;
    
    // Cập nhật lại session và localStorage
    $_SESSION['cart'] = $cart;
    echo "<script>localStorage.setItem('cart', JSON.stringify(" . json_encode($cart) . "));</script>";
}

// Sửa lỗi đường dẫn ảnh
if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as &$item) {
        // Kiểm tra và sửa đường dẫn ảnh không hợp lệ
        if(!isset($item['image']) || empty($item['image']) || !file_exists($item['image'])) {
            // Trước tiên, kiểm tra xem sản phẩm có tồn tại trong cơ sở dữ liệu không
            if(isset($item['id'])) {
                // Truy vấn cơ sở dữ liệu để lấy thông tin sản phẩm
                $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
                $stmt->bind_param("i", $item['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result && $result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $imagePath = $product['image'];
                    
                    // Kiểm tra xem đường dẫn hình ảnh có cần thêm tiền tố 'uploads/products/' không
                    if(!empty($imagePath)) {
                        if(strpos($imagePath, 'uploads/') === false && strpos($imagePath, 'images/') === false) {
                            $imagePath = 'uploads/products/' . $imagePath;
                        }
                        $item['image'] = $imagePath;
                    }
                }
            }
            
            // Nếu vẫn không có hình ảnh hợp lệ, sử dụng hình ảnh mặc định dựa trên tên sản phẩm
            if(!isset($item['image']) || empty($item['image']) || !file_exists($item['image'])) {
                if(isset($item['name'])) {
                    $name = strtolower($item['name']);
                    if(strpos($name, 'arabica') !== false) {
                        if(strpos($name, 'cầu đất') !== false || strpos($name, 'caudat') !== false) {
                            $item['image'] = 'images/arabica-caudat.jpg';
                        } else {
                            $item['image'] = 'images/arabica.jpg';
                        }
                    } else if(strpos($name, 'robusta') !== false) {
                        if(strpos($name, 'đắk lắk') !== false || strpos($name, 'daklak') !== false) {
                            $item['image'] = 'images/robusta-daklak.jpg';
                        } else if(strpos($name, 'ấn độ') !== false || strpos($name, 'india') !== false) {
                            $item['image'] = 'images/robusta-india.jpg';
                        } else {
                            $item['image'] = 'images/robusta.jpg';
                        }
                    } else if(strpos($name, 'chồn') !== false || strpos($name, 'chon') !== false) {
                        $item['image'] = 'images/coffee-chon.jpg';
                    } else {
                        $item['image'] = 'images/default-product.jpg';
                    }
                } else {
                    $item['image'] = 'images/default-product.jpg';
                }
            }
        }
    }
    // Lưu lại giỏ hàng đã được sửa
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// XÓA TOÀN BỘ GIỎ HÀNG ĐỂ LÀM LẠI
if(isset($_GET['reset'])) {
    // Xóa hoàn toàn giỏ hàng từ session
    unset($_SESSION['cart']);
    // Xóa localStorage thông qua JavaScript
    echo '<script>localStorage.removeItem("cart"); window.location.href = "products.php";</script>';
    exit;
}

// XỬ LÝ CÁC THAO TÁC VỚI GIỎ HÀNG
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    if($action == 'remove') {
        $_SESSION['cart'] = removeFromCart($cart, $id);
        echo '<script>
            localStorage.setItem("cart", JSON.stringify('.json_encode($_SESSION['cart']).'));
            window.location.href = "cart.php";
        </script>';
        exit;
    }
    else if($action == 'update' && isset($_GET['quantity'])) {
        $quantity = (int)$_GET['quantity'];
        try {
            $result = updateCartItemQuantity($cart, $id, $quantity, $conn);
            $_SESSION['cart'] = $result['cart'];
            
            // Hiển thị thông báo nếu có
            if(!empty($result['message'])) {
                echo "<script>alert('" . addslashes($result['message']) . "');</script>";
            }
        } catch (Exception $e) {
            // Có thể xử lý lỗi nếu cần
        }
        echo '<script>
            localStorage.setItem("cart", JSON.stringify('.json_encode($_SESSION['cart']).'));
            window.location.href = "cart.php";
        </script>';
        exit;
    }
}

// Xử lý đồng bộ từ localStorage
if(isset($_POST['sync_cart'])) {
    $cartData = $_POST['sync_cart'];
    $cartArray = json_decode($cartData, true);
    
    // Log dữ liệu nhận được
    file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Sync cart data: ' . $cartData . "\n", FILE_APPEND);
    
    if(is_array($cartArray) && !empty($cartArray)) {
        // Đảm bảo dữ liệu hợp lệ và hợp nhất các sản phẩm trùng ID
        $validCart = [];
        foreach($cartArray as $item) {
            if(isset($item['id'], $item['name'], $item['price'])) {
                // Kiểm tra số lượng hợp lệ
                if(!isset($item['quantity']) || $item['quantity'] < 1) {
                    $item['quantity'] = 1;
                }
                
                // Đảm bảo ID là số nguyên
                $item['id'] = (int)$item['id'];
                
                // Kiểm tra xem sản phẩm có tồn tại trong database không
                $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
                $stmt->bind_param("i", $item['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result && $result->num_rows > 0) {
                    // Kiểm tra xem sản phẩm đã có trong giỏ hàng mới chưa
                    $found = false;
                    foreach($validCart as $key => $validItem) {
                        if($validItem['id'] == $item['id']) {
                            // Hợp nhất số lượng
                            $validCart[$key]['quantity'] += $item['quantity'];
                            $found = true;
                            break;
                        }
                    }
                    
                    // Nếu không tìm thấy, thêm mới
                    if(!$found) {
                        $validCart[] = $item;
                    }
                }
            }
        }
        
        // Lưu vào session
        $_SESSION['cart'] = array_values($validCart);
        file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Synced cart count: ' . count($_SESSION['cart']) . "\n", FILE_APPEND);
        echo "OK";
    }
    exit;
}

// Hợp nhất các sản phẩm trùng ID trong giỏ hàng
function consolidateCart(&$cart) {
    if(!is_array($cart) || empty($cart)) {
        return;
    }
    
    $mergedCart = [];
    foreach($cart as $item) {
        if(!isset($item['id'])) continue;
        
        $id = (int)$item['id'];
        $found = false;
        
        foreach($mergedCart as $key => $mergedItem) {
            if((int)$mergedItem['id'] == $id) {
                $mergedCart[$key]['quantity'] += isset($item['quantity']) ? (int)$item['quantity'] : 1;
                $found = true;
                break;
            }
        }
        
        if(!$found) {
            $item['id'] = $id; // Đảm bảo ID là số nguyên
            $item['quantity'] = isset($item['quantity']) ? (int)$item['quantity'] : 1;
            $mergedCart[] = $item;
        }
    }
    
    $cart = array_values($mergedCart);
    return;
}

// Hợp nhất các sản phẩm trùng ID
consolidateCart($cart);
$_SESSION['cart'] = $cart;

// Nếu không có sản phẩm trong giỏ hàng nhưng có dữ liệu trong localStorage
if(empty($cart)) {
    echo '
    <script>
    window.onload = function() {
        var cartData = localStorage.getItem("cart");
        if(cartData) {
            try {
                var cart = JSON.parse(cartData);
                if(cart && cart.length > 0) {
                    // Có dữ liệu trong localStorage, gửi AJAX để đồng bộ
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "cart.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if(xhr.readyState === 4 && xhr.status === 200) {
                            console.log("Đã đồng bộ giỏ hàng từ localStorage:", cart.length, "sản phẩm");
                            window.location.reload();
                        }
                    };
                    xhr.send("sync_cart=" + encodeURIComponent(cartData));
                }
            } catch(e) {
                console.error("Lỗi khi đọc giỏ hàng:", e);
            }
        }
    };
    </script>';
} else {
    // Nếu đã có giỏ hàng trong session, đảm bảo localStorage cũng được cập nhật
    echo '<script>
    window.onload = function() {
        var sessionCart = ' . json_encode($cart) . ';
        localStorage.setItem("cart", JSON.stringify(sessionCart));
        console.log("Đã đồng bộ giỏ hàng từ session vào localStorage:", sessionCart.length, "sản phẩm");
        
        // Cập nhật số lượng sản phẩm trên biểu tượng giỏ hàng
        const cartCountElement = document.getElementById("cartCount");
        if (cartCountElement) {
            cartCountElement.textContent = sessionCart.length;
        }
    };
    </script>';
}

// TÍNH TỔNG TIỀN
$totalAmount = 0;
foreach($cart as $item) {
    if(isset($item['price']) && isset($item['quantity'])) {
        $totalAmount += $item['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Cà Phê Đậm Đà</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    /* Sử dụng cùng style với trang chủ */
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

    /* Style riêng cho giỏ hàng */
    .cart-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .cart-empty {
        text-align: center;
        padding: 50px 20px;
        background-color: #f9f9f9;
        border-radius: 10px;
        margin: 30px 0;
    }

    .cart-empty i {
        font-size: 50px;
        color: #d4a373;
        margin-bottom: 20px;
    }

    .cart-table-container {
        overflow-x: auto;
        margin-bottom: 30px;
    }

    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin: 30px 0;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }

    .cart-table th,
    .cart-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    .cart-table th {
        background-color: #3c2f2f;
        color: white;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.9em;
    }

    .cart-table tr:hover {
        background-color: #f9f9f9;
    }

    .product-info {
        display: flex;
        align-items: center;
    }

    .product-info img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
        background-color: #f9f9f9;
        padding: 5px;
        margin-right: 15px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quantity-btn {
        width: 30px;
        height: 30px;
        background-color: #d4a373;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s;
    }

    .quantity-btn:hover {
        background-color: #8b4513;
    }

    .quantity-input {
        width: 50px;
        height: 35px;
        text-align: center;
        margin: 0 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .remove-btn {
        color: #3c2f2f;
        background: none;
        border: none;
        padding: 8px 12px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
        text-decoration: underline;
    }

    .remove-btn:hover {
        color: #d4a373;
    }

    /* Cart summary styling */
    .cart-summary-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin-top: 30px;
        gap: 20px;
    }

    .cart-summary-box {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        flex: 1;
        min-width: 300px;
        max-width: 400px;
    }

    .cart-summary-box h3 {
        font-family: 'Playfair Display', serif;
        color: #3c2f2f;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        margin: 10px 0;
        padding: 5px 0;
    }

    .summary-line.total {
        font-weight: bold;
        font-size: 1.2em;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
        color: #3c2f2f;
    }

    .checkout-btn {
        display: block;
        width: 100%;
        padding: 12px;
        background-color: #28a745;
        color: white;
        text-align: center;
        text-decoration: none;
        border-radius: 8px;
        margin-top: 20px;
        font-weight: bold;
        transition: all 0.3s;
    }

    .checkout-btn:hover {
        background-color: #218838;
        transform: translateY(-2px);
    }

    .cart-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        flex: 1;
        min-width: 300px;
        align-items: flex-start;
    }

    .btn-secondary {
        background-color: #f1f1f1;
        color: #333;
        border: 1px solid #ddd;
        margin: 5px;
        flex: 1;
    }

    .btn-secondary:hover {
        background-color: #e2e2e2;
    }

    .btn-clear {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }

    .btn-clear:hover {
        background-color: #f1b0b7;
    }

    @media (max-width: 768px) {
        .cart-summary-container {
            flex-direction: column;
        }

        .cart-summary-box,
        .cart-actions {
            max-width: 100%;
        }
    }

    /* Dropdown menu style */
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
        font-size: 16px;
        padding: 12px 18px;
        text-decoration: none;
        display: block;
    }
    
    .dropdown-content a:hover {
        background-color: #5a4b4b;
    }
    
    .dropdown-content i {
        margin-right: 8px;
        color: #d4a373;
    }

    .nav-icon {
        margin-right: 8px;
        color: #d4a373;
    }

    /* Notification alerts styles */
    .alert-notification {
        position: relative;
        padding: 16px 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        display: flex;
        align-items: flex-start;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        0% { transform: translateY(-20px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

    .alert-warning {
        background-color: #fff3cd;
        border-left: 5px solid #ffc107;
        color: #856404;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-left: 5px solid #17a2b8;
        color: #0c5460;
    }

    .alert-notification i {
        font-size: 20px;
        margin-right: 15px;
        margin-top: 2px;
    }

    .alert-content {
        flex: 1;
    }

    .alert-content p {
        margin: 0;
        font-size: 15px;
    }

    .close-alert {
        background: none;
        border: none;
        font-size: 20px;
        color: inherit;
        opacity: 0.7;
        cursor: pointer;
        padding: 0 5px;
        transition: opacity 0.2s;
    }

    .close-alert:hover {
        opacity: 1;
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
                        <a href="arabica.php">Arabica</a>
                        <a href="robusta.php">Robusta</a>
                        <a href="chon.php">Chồn</a>
                        <a href="Khac.php">Khác</a>
                    </div>
                </div>
                <a href="about.php">Giới thiệu</a>
                <a href="cart.php">Giỏ hàng</a>
                <?php
                if(isset($_SESSION['user_id'])) {
                    // Hiển thị menu dropdown thông tin tài khoản
                    echo '<div class="dropdown">
                        <a href="profile.php" class="nav-user-icon"><i class="fas fa-user"></i> ' . htmlspecialchars($_SESSION['fullname']) . '</a>
                        <div class="dropdown-content">
                            <a href="profile.php"><i class="fas fa-user-circle"></i> Thông tin tài khoản</a>
                            <a href="address-book.php"><i class="fas fa-address-book"></i> Sổ địa chỉ</a>
                            <a href="my-orders.php"><i class="fas fa-shopping-bag"></i> Lịch sử đơn hàng</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
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

    <div class="cart-container">
        <h1>Giỏ hàng của bạn</h1>

        <?php if(isset($_SESSION['removed_products_message'])): ?>
            <div class="alert-notification alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="alert-content">
                    <p><?php echo $_SESSION['removed_products_message']; ?></p>
                </div>
                <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
            </div>
            <?php unset($_SESSION['removed_products_message']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['adjusted_products_message'])): ?>
            <div class="alert-notification alert-info">
                <i class="fas fa-info-circle"></i>
                <div class="alert-content">
                    <p><?php echo $_SESSION['adjusted_products_message']; ?></p>
                </div>
                <button class="close-alert" onclick="this.parentElement.style.display='none';">&times;</button>
            </div>
            <?php unset($_SESSION['adjusted_products_message']); ?>
        <?php endif; ?>

        <?php
        // Kiểm tra xem có hình ảnh nào bị thiếu không
        $missing_images = false;
        if(!empty($cart)) {
            foreach($cart as $item) {
                if(isset($item['image']) && !file_exists($item['image'])) {
                    $missing_images = true;
                    break;
                }
            }
        }
        
        // Hiển thị thông báo nếu có hình ảnh bị thiếu
        if($missing_images) {
            echo '<div style="background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 10px; margin-bottom: 20px; border-radius: 5px;">
                <h4 style="margin-top: 0;">Lưu ý: Một số hình ảnh sản phẩm không hiển thị được</h4>
                <p>Để xem hình ảnh sản phẩm đầy đủ, vui lòng <a href="download-images.php" style="color: #856404; font-weight: bold;">nhấn vào đây</a> để tải hình ảnh.</p>
            </div>';
        }
        ?>

        <div id="cart-content">
            <?php
            // Nếu không có sản phẩm trong giỏ hàng
            if(empty($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
                echo '<div class="cart-empty">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Giỏ hàng của bạn đang trống</h2>
                        <p>Quay lại cửa hàng và thêm sản phẩm vào giỏ hàng của bạn.</p>
                        <a href="products.php" class="btn">Tiếp tục mua sắm</a>
                      </div>';
            } else {
                // Nếu có sản phẩm trong giỏ hàng, hiển thị danh sách
                echo '<div class="cart-table-container">';
                echo '<table class="cart-table">';
                echo '<thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Thao tác</th>
                        </tr>
                      </thead>';
                echo '<tbody>';

                // Debug: Hiển thị thông tin chi tiết về giỏ hàng
                file_put_contents('cart_debug.txt', date('Y-m-d H:i:s') . ' - Cart in cart.php: ' . print_r($_SESSION['cart'], true) . "\n", FILE_APPEND);
                
                $totalAmount = 0;
                foreach($_SESSION['cart'] as $item) {
                    // Debug: Hiển thị thông tin chi tiết về từng sản phẩm
                    file_put_contents('cart_debug.txt', date('Y-m-d H:i:s') . ' - Processing item: ' . print_r($item, true) . "\n", FILE_APPEND);
                    
                    if(isset($item['id'], $item['name'], $item['price'])) {
                        $itemTotal = $item['price'] * $item['quantity'];
                        $totalAmount += $itemTotal;
                        
                        echo '<tr>';
                        echo '<td>
                                <div class="product-info">
                                    <img src="' . htmlspecialchars($item['image']) . '" alt="' . htmlspecialchars($item['name']) . '" onerror="this.src=\'images/default-product.jpg\'">
                                    <div>
                                        <strong>' . htmlspecialchars($item['name']) . '</strong>
                                    </div>
                                </div>
                              </td>';
                        echo '<td>' . number_format($item['price'], 0, ',', '.') . ' VNĐ</td>';
                        echo '<td>
                                <div class="quantity-control">
                                    <button class="quantity-btn" onclick="updateQuantity(\'' . $item['id'] . '\', -1)">-</button>
                                    <input type="text" value="' . $item['quantity'] . '" id="quantity-' . $item['id'] . '" readonly class="quantity-input">
                                    <button class="quantity-btn" onclick="updateQuantity(\'' . $item['id'] . '\', 1)">+</button>
                                </div>
                              </td>';
                        echo '<td class="item-total" data-id="' . $item['id'] . '" data-price="' . $item['price'] . '">' . number_format($itemTotal, 0, ',', '.') . ' VNĐ</td>';
                        echo '<td>
                                <button class="remove-btn" onclick="removeFromCart(\'' . $item['id'] . '\')">
                                    <i class="fas fa-trash"></i>
                                </button>
                              </td>';
                        echo '</tr>';
                    } else {
                        // Debug: Ghi log khi có sản phẩm không hợp lệ
                        file_put_contents('cart_debug.txt', date('Y-m-d H:i:s') . ' - Invalid item found: ' . print_r($item, true) . "\n", FILE_APPEND);
                    }
                }
                
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
                
                // Hiển thị tổng tiền và các nút hành động
                echo '<div class="cart-summary-container">';
                echo '<div class="cart-summary-box">';
                echo '<h3>Tóm tắt đơn hàng</h3>';
                
                // Tính tổng số lượng sản phẩm
                $totalQuantity = 0;
                foreach($_SESSION['cart'] as $item) {
                    $totalQuantity += $item['quantity'];
                }
                
                echo '<div class="summary-line">';
                echo '<span>Số lượng sản phẩm:</span>';
                echo '<span id="product-count">' . $totalQuantity . '</span>';
                echo '</div>';
                
                echo '<div class="summary-line">';
                echo '<span>Tạm tính:</span>';
                echo '<span id="subtotal-amount">' . number_format($totalAmount, 0, ',', '.') . ' VNĐ</span>';
                echo '</div>';
                
                echo '<div class="summary-line">';
                echo '<span>Phí vận chuyển:</span>';
                echo '<span>Miễn phí</span>';
                echo '</div>';
                
                echo '<div class="summary-line total">';
                echo '<span>Tổng cộng:</span>';
                echo '<span id="total-amount">' . number_format($totalAmount, 0, ',', '.') . ' VNĐ</span>';
                echo '</div>';
                
                echo '<a href="checkout.php" class="checkout-btn">Tiến hành thanh toán</a>';
                echo '</div>';
                
                echo '<div class="cart-actions">';
                echo '<a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Tiếp tục mua sắm</a>';
                echo '<a href="javascript:void(0)" onclick="clearCart()" class="btn btn-secondary btn-clear"><i class="fas fa-trash"></i> Xóa giỏ hàng</a>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Đồng bộ giỏ hàng từ localStorage với session
        const storedCart = localStorage.getItem('cart');

        // Ghi log để debug
        console.log("LocalStorage cart:", storedCart);
        console.log("Session cart items:",
            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>);

        // Cập nhật số lượng trong biểu tượng giỏ hàng
        updateCartCount();
    });

    // Hàm cập nhật số lượng sản phẩm trực tiếp trên trang
    function updateQuantity(id, change) {
        const quantityInput = document.getElementById('quantity-' + id);
        let currentQuantity = parseInt(quantityInput.value);
        let newQuantity = currentQuantity + change;
        
        // Đảm bảo số lượng không nhỏ hơn 1
        if (newQuantity < 1) {
            // Xác nhận xóa sản phẩm
            removeFromCart(id);
            return;
        }
        
        // Cập nhật giá trị hiển thị
        quantityInput.value = newQuantity;
        
        // Cập nhật thành tiền của sản phẩm
        const itemTotalElement = document.querySelector(`.item-total[data-id="${id}"]`);
        const itemPrice = parseFloat(itemTotalElement.getAttribute('data-price'));
        const newItemTotal = itemPrice * newQuantity;
        itemTotalElement.textContent = formatCurrency(newItemTotal) + ' VNĐ';
        
        // Cập nhật tổng tiền và cập nhật giỏ hàng
        updateTotalAmount();
        
        // Cập nhật giỏ hàng trên server (gửi AJAX request)
        // Dùng AJAX để không phải reload trang
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `process-cart.php?action=update&id=${id}&quantity=${newQuantity}&ajax=1`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Cập nhật localStorage
                        localStorage.setItem('cart', JSON.stringify(response.cart));
                        
                        // Nếu có thông báo (ví dụ: về tồn kho), hiển thị
                        if (response.message) {
                            alert(response.message);
                            // Reload trang để cập nhật số lượng nếu có điều chỉnh do tồn kho
                            window.location.reload();
                        }
                    }
                } catch (e) {
                    console.error('Lỗi khi xử lý phản hồi:', e);
                }
            }
        };
        xhr.send();
    }
    
    // Hàm cập nhật tổng tiền
    function updateTotalAmount() {
        let totalAmount = 0;
        let totalQuantity = 0;
        const itemTotalElements = document.querySelectorAll('.item-total');
        
        // Tính tổng tiền và số lượng từ tất cả các sản phẩm
        itemTotalElements.forEach(element => {
            const itemId = element.getAttribute('data-id');
            const quantityInput = document.getElementById('quantity-' + itemId);
            const quantity = parseInt(quantityInput.value);
            
            // Cộng dồn số lượng
            totalQuantity += quantity;
            
            const itemTotal = parseInt(element.textContent.replace(/[^0-9]/g, ''));
            totalAmount += itemTotal;
        });
        
        // Cập nhật hiển thị
        const productCountElement = document.getElementById('product-count');
        const subtotalElement = document.getElementById('subtotal-amount');
        const totalElement = document.getElementById('total-amount');
        
        if (productCountElement) {
            productCountElement.textContent = totalQuantity;
        }
        
        if (subtotalElement && totalElement) {
            const formattedAmount = formatCurrency(totalAmount) + ' VNĐ';
            subtotalElement.textContent = formattedAmount;
            totalElement.textContent = formattedAmount;
        }
    }
    
    // Hàm định dạng tiền tệ
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount);
    }

    // Hàm cập nhật số lượng sản phẩm (chuyển hướng trang)
    function updateCartItem(id, quantity) {
        if (quantity <= 0) {
            // Tìm tên sản phẩm để hiển thị trong thông báo xác nhận
            let productName = '';
            try {
                const cart = JSON.parse(localStorage.getItem('cart'));
                if (cart) {
                    const product = cart.find(item => item.id == id);
                    if (product) {
                        productName = product.name;
                    }
                }
            } catch (e) {
                console.error('Lỗi khi đọc giỏ hàng:', e);
            }
            
            const confirmMessage = productName 
                ? `Bạn có chắc muốn xóa sản phẩm "${productName}" khỏi giỏ hàng?` 
                : 'Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?';
                
            if (confirm(confirmMessage)) {
                window.location.href = 'cart.php?action=remove&id=' + id;
            }
            return;
        }
        window.location.href = 'cart.php?action=update&id=' + id + '&quantity=' + quantity;
    }

    function removeFromCart(id) {
        // Tìm tên sản phẩm để hiển thị trong thông báo xác nhận
        let productName = '';
        try {
            const cart = JSON.parse(localStorage.getItem('cart'));
            if (cart) {
                const product = cart.find(item => item.id == id);
                if (product) {
                    productName = product.name;
                }
            }
        } catch (e) {
            console.error('Lỗi khi đọc giỏ hàng:', e);
        }
        
        const confirmMessage = productName 
            ? `Bạn có chắc muốn xóa sản phẩm "${productName}" khỏi giỏ hàng?` 
            : 'Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?';
            
        if (confirm(confirmMessage)) {
            window.location.href = 'cart.php?action=remove&id=' + id;
        }
    }
    
    function clearCart() {
        if (confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng? Tất cả các sản phẩm sẽ bị xóa.')) {
            window.location.href = 'cart.php?reset=1';
        }
    }

    // Cập nhật số lượng hiển thị trên icon giỏ hàng
    function updateCartCount() {
        const cartCountElement = document.querySelector('.cart-count');
        if (!cartCountElement) return;

        const storedCart = localStorage.getItem('cart');
        if (storedCart) {
            try {
                const cartData = JSON.parse(storedCart);
                if (Array.isArray(cartData) && cartData.length > 0) {
                    // Đếm số lượng sản phẩm khác nhau (không phải tổng số mục)
                    const uniqueProducts = new Map();
                    cartData.forEach(item => {
                        if (item && item.id) {
                            // Đảm bảo ID là số nguyên
                            const id = parseInt(item.id);
                            uniqueProducts.set(id, true);
                        }
                    });

                    const uniqueCount = uniqueProducts.size;
                    cartCountElement.textContent = uniqueCount;
                    cartCountElement.style.display = 'flex';
                } else {
                    cartCountElement.style.display = 'none';
                }
            } catch (e) {
                console.error('Lỗi khi phân tích dữ liệu giỏ hàng:', e);
                cartCountElement.style.display = 'none';
            }
        } else {
            cartCountElement.style.display = 'none';
        }
    }

    // Hàm để hợp nhất sản phẩm trùng ID trong localStorage
    function consolidateLocalStorageCart() {
        const storedCart = localStorage.getItem('cart');
        if (!storedCart) return;

        try {
            const cartData = JSON.parse(storedCart);
            if (!Array.isArray(cartData)) return;

            // Tạo một đối tượng để hợp nhất các sản phẩm
            const mergedItems = {};

            // Lặp qua từng mục trong giỏ hàng
            cartData.forEach(item => {
                if (!item || !item.id) return;

                const id = parseInt(item.id);
                if (!mergedItems[id]) {
                    // Tạo một bản sao của item với ID là số nguyên
                    mergedItems[id] = {
                        ...item,
                        id: id,
                        quantity: parseInt(item.quantity || 1)
                    };
                } else {
                    // Cập nhật số lượng
                    mergedItems[id].quantity += parseInt(item.quantity || 1);
                }
            });

            // Chuyển đổi đối tượng hợp nhất thành mảng
            const consolidatedCart = Object.values(mergedItems);

            // Lưu mảng hợp nhất vào localStorage
            localStorage.setItem('cart', JSON.stringify(consolidatedCart));

            console.log("Đã hợp nhất giỏ hàng: ", consolidatedCart.length, "sản phẩm khác nhau");
            return consolidatedCart;
        } catch (e) {
            console.error('Lỗi khi hợp nhất giỏ hàng:', e);
            return null;
        }
    }

    // Thực hiện hợp nhất giỏ hàng khi trang tải
    document.addEventListener('DOMContentLoaded', function() {
        consolidateLocalStorageCart();
    });
    </script>


</body>

</html>