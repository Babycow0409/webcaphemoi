<?php
session_start();
header('Content-Type: application/json');

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra quyền admin
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.']);
    exit;
}

// Kiểm tra tham số
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu tham số cần thiết.']);
    exit;
}

$user_id = intval($_GET['id']);
$action = $_GET['action'];

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab1";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Kết nối database thất bại.']);
    exit;
}

// Không cho phép khóa/mở khóa admin
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
if ($stmt->fetch() && $role === 'admin') {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Không thể khóa hoặc mở khóa tài khoản quản trị viên.']);
    exit;
}
$stmt->close();

// Xác định trạng thái mới
if ($action === 'activate') {
    $new_status = 1;
    $msg = 'Mở khóa tài khoản thành công!';
} elseif ($action === 'deactivate') {
    $new_status = 0;
    $msg = 'Khóa tài khoản thành công!';
} else {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
    exit;
}

// Cập nhật trạng thái
$stmt = $conn->prepare("UPDATE users SET active = ? WHERE id = ?");
$stmt->bind_param("ii", $new_status, $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => $msg]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái tài khoản.']);
}
$stmt->close();
$conn->close();