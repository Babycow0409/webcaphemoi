<?php
// Đảm bảo đã tải config.php
require_once 'includes/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Kiểm tra ID địa chỉ
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid address ID']);
    exit;
}

$address_id = intval($_GET['id']);

// Truy vấn thông tin địa chỉ
$stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $address_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Address not found']);
    exit;
}

$address = $result->fetch_assoc();

// Trả về dữ liệu dạng JSON
header('Content-Type: application/json');
echo json_encode($address);
exit; 