<?php
session_start();

// Ghi log để debug
$log_file = 'cart_log.txt';
$request_data = [
    'POST' => $_POST,
    'GET' => $_GET,
    'REQUEST' => $_REQUEST
];
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Request: ' . print_r($request_data, true) . "\n", FILE_APPEND);

// Xử lý GET và POST
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $price = isset($_POST['price']) ? (int)$_POST['price'] : 0;
    $image = isset($_POST['image']) ? $_POST['image'] : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
} else {
    // Xử lý GET request
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $name = isset($_GET['name']) ? $_GET['name'] : '';
    $price = isset($_GET['price']) ? (int)$_GET['price'] : 0;
    $image = isset($_GET['image']) ? $_GET['image'] : '';
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
}

// Ghi log dữ liệu sản phẩm
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Product data: ' . 
    "ID: $id, Name: $name, Price: $price, Image: $image, Quantity: $quantity\n", FILE_APPEND);

// Kiểm tra dữ liệu hợp lệ
if(empty($id) || empty($name) || $price <= 0) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Error: Invalid product data' . "\n", FILE_APPEND);
    
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ']);
    } else {
        // Chuyển hướng nếu là GET request
        header('Location: products.php?error=invalid_data');
    }
    exit;
}

// Không kiểm tra file_exists() nữa vì có thể không chính xác với đường dẫn tương đối
// Thay vào đó, luôn áp dụng logic xử lý đường dẫn ảnh dựa trên tên sản phẩm
$lowerName = strtolower($name);

if(strpos($lowerName, 'arabica') !== false) {
    if(strpos($lowerName, 'cầu đất') !== false || strpos($lowerName, 'caudat') !== false) {
        $image = 'images/arabica-caudat.jpg';
    } else {
        $image = 'images/arabica.jpg';
    }
} else if(strpos($lowerName, 'robusta') !== false) {
    if(strpos($lowerName, 'đắk lắk') !== false || strpos($lowerName, 'daklak') !== false) {
        $image = 'images/robusta-daklak.jpg';
    } else if(strpos($lowerName, 'ấn độ') !== false || strpos($lowerName, 'india') !== false) {
        $image = 'images/robusta-india.jpg';
    } else {
        $image = 'images/robusta.jpg';
    }
} else {
    // Giữ nguyên đường dẫn ảnh nếu đã được truyền và không phải Arabica/Robusta
    if(empty($image)) {
        $image = 'images/default-product.jpg';
    }
}

// Lấy giỏ hàng từ session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Ghi log giỏ hàng trước khi thêm
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Cart before: ' . print_r($cart, true) . "\n", FILE_APPEND);

// Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
$found = false;
foreach($cart as $key => $item) {
    if(isset($item['id']) && $item['id'] === $id) {
        // Cập nhật số lượng
        $cart[$key]['quantity'] += $quantity;
        $found = true;
        break;
    }
}

// Nếu chưa có trong giỏ hàng, thêm mới
if(!$found) {
    $cart[] = [
        'id' => $id,
        'name' => $name,
        'price' => $price,
        'image' => $image,
        'quantity' => $quantity
    ];
}

// Lưu giỏ hàng vào session
$_SESSION['cart'] = array_values($cart);

// Ghi log giỏ hàng sau khi thêm
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Cart after: ' . print_r($_SESSION['cart'], true) . "\n", FILE_APPEND);

// Trả về kết quả dựa vào loại request
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
        'cart' => $_SESSION['cart'],
        'count' => count($_SESSION['cart'])
    ]);
} else {
    // Lưu cookie để không chớp trang
    setcookie('cart_updated', 'true', time() + 3600, '/');
    // Chuyển hướng tới trang giỏ hàng
    header('Location: cart.php?added=1');
}
?> 