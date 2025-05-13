<?php
// File config.php - Xử lý các cấu hình chung

// Đảm bảo session đã được bắt đầu
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Đặt múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Đảm bảo Content-Type header được thiết lập đúng
// Chỉ thiết lập header nếu chưa có output nào được gửi đi
if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

// Thiết lập các hằng số
define('SITE_NAME', 'Cà Phê Đậm Đà');
define('SITE_URL', 'http://localhost/php1su/lab1');

// Hàm xử lý lỗi và thông báo
function set_message($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function display_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        
        echo '<div class="alert alert-' . $type . '">' . $message . '</div>';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Hàm chuyển hướng an toàn
function redirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit;
    } else {
        echo '<script>window.location.href="' . $url . '";</script>';
        exit;
    }
}

// Kết nối database
require_once 'db_connect.php';
?> 