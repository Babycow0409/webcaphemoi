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

// Đặt charset là utf8mb4
$conn->set_charset("utf8mb4");

// Thêm thông tin debug - chỉ để kiểm tra
if (isset($_GET['debug_db']) && $_GET['debug_db'] == 1) {
    echo "Kết nối cơ sở dữ liệu thành công:";
    echo "<pre>";
    echo "Server: $servername\n";
    echo "Database: $dbname\n";
    echo "Username: $username\n";
    echo "</pre>";
}
?>