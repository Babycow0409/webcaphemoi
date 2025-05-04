<?php
// Bắt đầu session nếu chưa bắt đầu
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kết quả trả về mặc định
$response = [
    'success' => false,
    'message' => 'Không có dữ liệu giỏ hàng được gửi',
    'count' => 0
];

// Ghi log để theo dõi request
file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Sync cart request received, POST data: ' . print_r($_POST, true) . "\n", FILE_APPEND);

// Kiểm tra xem có dữ liệu giỏ hàng được gửi không
if(isset($_POST['cart_data'])) {
    $cartData = $_POST['cart_data'];
    
    // Ghi log để debug
    file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Cart data received: ' . $cartData . "\n", FILE_APPEND);
    
    // Chuyển đổi dữ liệu JSON thành mảng PHP
    $cartArray = json_decode($cartData, true);
    
    // Kiểm tra tính hợp lệ của dữ liệu
    if(is_array($cartArray)) {
        // Lọc các sản phẩm hợp lệ
        $validCart = [];
        foreach($cartArray as $item) {
            if(isset($item['id']) && (isset($item['name']) || isset($item['price']))) {
                // Đảm bảo số lượng là hợp lệ
                if(!isset($item['quantity']) || $item['quantity'] < 1) {
                    $item['quantity'] = 1;
                }
                $validCart[] = $item;
            }
        }
        
        // Lưu vào session
        $_SESSION['cart'] = array_values($validCart);
        
        // Cập nhật kết quả trả về
        $response = [
            'success' => true,
            'message' => 'Đã đồng bộ ' . count($_SESSION['cart']) . ' sản phẩm vào giỏ hàng',
            'count' => count($_SESSION['cart'])
        ];
        
        // Ghi log kết quả
        file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Sync cart success: ' . count($_SESSION['cart']) . " sản phẩm\n", FILE_APPEND);
    } else {
        $response['message'] = 'Dữ liệu giỏ hàng không hợp lệ';
        file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . " - Sync cart failed: Invalid data format\n", FILE_APPEND);
    }
} else {
    file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . " - Sync cart failed: No cart data in request\n", FILE_APPEND);
}

// Trả về kết quả dưới dạng JSON
header('Content-Type: application/json');
echo json_encode($response);
?> 