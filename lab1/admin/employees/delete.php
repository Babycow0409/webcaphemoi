<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin']) && !isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
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

// Lấy ID nhân viên từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = "ID nhân viên không hợp lệ";
    header("Location: index.php");
    exit();
}

// Kiểm tra nhân viên có tồn tại không
$sql = "SELECT fullname FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Không tìm thấy nhân viên";
    header("Location: index.php");
    exit();
}

$employee = $result->fetch_assoc();
$stmt->close();

// Xóa nhân viên
$sql = "DELETE FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Đã xóa nhân viên " . htmlspecialchars($employee['fullname']) . " thành công!";
} else {
    $_SESSION['error_message'] = "Lỗi khi xóa nhân viên: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit();
?>

