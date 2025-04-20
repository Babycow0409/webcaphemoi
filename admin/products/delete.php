<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

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

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = intval($_GET['id']);

// Kiểm tra xem cột active đã tồn tại trong bảng products chưa
$check_column = "SHOW COLUMNS FROM products LIKE 'active'";
$result = $conn->query($check_column);

if ($result->num_rows > 0) {
    // Cột active đã tồn tại, cập nhật trạng thái
    $sql = "UPDATE products SET active = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?message=Sản phẩm đã được ẩn thành công");
    } else {
        header("Location: index.php?error=Có lỗi xảy ra khi ẩn sản phẩm");
    }
} else {
    // Cột active chưa tồn tại, xóa sản phẩm
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?message=Sản phẩm đã được xóa thành công");
    } else {
        header("Location: index.php?error=Có lỗi xảy ra khi xóa sản phẩm");
    }
}

exit();
?> 