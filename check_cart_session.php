<?php
// Bắt đầu session nếu chưa bắt đầu
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra giỏ hàng trong session
$hasSession = isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0;
$count = $hasSession ? count($_SESSION['cart']) : 0;

// Ghi log cho debug
file_put_contents('cart_log.txt', date('Y-m-d H:i:s') . ' - Check cart session: ' . ($hasSession ? 'true' : 'false') . ', count: ' . $count . "\n", FILE_APPEND);

// Trả về kết quả dưới dạng JSON
header('Content-Type: application/json');
echo json_encode([
    'hasSession' => $hasSession,
    'count' => $count
]);
?> 