<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit();
}

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

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = intval($_GET['id']);

// Lấy thông tin sản phẩm trước khi xóa
$sql_select = "SELECT * FROM products WHERE id = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $product_id);
$stmt_select->execute();
$result_select = $stmt_select->get_result();
$product = $result_select->fetch_assoc();

// Xóa sản phẩm
$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    // Xóa file hình ảnh nếu có
    if (!empty($product['image']) && file_exists("../../" . $product['image'])) {
        unlink("../../" . $product['image']);
    }
    header("Location: index.php?message=Sản phẩm đã được xóa thành công");
} else {
    header("Location: index.php?error=Có lỗi xảy ra khi xóa sản phẩm: " . $conn->error);
}

exit();
?>
