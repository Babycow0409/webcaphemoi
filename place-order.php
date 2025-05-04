<?php
session_start();
include 'includes/db_connect.php';
require_once 'includes/cart_functions.php';

// Kiểm tra giỏ hàng
if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['message'] = "Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.";
    header("Location: cart.php");
    exit;
}

// Kiểm tra dữ liệu POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit;
}

// Lấy dữ liệu từ form
$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$city = "Không rõ"; // Thêm giá trị mặc định cho city
$payment = $_POST['payment'];
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

// Kiểm tra dữ liệu
if (empty($fullname) || empty($email) || empty($phone) || empty($address)) {
    $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin bắt buộc.";
    header("Location: checkout.php");
    exit;
}

// Lấy thông tin giỏ hàng và tính tổng tiền
$cart = $_SESSION['cart'];
$totalAmount = calculateCartTotal($cart);

try {
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    // Lưu thông tin đơn hàng
    $order_date = date('Y-m-d H:i:s');
    $status = 'pending';
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Tạo đơn hàng mới
    $stmt = $conn->prepare("INSERT INTO orders (user_id, shipping_name, shipping_address, shipping_phone, shipping_city, total_amount, payment_method, status, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssdsss", $user_id, $fullname, $address, $phone, $city, $totalAmount, $payment, $status, $order_date);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // Lưu chi tiết đơn hàng
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $price = $item['price'];
        $quantity = $item['quantity'];
        
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isid", $order_id, $item['name'], $quantity, $price);
        $stmt->execute();
    }
    
    // Hoàn tất transaction
    $conn->commit();
    
    // Xóa giỏ hàng
    unset($_SESSION['cart']);
    
    // Thêm thông tin đơn hàng vào session để hiển thị trang cảm ơn
    $_SESSION['order_id'] = $order_id;
    $_SESSION['order_total'] = $totalAmount;
    
    // Chuyển hướng đến trang cảm ơn
    header("Location: order-success.php");
    exit;
    
} catch (Exception $e) {
    // Rollback nếu có lỗi
    $conn->rollback();
    
    // Hiển thị thông báo lỗi
    $_SESSION['error'] = "Có lỗi xảy ra khi đặt hàng: " . $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đang xử lý đơn hàng - Cà Phê Đậm Đà</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        .spinner {
            width: 50px;
            height: 50px;
            margin: 20px auto;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <h1>Đang xử lý đơn hàng của bạn</h1>
    <div class="spinner"></div>
    <p>Vui lòng đợi trong giây lát...</p>
</body>
</html> 