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

// Lấy ID ca từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = "ID ca làm việc không hợp lệ";
    header("Location: index.php");
    exit();
}

// Kiểm tra ca có tồn tại không
$sql = "SELECT shift_name FROM work_shifts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Không tìm thấy ca làm việc";
    header("Location: index.php");
    exit();
}

$shift = $result->fetch_assoc();
$stmt->close();

// Kiểm tra xem ca có đang được sử dụng trong phân ca không
$check_sql = "SELECT COUNT(*) as count FROM shift_assignments WHERE shift_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_data = $check_result->fetch_assoc();
$check_stmt->close();

if ($check_data['count'] > 0) {
    $_SESSION['error_message'] = "Không thể xóa ca này vì đã có nhân viên được phân ca. Vui lòng xóa các phân ca trước.";
    header("Location: index.php");
    exit();
}

// Xóa ca
$sql = "DELETE FROM work_shifts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Đã xóa ca " . htmlspecialchars($shift['shift_name']) . " thành công!";
} else {
    $_SESSION['error_message'] = "Lỗi khi xóa ca: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit();
?>




