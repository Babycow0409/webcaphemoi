<?php
session_start();
include 'includes/db_connect.php';
require_once 'includes/cart_functions.php';

// Xử lý cả GET và POST
$action = '';
$id = '';
$name = '';
$price = 0;
$image = '';
$quantity = 1;
$ajax = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $image = isset($_POST['image']) ? $_POST['image'] : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $action = isset($_GET['action']) ? $_GET['action'] : 'add';
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $name = isset($_GET['name']) ? $_GET['name'] : '';
    $price = isset($_GET['price']) ? (float)$_GET['price'] : 0;
    $image = isset($_GET['image']) ? $_GET['image'] : '';
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    $ajax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
}

// Lấy giỏ hàng hiện tại
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Xử lý các hành động
$message = '';
$redirectUrl = 'cart.php';

if ($action == 'add') {
    // Thêm sản phẩm vào giỏ hàng với kiểm tra tồn kho
    $result = addToCart($cart, $id, $name, $price, $image, $quantity, $conn);
    $cart = $result['cart'];
    $message = $result['message'];
    $redirectUrl = 'cart.php';
} else if ($action == 'remove') {
    // Xóa sản phẩm khỏi giỏ hàng
    $cart = removeFromCart($cart, $id);
} else if ($action == 'update') {
    // Cập nhật số lượng sản phẩm trong giỏ hàng với kiểm tra tồn kho
    $result = updateCartItemQuantity($cart, $id, $quantity, $conn);
    $cart = $result['cart'];
    $message = $result['message'];
}

// Cập nhật session
$_SESSION['cart'] = $cart;

// Xử lý phản hồi
if ($ajax) {
    // Nếu là yêu cầu AJAX, trả về dữ liệu JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'cart' => $cart,
        'message' => $message,
        'count' => count($cart),
        'total' => calculateCartTotal($cart)
    ]);
    exit;
} else {
    // Hiển thị thông báo nếu có
    if (!empty($message)) {
        echo "<script>alert('" . addslashes($message) . "');</script>";
    }
    
    // Thực hiện thao tác giỏ hàng với JavaScript
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Đang xử lý giỏ hàng...</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                margin-top: 100px;
            }
            .loader {
                border: 5px solid #f3f3f3;
                border-radius: 50%;
                border-top: 5px solid #3c2f2f;
                width: 40px;
                height: 40px;
                margin: 20px auto;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body>
        <h2>Đang xử lý giỏ hàng...</h2>
        <div class="loader"></div>
        <p>Vui lòng đợi trong giây lát.</p>
        
        <script>
            // Cập nhật LocalStorage từ dữ liệu PHP
            localStorage.setItem("cart", JSON.stringify(' . json_encode($cart) . '));
            
            // Chuyển hướng về trang giỏ hàng sau 0.5 giây
            setTimeout(function() {
                window.location.href = "' . $redirectUrl . '";
            }, 500);
        </script>
    </body>
    </html>
    ';
    exit;
}
?> 