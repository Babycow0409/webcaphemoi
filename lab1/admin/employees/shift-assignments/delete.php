<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin']) && !isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy ID phân ca từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = "ID phân ca không hợp lệ";
    header("Location: index.php");
    exit();
}

// Xóa phân ca
$sql = "DELETE FROM shift_assignments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Đã xóa phân ca thành công!";
} else {
    $_SESSION['error_message'] = "Lỗi khi xóa phân ca: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit();
?>




