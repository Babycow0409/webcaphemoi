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

// Đảm bảo user_id được đặt đúng
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['error'] = "Phiên đăng nhập của bạn đã hết hạn. Vui lòng đăng nhập lại.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin người dùng từ cơ sở dữ liệu
$user_stmt = $conn->prepare("SELECT fullname, phone FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Lấy dữ liệu từ form
$fullname = $user_data['fullname']; // Lấy từ cơ sở dữ liệu
$email = trim($_POST['email']);
$phone = $user_data['phone']; // Lấy từ cơ sở dữ liệu
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

// Ghi log thông tin user_id
$debug_info = "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL') . "\n";
$debug_info .= "Email: " . (isset($_SESSION['email']) ? $_SESSION['email'] : 'NULL') . "\n";
$debug_info .= "Fullname: " . (isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'NULL') . "\n";
file_put_contents('order_debug.log', date('Y-m-d H:i:s') . " - PRE-ORDER: " . $debug_info, FILE_APPEND);

try {
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    // Lưu thông tin đơn hàng
    $order_date = date('Y-m-d H:i:s');
    $status = 'pending';
    
    // Ghi log thông tin trước khi insert
    $log_info = "Attempting to insert order with user_id: $user_id\n";
    $log_info .= "Name: $fullname\n";
    $log_info .= "Total: $totalAmount\n";
    file_put_contents('order_debug.log', date('Y-m-d H:i:s') . " - INSERT DATA: " . $log_info, FILE_APPEND);
    
    // Tạo đơn hàng mới
    $stmt = $conn->prepare("INSERT INTO orders (user_id, shipping_name, shipping_address, shipping_phone, shipping_city, total_amount, payment_method, status, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssdsss", $user_id, $fullname, $address, $phone, $city, $totalAmount, $payment, $status, $order_date);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // Ghi log kết quả insert
    $log_result = "Created order ID: $order_id\n";
    file_put_contents('order_debug.log', date('Y-m-d H:i:s') . " - ORDER CREATED: " . $log_result, FILE_APPEND);
    
    // Lưu chi tiết đơn hàng
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $price = $item['price'];
        $quantity = $item['quantity'];
        $product_image = isset($item['image']) ? $item['image'] : '';
        
        // Kiểm tra xem cột product_id và image có tồn tại trong bảng order_items không
        $check_product_id = $conn->query("SHOW COLUMNS FROM order_items LIKE 'product_id'");
        $check_image = $conn->query("SHOW COLUMNS FROM order_items LIKE 'image'");
        
        // Chuẩn bị câu truy vấn dựa trên các cột có sẵn
        if ($check_product_id->num_rows > 0 && $check_image->num_rows > 0) {
            // Nếu cả product_id và image tồn tại
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisids", $order_id, $product_id, $item['name'], $quantity, $price, $product_image);
        } elseif ($check_product_id->num_rows > 0) {
            // Nếu chỉ có product_id tồn tại
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisid", $order_id, $product_id, $item['name'], $quantity, $price);
        } elseif ($check_image->num_rows > 0) {
            // Nếu chỉ có image tồn tại
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isids", $order_id, $item['name'], $quantity, $price, $product_image);
        } else {
            // Nếu không có cột nào tồn tại
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isid", $order_id, $item['name'], $quantity, $price);
        }
        $stmt->execute();
    }
    
    // Hoàn tất transaction
    $conn->commit();
    
    // Ghi log transaction hoàn tất
    file_put_contents('order_debug.log', date('Y-m-d H:i:s') . " - TRANSACTION COMPLETED\n", FILE_APPEND);
    
    // Kiểm tra xác nhận đơn hàng đã được lưu
    $verify_query = "SELECT * FROM orders WHERE id = $order_id";
    $verify_result = $conn->query($verify_query);
    if ($verify_result->num_rows > 0) {
        $verify_order = $verify_result->fetch_assoc();
        $verification_log = "Verified order exists - ID: $order_id | User ID: {$verify_order['user_id']} | Total: {$verify_order['total_amount']}\n";
        file_put_contents('order_debug.log', date('Y-m-d H:i:s') . " - VERIFICATION: " . $verification_log, FILE_APPEND);
    } else {
        file_put_contents('order_debug.log', date('Y-m-d H:i:s') . " - VERIFICATION ERROR: Order not found after commit!\n", FILE_APPEND);
    }
    
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
    
    // Ghi log lỗi
    file_put_contents('order_debug.log', date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Hiển thị thông báo lỗi
    $_SESSION['error'] = "Có lỗi xảy ra khi đặt hàng: " . $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?> 