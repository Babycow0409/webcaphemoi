<?php
session_start();
include 'includes/db_connect.php';
$page_title = "Giỏ hàng";

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
    
    // Lấy giỏ hàng từ session hoặc tạo mới nếu chưa có
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    
    if($action == 'remove') {
        // Ghi log trước khi xóa sản phẩm
        file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Removing product ID: ' . $id . " from cart\n", FILE_APPEND);
        file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Cart before removal: ' . print_r($cart, true) . "\n", FILE_APPEND);
        
        // Xóa sản phẩm từ giỏ hàng
        foreach($cart as $key => $item) {
            $itemId = isset($item['id']) ? (int)$item['id'] : 0;
            if($itemId === $id) {
                unset($cart[$key]);
                file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Removed product ID: ' . $id . " from cart\n", FILE_APPEND);
                break;
            }
        }
        // Cập nhật lại session với mảng đã xếp lại chỉ số
        $_SESSION['cart'] = array_values($cart);
        
        file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Cart after removal: ' . print_r($_SESSION['cart'], true) . "\n", FILE_APPEND);
        
        // Cập nhật lại localStorage và chuyển hướng
        echo '<script>
            localStorage.setItem("cart", JSON.stringify('.json_encode($_SESSION['cart']).'));
            window.location.href = "cart.php";
        </script>';
        exit;
    }
    else if($action == 'update' && isset($_GET['quantity'])) {
        $quantity = (int)$_GET['quantity'];
        if($quantity > 0) {
            // Ghi log trước khi cập nhật số lượng
            file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Updating product ID: ' . $id . ' quantity to: ' . $quantity . "\n", FILE_APPEND);
            file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Cart before update: ' . print_r($cart, true) . "\n", FILE_APPEND);
            
            // Cập nhật số lượng sản phẩm
            $updated = false;
            foreach($cart as $key => $item) {
                $itemId = isset($item['id']) ? (int)$item['id'] : 0;
                if($itemId === $id) {
                    $cart[$key]['quantity'] = $quantity;
                    $updated = true;
                    file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Updated product ID: ' . $id . ' quantity to: ' . $quantity . "\n", FILE_APPEND);
                    break;
                }
            }
            
            if (!$updated) {
                file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Could not find product ID: ' . $id . ' in cart for update' . "\n", FILE_APPEND);
            }
            
            // Cập nhật session
            $_SESSION['cart'] = array_values($cart);
            
            file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Cart after update: ' . print_r($_SESSION['cart'], true) . "\n", FILE_APPEND);
            
            // Cập nhật localStorage và chuyển hướng
            echo '<script>
                localStorage.setItem("cart", JSON.stringify('.json_encode($_SESSION['cart']).'));
                window.location.href = "cart.php";
            </script>';
            exit;
        }
    }
}

// Lấy giỏ hàng từ session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

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

// Xử lý đồng bộ từ localStorage
if(isset($_POST['sync_cart'])) {
    $cartData = $_POST['sync_cart'];
    $cartArray = json_decode($cartData, true);
    
    // Log dữ liệu nhận được
    file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Sync cart data: ' . $cartData . "\n", FILE_APPEND);
    
    if(is_array($cartArray) && !empty($cartArray)) {
        // Đảm bảo dữ liệu hợp lệ
        $validCart = [];
        foreach($cartArray as $item) {
            if(isset($item['id'], $item['name'], $item['price'])) {
                // Kiểm tra số lượng hợp lệ
                if(!isset($item['quantity']) || $item['quantity'] < 1) {
                    $item['quantity'] = 1;
                }
                $validCart[] = $item;
            }
        }
        
        // Lưu vào session
        $_SESSION['cart'] = array_values($validCart);
        file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Synced cart count: ' . count($_SESSION['cart']) . "\n", FILE_APPEND);
        echo "OK";
    }
    exit;
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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400&display=swap" rel="stylesheet">
    <style>
        /* Sử dụng cùng style với trang chủ */
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
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        
        .cart-table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            background-color: #f9f9f9;
            padding: 5px;
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
            margin: 0 8px;
            border: 1px solid #d4a373;
            border-radius: 5px;
        }
        
        .remove-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .remove-btn:hover {
            background-color: #c82333;
        }
        
        .cart-summary {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .cart-summary h3 {
            font-family: 'Playfair Display', serif;
            color: #3c2f2f;
            margin-bottom: 20px;
            border-bottom: 2px solid #d4a373;
            padding-bottom: 10px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-row:last-child {
            font-weight: bold;
            font-size: 1.2em;
            border-top: 2px solid #d4a373;
            border-bottom: none;
            padding-top: 15px;
        }
        
        .checkout-btn {
            background-color: #28a745;
            width: 100%;
            padding: 15px;
            font-size: 1.1em;
            border-radius: 5px;
        }
        
        .checkout-btn:hover {
            background-color: #218838;
        }
        
        .continue-shopping {
            display: block;
            text-align: center;
            color: #3c2f2f;
            margin-top: 20px;
            text-decoration: none;
            font-weight: bold;
        }
        
        .continue-shopping:hover {
            color: #d4a373;
        }
        
        @media (max-width: 768px) {
            .cart-table {
                display: block;
                overflow-x: auto;
            }
            
            .cart-table th,
            .cart-table td {
                padding: 10px;
            }
            
            .cart-table img {
                width: 60px;
                height: 60px;
            }
            
            .quantity-btn {
                width: 25px;
                height: 25px;
                font-size: 14px;
            }
            
            .quantity-input {
                width: 40px;
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
        
        /* Footer styles */
        footer {
            background-color: #3c2f2f;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
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

    <div class="cart-container">
        <h1>Giỏ hàng của bạn</h1>
        
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
            // Kiểm tra giỏ hàng trống
            if(empty($cart)) {
                echo '<div class="cart-empty">
                    <i>🛒</i>
                    <h3>Giỏ hàng của bạn đang trống</h3>
                    <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
                    <a href="products.php" class="btn">Tiếp tục mua sắm</a>
                </div>';
            } else {
                // Hiển thị thông tin debug
                echo '<!-- Số lượng sản phẩm trong giỏ: ' . count($cart) . ' -->';
                
                echo '<table class="cart-table">
                    <thead>
                        <tr>
                            <th>SẢN PHẨM</th>
                            <th>ĐƠN GIÁ</th>
                            <th>SỐ LƯỢNG</th>
                            <th>THÀNH TIỀN</th>
                            <th>THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                $total_items = 0;
                
                // Hiển thị từng sản phẩm
                foreach($cart as $index => $item) {
                    // Kiểm tra sản phẩm hợp lệ
                    if(!isset($item['id']) || !isset($item['name']) || !isset($item['price'])) {
                        continue;
                    }
                    
                    $id = htmlspecialchars($item['id']);
                    $name = htmlspecialchars($item['name']);
                    $price = (int)$item['price'];
                    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                    $itemTotal = $price * $quantity;
                    $total_items++;
                    
                    // Xử lý đường dẫn ảnh
                    $imageSrc = isset($item['image']) && !empty($item['image']) ? htmlspecialchars($item['image']) : 'images/default-product.jpg';
                    
                    echo '<!-- Hiển thị sản phẩm #' . $index . ' - ' . $id . ' -->';
                    echo '<tr>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <div style="width: 80px; height: 80px; background-color: #f8f9fa; border-radius: 5px; overflow: hidden; margin-right: 15px; position: relative;">
                                    <img src="'.$imageSrc.'" alt="'.$name.'" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;"
                                         onerror="this.onerror=null; this.src=\'images/default-product.jpg\';">
                                </div>
                                <span>'.$name.'</span>
                            </div>
                        </td>
                        <td>'.number_format($price, 0, ',', '.').' VNĐ</td>
                        <td>
                            <div class="quantity-control">
                                <a href="javascript:void(0)" onclick="updateQuantity(\''.$id.'\', '.($quantity > 1 ? $quantity - 1 : 1).')" class="quantity-btn" style="display:inline-block; text-align:center; line-height:30px; text-decoration:none;">-</a>
                                <span class="quantity-input">'.$quantity.'</span>
                                <a href="javascript:void(0)" onclick="updateQuantity(\''.$id.'\', '.($quantity + 1).')" class="quantity-btn" style="display:inline-block; text-align:center; line-height:30px; text-decoration:none;">+</a>
                            </div>
                        </td>
                        <td>'.number_format($itemTotal, 0, ',', '.').' VNĐ</td>
                        <td>
                            <a href="cart.php?action=remove&id='.$id.'" class="remove-btn" onclick="return confirm(\'Bạn có chắc muốn xóa sản phẩm này?\')">Xóa</a>
                        </td>
                    </tr>';
                }
                
                echo '</tbody>
                </table>';
                
                // Tính tổng tiền và hiển thị nút thanh toán
                if(count($cart) > 0) {
                    $total = array_reduce($cart, function($carry, $item) {
                        return $carry + $item['price'] * $item['quantity'];
                    }, 0);
                ?>
                    <div class="cart-summary">
                        <h2>Tổng đơn hàng (Số lượng sản phẩm: <?= count($cart) ?>)</h2>
                        <hr>
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?= number_format($total, 0, '.', '.') ?> VND</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span>Miễn phí</span>
                        </div>
                        <div class="summary-row total">
                            <span>Tổng cộng:</span>
                            <span><?= number_format($total, 0, '.', '.') ?> VND</span>
                        </div>
                        
                        <a href="checkout.php" class="checkout-btn">Tiến hành thanh toán</a>
                        
                        <div class="continue-shopping">
                            <a href="products.php">← Tiếp tục mua sắm</a>
                            <a href="cart.php?reset=1" class="clear-cart">Xóa toàn bộ giỏ hàng</a>
                        </div>
                    </div>
                <?php
                } else {
                    echo '<div class="empty-cart">
                        <h2>Giỏ hàng của bạn đang trống</h2>
                        <p>Hãy thêm sản phẩm vào giỏ hàng để mua sắm.</p>
                        <a href="products.php" class="btn">Tiếp tục mua sắm</a>
                    </div>';
                }
            }
            ?>
        </div>
    </div>

    <footer id="contact">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <h2 style="color: white;">Liên hệ</h2>
            <p style="margin: 20px 0;">
                Địa chỉ: 123 Đường Nguyễn Huệ, Quận 1, TP.HCM<br>
                Email: info@caphedamda.com<br>
                Điện thoại: 0909 123 456
            </p>
            <div style="margin: 20px 0;">
                <a href="#" style="color: #d4a373; margin: 0 10px;">Facebook</a>
                <a href="#" style="color: #d4a373; margin: 0 10px;">Instagram</a>
                <a href="#" style="color: #d4a373; margin: 0 10px;">Twitter</a>
            </div>
            <p style="margin-top: 20px; font-size: 0.9em;">
                © 2023 Cà Phê Đậm Đà. Tất cả các quyền được bảo lưu.
            </p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Đồng bộ từ session vào localStorage
        var sessionCart = <?php echo json_encode($cart); ?>;
        localStorage.setItem('cart', JSON.stringify(sessionCart));
        
        // Tính tổng tiền
        var totalAmount = 0;
        sessionCart.forEach(function(item) {
            totalAmount += (item.price * item.quantity);
        });
        
        // Hiển thị debug info trong console
        console.log('Giỏ hàng (session):', sessionCart);
        console.log('Giỏ hàng (localStorage):', JSON.parse(localStorage.getItem('cart') || '[]'));
        console.log('Tổng tiền:', totalAmount);
        console.log('Số lượng sản phẩm:', sessionCart.length);
        
        // Cập nhật số lượng trong badge
        const cartCountElement = document.getElementById('cartCount');
        if (cartCountElement) {
            cartCountElement.textContent = sessionCart.length;
        }
        
        // Kiểm tra chi tiết từng sản phẩm
        if (sessionCart.length > 0) {
            console.log('Chi tiết sản phẩm trong giỏ hàng:');
            sessionCart.forEach(function(item, index) {
                console.log(`Sản phẩm ${index + 1}:`, item);
            });
        }
        
        // Hiển thị thông báo nếu có sự không đồng bộ giữa localStorage và session
        var localCart = JSON.parse(localStorage.getItem('cart') || '[]');
        if (JSON.stringify(localCart) !== JSON.stringify(sessionCart)) {
            console.warn('Phát hiện sự không đồng bộ giữa localStorage và session, đang đồng bộ lại...');
            localStorage.setItem('cart', JSON.stringify(sessionCart));
        }
    });
    
    // Hàm cập nhật số lượng sản phẩm
    function updateQuantity(id, quantity) {
        if (quantity <= 0) return;
        
        // Chuyển hướng đến URL cập nhật
        window.location.href = 'cart.php?action=update&id=' + id + '&quantity=' + quantity;
    }
    </script>

    <!-- Debug: Hiển thị thông tin giỏ hàng -->
    <div style="margin-top: 50px; padding: 20px; background: #f5f5f5; border-radius: 5px;">
        <h3>Debug: Thông tin giỏ hàng (<?= count($cart) ?> sản phẩm)</h3>
        <pre style="background: #fff; padding: 10px; border-radius: 5px; overflow: auto; max-height: 300px;">
    <?php 
    $cart_debug = [];
    foreach($cart as $item) {
        $cart_debug[] = [
            'id' => $item['id'] . ' (' . gettype($item['id']) . ')',
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'image' => $item['image']
        ];
    }
    print_r($cart_debug); 
    ?>
        </pre>
    </div>
</body>
</html> 