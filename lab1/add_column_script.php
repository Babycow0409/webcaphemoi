Script to add custom_order_id column to orders table

<?php
// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra xem cột custom_order_id đã tồn tại chưa
$checkColumn = $conn->query("SHOW COLUMNS FROM orders LIKE 'custom_order_id'");
if ($checkColumn->num_rows === 0) {
    // Thêm cột custom_order_id vào bảng orders nếu chưa tồn tại
    $alterQuery = "ALTER TABLE orders ADD COLUMN custom_order_id VARCHAR(50) NULL UNIQUE AFTER id";
    
    if ($conn->query($alterQuery) === TRUE) {
        echo "Đã thêm cột custom_order_id vào bảng orders thành công!";
    } else {
        echo "Lỗi khi thêm cột: " . $conn->error;
    }
} else {
    echo "Cột custom_order_id đã tồn tại trong bảng orders.";
}

// Đóng kết nối
$conn->close();
?>
