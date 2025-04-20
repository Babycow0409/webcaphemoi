<?php
session_start();

// Kiểm tra xem đã có giỏ hàng trong session chưa
if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Nếu đã có, không cần đồng bộ nữa
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Giỏ hàng đã tồn tại']);
    exit;
}

// Nhận dữ liệu từ request
$json = file_get_contents('php://input');
$cart = json_decode($json, true);

// Kiểm tra dữ liệu hợp lệ
if(is_array($cart)) {
    // Xử lý đặc biệt cho Robusta Ấn Độ
    foreach($cart as &$item) {
        if(isset($item['name']) && strpos($item['name'], 'Robusta Ấn Độ') !== false) {
            $item['image'] = 'images/robusta-india.jpg';
        }
        
        // Đảm bảo số lượng là số nguyên dương
        if(isset($item['quantity'])) {
            $item['quantity'] = max(1, (int)$item['quantity']);
        } else {
            $item['quantity'] = 1;
        }
    }
    
    // Lưu vào session
    $_SESSION['cart'] = $cart;
    
    // Phản hồi thành công
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Đồng bộ giỏ hàng thành công']);
} else {
    // Phản hồi lỗi
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dữ liệu giỏ hàng không hợp lệ']);
}
?> 