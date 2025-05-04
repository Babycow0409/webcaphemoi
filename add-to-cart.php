<?php
session_start();
require_once 'includes/db_connect.php'; // Kết nối database
require_once 'includes/cart_functions.php'; // Thêm thư viện giỏ hàng

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
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $image = isset($_POST['image']) ? $_POST['image'] : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
} else {
    // Xử lý GET request
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    $price = isset($_GET['price']) ? (float)$_GET['price'] : 0;
    $image = isset($_GET['image']) ? $_GET['image'] : '';
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    $ajax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
}

// Ghi log dữ liệu sản phẩm
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Product data: ' . 
    "ID: $id, Name: $name, Price: $price, Image: $image, Quantity: $quantity\n", FILE_APPEND);

// Kiểm tra dữ liệu hợp lệ
if(empty($id) || $id <= 0) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Error: Invalid product ID' . "\n", FILE_APPEND);
    
    if($ajax || $_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
    } else {
        // Chuyển hướng nếu là GET request
        header('Location: products.php?error=invalid_id');
    }
    exit;
}

// Lấy thông tin sản phẩm từ database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Lấy thông tin từ database nếu không có dữ liệu trong request hoặc dữ liệu không hợp lệ
    if (empty($name) || $name === "undefined") {
        $name = $product['name'];
    }
    
    if ($price <= 0) {
        $price = (float)$product['price'];
    }
    
    if (empty($image) || $image === "undefined") {
        $image = $product['image'];
    }
    
    // Xử lý đường dẫn hình ảnh
    if (!empty($image) && strpos($image, 'uploads/') === false && strpos($image, 'images/') === false) {
        $image = 'uploads/products/' . $image;
    }
    
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Product found in database: ' . 
        "ID: {$product['id']}, Name: {$product['name']}, Price: {$product['price']}, Image: {$product['image']}\n", FILE_APPEND);
    
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Final product data for cart: ' . 
        "ID: $id, Name: $name, Price: $price, Image: $image, Quantity: $quantity\n", FILE_APPEND);
} else {
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Error: Product not found in database' . "\n", FILE_APPEND);
    
    if($ajax || $_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
    } else {
        header('Location: products.php?error=product_not_found');
    }
    exit;
}

// Đảm bảo đường dẫn hình ảnh đúng
if (!empty($image) && strpos($image, 'uploads/') === false && strpos($image, 'images/') === false) {
    $image = 'uploads/products/' . $image;
}

// Reset giỏ hàng nếu chưa có hoặc không phải là mảng
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];

// Ghi log giỏ hàng trước khi thêm
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Cart before: ' . print_r($cart, true) . "\n", FILE_APPEND);
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Adding product with ID: ' . $id . " (type: integer)\n", FILE_APPEND);

// Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
$found = false;
foreach($cart as $key => $item) {
    $itemId = isset($item['id']) ? (int)$item['id'] : 0;
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Checking cart item with ID: ' . $itemId . " (type: integer)\n", FILE_APPEND);
    
    if($itemId === $id) {
        // Cập nhật số lượng
        $cart[$key]['quantity'] += $quantity;
        $cart[$key]['name'] = $name;
        $cart[$key]['price'] = $price;
        $cart[$key]['image'] = $image;
        
        $found = true;
        file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Found existing product, updated quantity to: ' . $cart[$key]['quantity'] . "\n", FILE_APPEND);
        break;
    }
}

// Nếu chưa có trong giỏ hàng, thêm mới
if(!$found) {
    // Đảm bảo ID là số nguyên khi thêm vào giỏ hàng
    $id = (int)$id;
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Final product ID type check: ' . gettype($id) . ", value: $id\n", FILE_APPEND);
    
    $newItem = [
        'id' => $id, // Lưu ID dưới dạng số nguyên
        'name' => $name,
        'price' => $price,
        'image' => $image,
        'quantity' => $quantity
    ];
    $cart[] = $newItem;
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Added new product to cart with ID type: ' . gettype($newItem['id']) . "\n", FILE_APPEND);
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - New item data: ' . print_r($newItem, true) . "\n", FILE_APPEND);
}

// Đảm bảo mảng giỏ hàng không bị lỗi
$cart = array_values($cart);

// Lưu giỏ hàng vào session
$_SESSION['cart'] = $cart;

// Ghi log giỏ hàng sau khi thêm
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Cart after: ' . print_r($_SESSION['cart'], true) . "\n", FILE_APPEND);
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Cart count: ' . count($_SESSION['cart']) . "\n", FILE_APPEND);

// Cập nhật localStorage qua JavaScript
$js_cart = json_encode($_SESSION['cart']);

// Trả về kết quả dựa vào loại request
if($ajax || $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
        'cart' => $_SESSION['cart'],
        'count' => count($_SESSION['cart'])
    ]);
} else {
    // Thông báo thành công
    $_SESSION['cart_message'] = "Đã thêm sản phẩm \"$name\" vào giỏ hàng.";
    
    // Lấy trang trước đó từ HTTP_REFERER hoặc về trang sản phẩm
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'products.php';
    
    // Script để cập nhật localStorage và chuyển về trang trước đó thay vì giỏ hàng
    echo "<script>
        localStorage.setItem('cart', '$js_cart');
        window.onload = function() {
            if(window.updateCartCount) {
                updateCartCount(" . count($_SESSION['cart']) . ");
            }
            window.location.href = '$redirect_url';
        };
    </script>";
}
?> 