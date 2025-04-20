<?php
// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coffee_shop";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra xem cột active đã tồn tại chưa
$check_column = "SHOW COLUMNS FROM products LIKE 'active'";
$result = $conn->query($check_column);

if ($result->num_rows == 0) {
    // Cột chưa tồn tại, thêm vào
    $sql = "ALTER TABLE products ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1";
    
    if ($conn->query($sql) === TRUE) {
        echo "Đã thêm cột 'active' vào bảng products thành công!";
    } else {
        echo "Lỗi khi thêm cột: " . $conn->error;
    }
} else {
    echo "Cột 'active' đã tồn tại trong bảng products.";
}

$conn->close();
?> 