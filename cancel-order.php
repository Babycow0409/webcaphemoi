<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Kiểm tra ID đơn hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user']['id'];

// Kiểm tra đơn hàng tồn tại và thuộc về người dùng hiện tại
$sql_check = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $order_id, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    // Đơn hàng không tồn tại hoặc không thuộc về người dùng hiện tại hoặc không ở trạng thái "pending"
    header("Location: orders.php?error=Không thể hủy đơn hàng này");
    exit();
}

// Cập nhật trạng thái đơn hàng thành "cancelled"
$sql_update = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("i", $order_id);

if ($stmt_update->execute()) {
    header("Location: orders.php?success=Đơn hàng đã được hủy thành công");
} else {
    header("Location: orders.php?error=Có lỗi xảy ra khi hủy đơn hàng");
}
exit();
?> 