<?php
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

// Kiểm tra bảng order_items
echo "<h3>Kiểm tra bảng order_items</h3>";

// Kiểm tra xem bảng có tồn tại
$check_table = $conn->query("SHOW TABLES LIKE 'order_items'");
if ($check_table->num_rows == 0) {
    echo "Bảng order_items không tồn tại!";
} else {
    echo "Bảng order_items tồn tại.<br>";
    
    // Lấy cấu trúc bảng
    $result = $conn->query("SHOW COLUMNS FROM order_items");
    if ($result->num_rows > 0) {
        echo "<ul>";
        while($row = $result->fetch_assoc()) {
            echo "<li>" . $row["Field"] . " - " . $row["Type"] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "Không lấy được cấu trúc bảng!";
    }
}

// Đóng kết nối
$conn->close();
?> 